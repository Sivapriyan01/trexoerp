<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SetupApiController extends Controller
{
    // GET /api/v1/setup
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');

        return response()->json(['success' => true, 'data' => $settings]);
    }

    // PUT /api/v1/setup
    public function update(Request $request)
    {
        $request->validate([
            'settings'   => 'required|array',
            'settings.*' => 'nullable|string',
        ]);

        foreach ($request->settings as $key => $value) {
            Setting::set($key, $value);
        }

        return response()->json(['success' => true, 'message' => 'Settings updated.']);
    }

    // GET /api/v1/setup/backup
    public function backup()
    {
        // Triggers the download — returns a URL or triggers stream
        return response()->json([
            'success' => true,
            'message' => 'Backup initiated. Use the web interface to download.',
            'backup_url' => route('tenant.setup.backup'),
        ]);
    }

    // GET /api/v1/setup/modules
    public function modules()
    {
        $modules = [
            'Billing', 'Purchase', 'Inventory', 'Customers', 'Vendors',
            'Employee List', 'Employee Attendance', 'Stock Transfer',
            'Instalments', 'WhatsApp', 'CRM', 'Production', 'SetUp',
        ];

        return response()->json(['success' => true, 'data' => $modules]);
    }

    // GET /api/v1/setup/business
    public function business()
    {
        $keys = ['business_name', 'address', 'phone', 'email', 'gstin', 'currency', 'logo'];

        $data = collect($keys)->mapWithKeys(fn($key) => [$key => Setting::get($key)]);

        return response()->json(['success' => true, 'data' => $data]);
    }
}
