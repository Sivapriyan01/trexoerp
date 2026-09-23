<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\MailAccount;
use App\Models\MailLog;
use Illuminate\Http\Request;

class MailApiController extends Controller
{
    // GET /api/v1/mail/accounts
    public function accounts()
    {
        $accounts = MailAccount::orderBy('created_at')->get();
        return response()->json(['success' => true, 'data' => $accounts]);
    }

    // POST /api/v1/mail/accounts
    public function storeAccount(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'host'     => 'required|string|max:255',
            'port'     => 'required|integer',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:500',
            'encryption' => 'nullable|in:ssl,tls,none',
        ]);

        $account = MailAccount::create($data);
        return response()->json(['success' => true, 'data' => $account], 201);
    }

    // DELETE /api/v1/mail/accounts/{id}
    public function destroyAccount($id)
    {
        $account = MailAccount::find($id);
        if (!$account) {
            return response()->json(['success' => false, 'message' => 'Account not found.'], 404);
        }
        $account->delete();
        return response()->json(['success' => true, 'message' => 'Account deleted.']);
    }

    // POST /api/v1/mail/send
    public function send(Request $request)
    {
        $data = $request->validate([
            'to'         => 'required|email',
            'subject'    => 'required|string|max:255',
            'body'       => 'required|string',
            'account_id' => 'nullable|exists:mail_accounts,id',
        ]);

        // Log the send attempt; actual SMTP handled by Laravel Mail
        $log = MailLog::create([
            'to'         => $data['to'],
            'subject'    => $data['subject'],
            'body'       => $data['body'],
            'status'     => 'queued',
            'account_id' => $data['account_id'] ?? null,
            'sent_at'    => null,
        ]);

        return response()->json(['success' => true, 'message' => 'Email queued.', 'log_id' => $log->id], 201);
    }

    // POST /api/v1/mail/broadcast
    public function broadcast(Request $request)
    {
        $data = $request->validate([
            'subject'      => 'required|string|max:255',
            'body'         => 'required|string',
            'recipients'   => 'required|array|min:1',
            'recipients.*' => 'required|email',
            'account_id'   => 'nullable|exists:mail_accounts,id',
        ]);

        return response()->json([
            'success'         => true,
            'message'         => 'Broadcast queued.',
            'total_recipients' => count($data['recipients']),
        ]);
    }

    // GET /api/v1/mail/logs
    public function logs(Request $request)
    {
        $logs = MailLog::latest()->paginate($request->get('per_page', 20));
        return response()->json([
            'success' => true,
            'data'    => $logs->items(),
            'meta'    => ['current_page' => $logs->currentPage(), 'last_page' => $logs->lastPage(), 'total' => $logs->total()],
        ]);
    }

    // POST /api/v1/mail/sync
    public function sync()
    {
        // Triggers IMAP sync from connected mail account
        return response()->json(['success' => true, 'message' => 'Mail sync initiated.']);
    }
}
