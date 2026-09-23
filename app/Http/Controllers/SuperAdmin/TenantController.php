<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    /**
     * Display a listing of tenants.
     */
    public function index(): View
    {
        $tenants = Tenant::with('domains')->get();
        return view('superadmin.tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new tenant.
     */
    public function create(): View
    {
        return view('superadmin.tenants.create');
    }

    /**
     * Store a newly created tenant.
     */
    public function store(Request $request)
    {
        // Automatically append central domain if only a subdomain is provided
        $domain = $request->domain;
        if ($domain && !str_contains($domain, '.')) {
            $centralDomain = config('tenancy.central_domains')[0] ?? 'localhost';
            $request->merge(['domain' => $domain . '.' . $centralDomain]);
        }

        $request->validate([
            'id' => 'required|string|unique:tenants,id|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
            'domain' => 'required|string|unique:domains,domain|max:255',
            'plan' => 'required|string',
        ]);

        // Create Tenant
        $tenant = Tenant::create([
            'id' => $request->id,
            'name' => $request->name,
            'email' => $request->email,
            'plan' => $request->plan,
            'status' => 'active',
        ]);

        // Create Domain
        $tenant->domains()->create([
            'domain' => $request->domain,
        ]);

        // Initialize Tenancy to create the Tenant Admin User
        tenancy()->initialize($tenant);

        \App\Models\TenantUser::updateOrCreate(
            ['email' => $request->email],
            [
                'name'        => 'Store Admin',
                'password'    => $request->password,
                'role'        => 'branch_admin',
                'is_active'   => true,
                'full_access' => true,
            ]
        );

        tenancy()->end();

        return redirect()->route('superadmin.tenants.index')
            ->with('status', "Tenant '{$tenant->name}' created successfully with domain '{$request->domain}'.");
    }

    /**
     * Show tenant details.
     */
    public function show(Tenant $tenant): View
    {
        $tenant->load('domains');
        return view('superadmin.tenants.show', compact('tenant'));
    }

    /**
     * Show the form for editing a tenant.
     */
    public function edit(Tenant $tenant): View
    {
        $tenant->load('domains');
        return view('superadmin.tenants.edit', compact('tenant'));
    }

    /**
     * Update the specified tenant.
     */
    public function update(Request $request, Tenant $tenant)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'plan'  => 'required|string',
        ]);

        $tenant->update([
            'name'  => $request->name,
            'email' => $request->email,
            'plan'  => $request->plan,
        ]);

        return redirect()->route('superadmin.tenants.index')
            ->with('status', "Tenant '{$tenant->name}' updated successfully.");
    }

    /**
     * Toggle tenant active/inactive status.
     */
    public function toggleStatus(Tenant $tenant)
    {
        $newStatus = $tenant->status === 'active' ? 'inactive' : 'active';
        $tenant->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'status'  => $newStatus,
            'message' => "Tenant {$newStatus}d successfully.",
        ]);
    }

    /**
     * Remove the specified tenant from storage.
     */
    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        return redirect()->route('superadmin.tenants.index')
            ->with('status', 'Tenant deleted successfully.');
    }

    public function backup(Tenant $tenant)
    {
        \Illuminate\Support\Facades\Artisan::call('tenant:backup', ['tenant' => $tenant->id]);
        
        $directory = storage_path('app/backups/tenants');
        // Search for the newly created .zip file
        $files = glob($directory . '/' . $tenant->id . '-*.zip');
        if (empty($files)) {
            return back()->with('error', 'Backup failed to generate.');
        }
        
        $latest_file = null;
        $latest_time = 0;
        foreach($files as $file) {
            if (filemtime($file) > $latest_time) {
                $latest_time = filemtime($file);
                $latest_file = $file;
            }
        }
        
        return response()->download($latest_file);
    }

    public function restore(Request $request, Tenant $tenant)
    {
        $request->validate(['backup_file' => 'required|file']);
        
        $file = $request->file('backup_file');
        
        // If it's a zip file, we need to extract it first
        $extension = $file->getClientOriginalExtension();
        if ($extension === 'zip') {
            $zip = new \ZipArchive;
            $res = $zip->open($file->getRealPath());
            if ($res === TRUE) {
                // Assuming the zip contains exactly one .sql file
                $sqlFilename = $zip->getNameIndex(0);
                $extractPath = storage_path('app/backups/temp/');
                $zip->extractTo($extractPath);
                $zip->close();
                $fullPath = $extractPath . $sqlFilename;
            } else {
                return back()->with('error', 'Failed to open the zip file.');
            }
        } else {
            $path = $file->storeAs('backups/temp', $tenant->id . '-restore.sql');
            $fullPath = storage_path('app/' . $path);
        }
        
        \Illuminate\Support\Facades\Artisan::call('tenant:restore', [
            'tenant' => $tenant->id,
            'file' => $fullPath
        ]);
        
        @unlink($fullPath);
        
        return back()->with('status', 'Backup imported successfully.');
    }
}
