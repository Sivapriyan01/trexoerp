<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SetupController extends Controller
{
    public function index()
    {
        // Check if settings page is locked
        if (Setting::get('sec_lock_enabled', '0') == '1' && !session('setup_unlocked')) {
            $data = [];
            foreach (Setting::all() as $s) {
                $data[$s->key] = $s->value;
            }
            return view('tenant.setup.index', compact('data'))->with('locked', true);
        }

        $data = [];
        foreach (Setting::all() as $s) {
            $data[$s->key] = $s->value;
        }

        return view('tenant.setup.index', compact('data'));
    }

    public function unlock(Request $request)
    {
        $pin = $request->input('pin');
        $storedPin = Setting::get('sec_lock_code', '');

        if ($pin === $storedPin) {
            session(['setup_unlocked' => true]);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid PIN']);
    }

    public function update(Request $request)
    {
        try {
            $input = $request->except(['_token', 'logo', 'inv_header_img', 'inv_footer_img', 'inv_watermark_img']);
            \Log::info('Setup Update Started', ['keys' => array_keys($input)]);

            foreach ($input as $key => $value) {
                // Determine group based on prefix or name
                $group = 'general';
                if (str_starts_with($key, 'business_')) $group = 'business';
                if (str_starts_with($key, 'bill_')) $group = 'billing';
                if (str_starts_with($key, 'pay_')) $group = 'billing';
                if (str_starts_with($key, 'inv_')) $group = 'invoice';
                if (str_starts_with($key, 'qb_')) $group = 'billing';
                if (str_starts_with($key, 'po_')) $group = 'billing';
                if (str_starts_with($key, 'pur_')) $group = 'purchase';
                if (str_starts_with($key, 'cat_')) $group = 'category';
                if (str_starts_with($key, 'gst_')) $group = 'gst';
                if (str_starts_with($key, 'anniversary_')) $group = 'anniversary';
                if (str_starts_with($key, 'sec_')) $group = 'security';
                if (str_starts_with($key, 'notif_')) $group = 'notification';
                if (str_starts_with($key, 'whatsapp_')) $group = 'whatsapp';
                if (str_starts_with($key, 'wa_')) $group = 'whatsapp';
                if (str_starts_with($key, 'website_')) $group = 'website';
                if (str_starts_with($key, 'msg91_')) $group = 'website';
                if (str_starts_with($key, 'print_')) $group = 'print';

                Setting::set($key, $value, $group);
            }

            // Handle Logo Upload
            if ($request->hasFile('logo')) {
                \Log::info('Handling Logo Upload');
                $path = $request->file('logo')->store('branding', 'public');
                Setting::set('business_logo', $path, 'business');
            }

            // Handle Multiple Invoice Image Uploads
            $invoiceImages = ['inv_header_img', 'inv_footer_img', 'inv_watermark_img'];
            foreach ($invoiceImages as $field) {
                if ($request->hasFile($field)) {
                    \Log::info("Handling $field Upload");
                    $path = $request->file($field)->store('branding', 'public');
                    Setting::set($field, $path, 'invoice');
                }
                
                // Handle Image Removal
                if ($request->input('remove_' . $field) == '1') {
                    Setting::set($field, '', 'invoice');
                }
            }

            \Log::info('Setup Update Completed Successfully');
            return redirect()->back()->with('success', 'Settings updated');
        } catch (\Exception $e) {
            \Log::error('Setup Update Failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to save settings: ' . $e->getMessage());
        }
    }

    public function backup()
    {
        $tenantId = tenant()->id;
        // Call the command without --no-telegram so it sends to Telegram
        \Illuminate\Support\Facades\Artisan::call('tenant:backup', ['tenant' => $tenantId]);
        
        return back()->with('success', 'Backup successfully generated and sent to Telegram.');
    }

    public function restore(Request $request)
    {
        $request->validate(['backup_file' => 'required|file']);
        
        $tenantId = tenant()->id;
        $file = $request->file('backup_file');
        
        $extension = $file->getClientOriginalExtension();
        if ($extension === 'zip') {
            $zip = new \ZipArchive;
            $res = $zip->open($file->getRealPath());
            if ($res === TRUE) {
                $sqlFilename = $zip->getNameIndex(0);
                $extractPath = storage_path('app/backups/temp/');
                $zip->extractTo($extractPath);
                $zip->close();
                $fullPath = $extractPath . $sqlFilename;
            } else {
                return back()->with('error', 'Failed to open the zip file.');
            }
        } else {
            $path = $file->storeAs('backups/temp', $tenantId . '-restore.sql');
            $fullPath = storage_path('app/' . $path);
        }
        
        \Illuminate\Support\Facades\Artisan::call('tenant:restore', [
            'tenant' => $tenantId,
            'file' => $fullPath
        ]);
        
        @unlink($fullPath);
        
        return back()->with('status', 'Backup imported successfully. Please log in again.');
    }
}
