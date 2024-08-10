<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Borrow;
use App\Models\Book;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BorrowController extends Controller
{
    public function index()
    {
        $borrows = Borrow::with('user', 'book')->latest('updated_at')->get();

        return response()->json([
            'message' => 'Semua Peminjaman',
            'data' => $borrows
        ]);
    }

    public function updateOrCreateBorrow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_id' => 'required|exists:books,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $book = Book::findOrFail($request->book_id);

            // Find an existing borrow record for the user and book, or create a new one
            $borrow = Borrow::firstOrNew([
                'user_id' => auth()->id(),
                'book_id' => $request->book_id,
            ]);

            if ($borrow->exists && $borrow->barrow_date === null) {
                // If the book is currently borrowed (barrow_date is null), update the barrow_date (return date)
                $borrow->barrow_date = now();
                $borrow->save();

                // Increase the stock of the book by 1 (since the book is being returned)
                $book->stok += 1;
                $book->save();

                return response()->json([
                    'message' => 'Tanggal pengembalian berhasil diupdate, stok buku bertambah',
                    'data' => $borrow
                ], 200);

            } elseif ($borrow->exists && $borrow->barrow_date !== null) {
                // If the book was previously borrowed and returned, allow it to be borrowed again
                if ($book->stok < 1) {
                    return response()->json([
                        'message' => 'Stok buku habis'
                    ], 400);
                }

                // Reset the borrow record for a new borrowing cycle
                $borrow->load_date = now();
                $borrow->barrow_date = null;
                $borrow->save();

                // Reduce the stock of the book by 1
                $book->stok -= 1;
                $book->save();

                return response()->json([
                    'message' => 'Peminjaman berhasil dibuat kembali, stok buku berkurang',
                    'data' => $borrow
                ], 200);

            } else {
                // If no borrow record exists, create a new one with the current load_date (borrow date)
                if ($book->stok < 1) {
                    return response()->json([
                        'message' => 'Stok buku habis'
                    ], 400);
                }

                $borrow = Borrow::create([
                    'user_id' => auth()->id(),
                    'book_id' => $request->book_id,
                    'load_date' => now(),
                    'barrow_date' => null, // Initially, barrow_date is null since the book is just being borrowed
                ]);

                // Reduce the stock of the book by 1
                $book->stok -= 1;
                $book->save();

                return response()->json([
                    'message' => 'Peminjaman berhasil dibuat, stok buku berkurang',
                    'data' => $borrow
                ], 200);
            }

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Buku tidak ditemukan'
            ], 404);
        }
    }

}
