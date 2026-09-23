<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MailLog;
use App\Models\MailAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Webklex\IMAP\Facades\Client;

class MailController extends Controller
{
    public function index(Request $request)
    {
        $folder = $request->query('folder', 'inbox');
        $search = $request->query('q');
        
        $messagesQuery = DB::table('inbox_messages');
        
        if ($folder == 'starred') {
            $messagesQuery->where('is_starred', true);
        } else {
            $messagesQuery->where('folder', $folder);
        }

        if ($search) {
            $messagesQuery->where(function($q) use ($search) {
                $q->where('subject', 'like', "%$search%")
                  ->orWhere('sender_name', 'like', "%$search%")
                  ->orWhere('message', 'like', "%$search%");
            });
        }

        $messages = $messagesQuery->latest()->get();

        if ($folder == 'sent') {
            $sentQuery = MailLog::query();
            if ($search) {
                $sentQuery->where('subject', 'like', "%$search%")
                          ->orWhere('message', 'like', "%$search%");
            }
            
            $messages = $sentQuery->latest()->get()->map(function($log) {
                return (object)[
                    'id' => $log->id,
                    'sender_name' => $log->recipient ? 'To: ' . $log->recipient : 'Me (Broadcast)',
                    'sender_email' => $log->recipient ?? '',
                    'subject' => $log->subject,
                    'message' => $log->message,
                    'is_read' => true,
                    'is_starred' => false,
                    'created_at' => $log->created_at,
                    'is_sent' => true
                ];
            });
        }

        $stats = [
            'unread' => DB::table('inbox_messages')->where('is_read', false)->count(),
            'starred' => DB::table('inbox_messages')->where('is_starred', true)->count(),
            'total_customers' => Customer::whereNotNull('email')->count(),
        ];

        $accounts = MailAccount::all();

        return view('tenant.mail.index', compact('messages', 'folder', 'stats', 'accounts', 'search'));
    }

    public function sync()
    {
        try {
            // Get account to sync
            $acc = MailAccount::where('is_default', true)->first() ?? MailAccount::first();

            if (!$acc) {
                // Fallback to ENV if no accounts in DB
                if (!env('IMAP_USERNAME')) {
                    return response()->json(['success' => false, 'message' => 'No mail accounts configured.']);
                }
                $client = Client::account('default');
            } else {
                // Create a temporary account config for the client
                $client = Client::make([
                    'host'          => $acc->imap_host ?: 'imap.gmail.com',
                    'port'          => $acc->imap_port ?: 993,
                    'encryption'    => $acc->imap_encryption ?: 'ssl',
                    'validate_cert' => false,
                    'username'      => $acc->smtp_user,
                    'password'      => $acc->smtp_password,
                    'protocol'      => 'imap'
                ]);
            }

            $client->connect();

            $inbox = $client->getFolder('INBOX') ?? $client->getFolder('Inbox') ?? $client->getFolder('inbox');
            if (!$inbox) {
                foreach($client->getFolders() as $f) {
                    if(str_contains(strtolower($f->name), 'inbox')) { $inbox = $f; break; }
                }
            }
            
            // Get last 20 messages (Newest first)
            $messages = $inbox->query()->all()->setFetchOrder("desc")->limit(20)->get();
            
            $count = 0;
            foreach ($messages as $message) {
                $uid = (string) $message->getUid();
                
                // Check if message already exists by UID
                $exists = DB::table('inbox_messages')
                    ->where('message_uid', $uid)
                    ->exists();

                if (!$exists) {
                    $count++;
                    $msgDate = \Carbon\Carbon::parse($message->getDate()->first());
                    DB::table('inbox_messages')->insert([
                        'message_uid' => $uid,
                        'sender_name' => $message->getFrom()[0]->personal ?? $message->getFrom()[0]->mail,
                        'sender_email' => $message->getFrom()[0]->mail,
                        'subject' => (string) $message->getSubject(),
                        'message' => $message->getHTMLBody() ?: $message->getTextBody(),
                        'is_read' => $message->hasFlag('seen'),
                        'folder' => 'inbox',
                        'created_at' => $msgDate,
                        'updated_at' => now(),
                    ]);
                }
            }

            return response()->json(['success' => true, 'message' => "Found $count new messages!"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Sync failed: ' . $e->getMessage()], 500);
        }
    }

    public function broadcast(Request $request)
    {
        $request->validate([
            'to' => 'required|string',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'account_id' => 'nullable'
        ]);

        $to = $request->to;
        $subject = $request->subject;
        $content = $request->message;

        // Use specifically selected account or fallback to default
        if ($request->account_id) {
            $acc = MailAccount::find($request->account_id);
        } else {
            $acc = MailAccount::where('is_default', true)->first() ?? MailAccount::first();
        }

        if ($acc) {
            config([
                'mail.mailers.smtp.host' => $acc->smtp_host,
                'mail.mailers.smtp.port' => $acc->smtp_port,
                'mail.mailers.smtp.username' => $acc->smtp_user,
                'mail.mailers.smtp.password' => $acc->smtp_password,
                'mail.mailers.smtp.encryption' => $acc->smtp_encryption ?: 'tls',
                'mail.from.address' => $acc->email,
                'mail.from.name' => $acc->from_name ?: 'TrexoERP',
            ]);
            Mail::purge('smtp');
        }

        try {
            Mail::send([], [], function ($message) use ($to, $subject, $content) {
                $message->to($to)
                    ->subject($subject)
                    ->html(nl2br($content));
            });

            // Log it
            MailLog::create([
                'subject' => $subject,
                'recipient' => $to,
                'message' => $content,
                'type' => 'individual',
                'recipients_count' => 1,
                'success_count' => 1,
                'failed_count' => 0,
                'sent_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Email sent successfully!',
                'sent_from' => $acc ? $acc->email : config('mail.from.address')
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    public function toggleStar($id)
    {
        $msg = DB::table('inbox_messages')->where('id', $id)->first();
        if ($msg) {
            DB::table('inbox_messages')->where('id', $id)->update(['is_starred' => !$msg->is_starred]);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    /**
     * Account Management
     */
    public function storeAccount(Request $request)
    {
        $data = $request->validate([
            'account_name' => 'required|string',
            'email' => 'required|email',
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|integer',
            'smtp_user' => 'required|string',
            'smtp_password' => 'required|string',
            'smtp_encryption' => 'nullable|string',
            'imap_host' => 'nullable|string',
            'imap_port' => 'nullable|integer',
            'imap_encryption' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);

        // Trim all string inputs
        $data = array_map(function($value) {
            return is_string($value) ? trim($value) : $value;
        }, $data);

        if ($request->is_default) {
            MailAccount::where('is_default', true)->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        MailAccount::create($data);
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Account added successfully!']);
        }
 
        return redirect()->back()->with('success', 'Mail account added successfully.');
    }

    public function deleteAccount(MailAccount $account)
    {
        $account->delete();
        return redirect()->back()->with('success', 'Mail account deleted.');
    }
}
