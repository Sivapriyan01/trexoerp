<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = \App\Models\TenantUser::latest()->get();
        return view('tenant.users.index', compact('users'));
    }

    public function create()
    {
        return view('tenant.users.create');
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name'     => 'required|string|max:255',
                'username' => 'nullable|string|max:255|unique:users,username',
                'email'    => 'required|email|unique:users,email',
                'phone'    => 'nullable|string|max:20',
                'address'  => 'nullable|string',
                'password' => 'required|string|min:6',
                'role'     => 'required|string',
                'branch_id'=> 'nullable|exists:branches,id',
                'permissions' => 'nullable|array'
            ]);

            // Password automatically hashed by model cast
            $data['is_active'] = $request->has('is_active') && $request->input('is_active') !== '0';
            $data['full_access'] = $request->has('full_access') && $request->input('full_access') !== '0';
            
            \App\Models\TenantUser::create($data);

            return redirect()->route('tenant.users.index')->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('User creation failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $user = \App\Models\TenantUser::findOrFail($id);
        return view('tenant.users.index', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = \App\Models\TenantUser::findOrFail($id);
        
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,'.$id,
            'email'    => 'required|email|unique:users,email,'.$id,
            'phone'    => 'nullable|string|max:20',
            'address'  => 'nullable|string',
            'password' => 'nullable|string|min:6',
            'role'     => 'required|string',
            'branch_id'=> 'nullable|exists:branches,id',
            'permissions' => 'nullable|array'
        ]);

        if (!$request->filled('password')) {
            unset($data['password']);
        }

        $data['is_active'] = $request->has('is_active') && $request->input('is_active') !== '0';
        $data['full_access'] = $request->has('full_access') && $request->input('full_access') !== '0';
        $data['permissions'] = $request->input('permissions', []);

        $user->update($data);

        return redirect()->route('tenant.users.index')->with('success', 'User updated successfully.');
    }

    public function show($id)
    {
        $user = \App\Models\TenantUser::findOrFail($id);
        return view('tenant.users.index', compact('user'));
    }

    public function destroy($id)
    {
        $user = \App\Models\TenantUser::findOrFail($id);

        if ($user->isStoreAdmin()) {
            return redirect()->route('tenant.users.index')->with('error', 'The Store Admin account is protected and cannot be deleted.');
        }

        if (auth('tenant')->check() && auth('tenant')->id() === $user->id) {
            return redirect()->route('tenant.users.index')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->route('tenant.users.index')->with('success', 'User deleted successfully.');
    }
}
