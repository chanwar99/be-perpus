<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Book;
use Illuminate\Support\Str;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BookController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'isOwner'])->only(['store', 'update', 'destroy']);
    }

    public function index()
    {
        $books = Book::with('category')->latest('updated_at')->get();

        return response()->json([
            'message' => 'Berhasil Tampil semua book',
            'data' => $books
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'summary' => 'required|string',
            'image' => 'nullable|max:2048',
            'stok' => 'required|integer',
            'category_id' => 'required|exists:categories,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Handle image upload
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $uploadedFileUrl = Cloudinary::upload($request->file('image')->getRealPath())->getSecurePath();
            $imageUrl = $uploadedFileUrl;
        }

        Book::create([
            'title' => $request->title,
            'summary' => $request->summary,
            'image' => $imageUrl,
            'stok' => $request->stok,
            'category_id' => $request->category_id,
        ]);

        return response()->json(['message' => 'Berhasil tambah book']);
    }

    public function show(string $id)
    {
        try {
            $book = Book::with(['category', 'list_barrows'])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Buku tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => "Berhasil Detail data dengan id $id",
            'data' => $book
        ]);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'summary' => 'required|string',
            'image' => 'nullable|max:2048',
            'stok' => 'required|integer',
            'category_id' => 'required|exists:categories,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $book = Book::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Buku tidak ditemukan'], 404);
        }

        // Handle image upload and delete old image if a new one is provided
        $imageUrl = $book->image;
        if ($request->hasFile('image')) {
            if ($book->image) {
                // Optionally, you can delete the old image from Cloudinary
                $publicId = pathinfo($book->image, PATHINFO_FILENAME);
                Cloudinary::destroy($publicId);
            }

            $uploadedFileUrl = Cloudinary::upload($request->file('image')->getRealPath())->getSecurePath();
            $imageUrl = $uploadedFileUrl;
        }

        $book->update([
            'title' => $request->title,
            'summary' => $request->summary,
            'image' => $imageUrl,
            'stok' => $request->stok,
            'category_id' => $request->category_id,
        ]);

        return response()->json(['message' => "Berhasil melakukan update Book id : $id"]);
    }

    public function destroy(string $id)
    {
        try {
            $book = Book::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Buku tidak ditemukan'], 404);
        }

        // Delete the image from Cloudinary
        if ($book->image) {
            $publicId = pathinfo($book->image, PATHINFO_FILENAME);
            Cloudinary::destroy($publicId);
        }

        $book->delete();

        return response()->json(['message' => "data dengan id : $id berhasil terhapus"]);
    }
}

