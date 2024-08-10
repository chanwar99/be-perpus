<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::latest('updated_at')->get();

        return response()->json([
            'message' => 'Data berhasil ditampilkan',
            'data' => $roles
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        Role::create([
            'name' => $request->name
        ]);

        return response()->json([
            'message' => 'Data berhasil ditambahkan'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $role = Role::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Role tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'message' => "Berhasil Detail data dengan id $id",
            'data' => $role
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => "required|string|max:255|unique:roles,name,$id",
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $role = Role::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Role tidak ditemukan'
            ], 404);
        }

        $role->update([
            'name' => $request->name
        ]);

        return response()->json([
            'message' => 'Data berhasil Diupdate'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $role = Role::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Role tidak ditemukan'
            ], 404);
        }

        $role->delete();

        return response()->json([
            'message' => 'Data Detail berhasil Dihapus'
        ]);
    }
}
