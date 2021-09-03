<?php

namespace App\Http\Controllers;

use App\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    public function index(Request $request){
        DB::beginTransaction();
        try {
            $books = Book::all();
            return $this->respondWithSuccess($books);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $this->respondWithError($e);
        }
    }

    public function store(Request $request){
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                "book_name" => "required|unique:books,book_name",
                "author" => "required",
                "cover_image" => "required"
            ]);
            if ($validator->fails()) {
                return $this->respondWithValidationError($validator->errors()->first());
            } 
            $path = $request->file('cover_image')->store('public/images');
            $book = new Book();
            $book->book_name = $request->book_name;
            $book->author = $request->author;
            $book->cover_image = $path;
            $book->save();
            DB::commit();
            if ($book) {
                return $this->respondWithSuccess($book);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return $this->respondWithError($e);
        }
    }

    public function edit(Request $request,$id){
        DB::beginTransaction();
        try {
            $book = Book::where("b_id", $id)->first();
            if (!$book) {
                return $this->respondWithError("book not found");
            }
            return $this->respondWithSuccess($book);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $this->respondWithError($e);
        }
    }

    public function update(Request $request,$id){
        DB::beginTransaction();
        try {
            $book = Book::find($id);
            $validator = Validator::make($request->all(), [
                "book_name" => "required",Rule::unique('books')->ignore($book->b_id, 'b_id'),
                "author" => "required",
                "cover_image" => "nullable"
            ]);
            if ($validator->fails()) {
                return $this->respondWithValidationError($validator->errors()->first());
            } 
            $book->book_name = $request->book_name;
            $book->author = $request->author;
            if($request->hasFile('cover_image')){
                $path = $request->file('cover_image')->store('public/images');
                $book->cover_image = $path;
            }
            $book->save();
            DB::commit();
            if ($book) {
                return $this->respondWithSuccess($book);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return $this->respondWithError($e->getMessage());
        }
    }

    public function delete(Request $request,$id){
        DB::beginTransaction();
        try {
            $book = Book::where("b_id", $id)->delete();
            DB::commit();
            if ($book) {
                return $this->respondWithSuccess("book's deleted successfully");
            }
            return $this->respondWithError();
        } catch (\Exception $e) {
            DB::rollback();
            return $this->respondWithError($e);
        }
    }
}
