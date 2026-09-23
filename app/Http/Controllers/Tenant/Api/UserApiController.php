<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Models\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserApiController extends Controller
{
    // GET /api/v1/users
    public function index(Request $request)
    {
        $query = TenantUser::orderBy('name');

        if ($request->filled('q')) {
            $query->where('name', 'ilike', '%' . $request->q . '%')
                  ->orWhere('email', 'ilike', '%' . $request->q . '%');
        }

        $users = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $users->items(),
            'meta'    => ['current_page' => $users->currentPage(), 'last_page' => $users->lastPage(), 'total' => $users->total()],
        ]);
    }

    // POST /api/v1/users
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:8',
            'role'        => 'nullable|string|max:100',
            'permissions' => 'nullable|array',
        ]);

        $user = TenantUser::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'password'    => Hash::make($data['password']),
            'role'        => $data['role'] ?? 'staff',
            'permissions' => $data['permissions'] ?? [],
        ]);

        return response()->json(['success' => true, 'data' => $user->makeHidden('password')], 201);
    }

    // GET /api/v1/users/{id}
    public function show($id)
    {
        $user = TenantUser::find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $user->makeHidden('password')]);
    }

    // PUT /api/v1/users/{id}
    public function update(Request $request, $id)
    {
        $user = TenantUser::find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'email'       => 'sometimes|email|unique:users,email,' . $id,
            'password'    => 'sometimes|string|min:8',
            'role'        => 'sometimes|nullable|string|max:100',
            'permissions' => 'sometimes|nullable|array',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json(['success' => true, 'data' => $user->makeHidden('password')]);
    }

    // DELETE /api/v1/users/{id}
    public function destroy($id)
    {
        $user = TenantUser::find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        if ($user->isStoreAdmin()) {
            return response()->json(['success' => false, 'message' => 'The Store Admin account is protected and cannot be deleted.'], 403);
        }

        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Cannot delete your own account.'], 403);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'User deleted.']);
    }

    // GET /api/v1/users/{id}/permissions
    public function permissions($id)
    {
        $user = TenantUser::find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $user->permissions ?? []]);
    }
}
