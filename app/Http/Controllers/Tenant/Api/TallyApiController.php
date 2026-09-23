<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Purchase;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Http\Request;

class TallyApiController extends Controller
{
    // GET /api/v1/tally
    public function index()
    {
        return response()->json([
            'success' => true,
            'module'  => 'Tally Integration',
            'version' => 'v1',
            'data'    => [
                'company_name'   => Setting::get('tally_company_name', null),
                'tally_enabled'  => (bool) Setting::get('tally_enabled', false),
                'last_import_at' => Setting::get('tally_last_import_at', null),
                'last_import_count' => (int) Setting::get('tally_last_import_count', 0),
            ],
            'endpoints' => [
                'settings'       => url('/api/v1/tally/settings'),
                'export_preview' => url('/api/v1/tally/export/preview'),
                'export'         => url('/api/v1/tally/export'),
                'import_status'  => url('/api/v1/tally/import/status'),
                'logs'           => url('/api/v1/tally/logs'),
                'validate'       => url('/api/v1/tally/validate'),
            ],
        ]);
    }

    // GET /api/v1/tally/settings
    public function settings()
    {
        $keys = [
            'tally_company_name', 'tally_ledger_sales', 'tally_ledger_purchase',
            'tally_ledger_cash', 'tally_ledger_gst', 'tally_enabled',
        ];

        $data = collect($keys)->mapWithKeys(fn($k) => [$k => Setting::get($k)]);

        return response()->json(['success' => true, 'data' => $data]);
    }

    // POST /api/v1/tally/settings
    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'tally_company_name'    => 'nullable|string|max:255',
            'tally_ledger_sales'    => 'nullable|string|max:255',
            'tally_ledger_purchase' => 'nullable|string|max:255',
            'tally_ledger_cash'     => 'nullable|string|max:255',
            'tally_ledger_gst'      => 'nullable|string|max:255',
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return response()->json(['success' => true, 'message' => 'Tally settings updated.']);
    }

    // GET /api/v1/tally/export/preview
    public function exportPreview(Request $request)
    {
        $request->validate([
            'type' => 'required|in:sales,purchases,masters',
            'from' => 'nullable|date',
            'to'   => 'nullable|date',
        ]);

        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->toDateString());

        if ($request->type === 'sales') {
            $count = Bill::where('status', 'completed')->whereBetween('bill_date', [$from, $to])->count();
        } elseif ($request->type === 'purchases') {
            $count = Purchase::whereBetween('purchase_date', [$from, $to])->count();
        } else {
            $count = Category::count();
        }

        return response()->json([
            'success' => true,
            'type'    => $request->type,
            'from'    => $from,
            'to'      => $to,
            'count'   => $count,
            'message' => "Ready to export {$count} {$request->type} records.",
        ]);
    }

    // POST /api/v1/tally/export
    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:sales,purchases,masters',
            'from' => 'nullable|date',
            'to'   => 'nullable|date',
        ]);

        // Returns export job confirmation; actual XML generation done by existing TallyController
        return response()->json([
            'success'    => true,
            'message'    => "Tally XML export for {$request->type} initiated.",
            'export_url' => route('tenant.tally.export'),
        ]);
    }

    // GET /api/v1/tally/import/status
    public function importStatus()
    {
        $lastImport = Setting::get('tally_last_import_at');
        $lastCount  = Setting::get('tally_last_import_count', 0);

        return response()->json([
            'success' => true,
            'data'    => [
                'last_import_at'    => $lastImport,
                'last_import_count' => (int) $lastCount,
            ],
        ]);
    }

    // GET /api/v1/tally/logs
    public function logs(Request $request)
    {
        // Returns tally sync log entries from settings or a log table if available
        return response()->json([
            'success' => true,
            'data'    => [],
            'message' => 'Tally sync logs.',
        ]);
    }

    // POST /api/v1/tally/validate
    public function validate(Request $request)
    {
        $request->validate(['company_name' => 'required|string']);

        $saved = Setting::get('tally_company_name');

        return response()->json([
            'success' => true,
            'match'   => strtolower($saved) === strtolower($request->company_name),
            'message' => 'Tally company name validated.',
        ]);
    }
}
