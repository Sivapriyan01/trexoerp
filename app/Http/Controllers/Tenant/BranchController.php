<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $branch = Branch::create([
            'name' => $request->name,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Branch created successfully',
            'branch' => $branch
        ]);
    }
}
