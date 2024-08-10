<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'isOwner'])->only(['store', 'update', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::latest('updated_at')->get();

        return response()->json([
            'message' => 'Berhasil Tampil semua category',
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        Category::create(['name' => $request->name]);

        return response()->json(['message' => 'Berhasil tambah category']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $category = Category::with('list_books')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Category tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'message' => "Berhasil Detail data dengan id $id",
            'data' => $category
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $category = Category::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Category tidak ditemukan'
            ], 404);
        }

        $category->update(['name' => $request->name]);

        return response()->json([
            'message' => "Berhasil melakukan update Category id : $id"
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {
            $category = Category::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Category tidak ditemukan'
            ], 404);
        }

        $category->delete();

        return response()->json([
            'message' => "data dengan id : $id berhasil terhapus"
        ]);
    }
}
