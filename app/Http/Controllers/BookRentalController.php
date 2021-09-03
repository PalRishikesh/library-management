<?php

namespace App\Http\Controllers;

use App\Book;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookRentalController extends Controller
{
    public function user(Request $request)
    {
        try {
            $users = User::select("u_id", "firstname", "mobile", "email");
            if ($request->filled('mobile')) {
                $users = $users->where('mobile', 'like', '%' . $request->mobile . '%');
            }
            if ($request->filled('email')) {
                $users = $users->where('email', 'like', '%' . $request->email . '%');
            }
            $returnData = $users->get();
            return $this->respondWithSuccess($returnData);
        } catch (\Exception $e) {
            return $this->respondWithError($e);
        }
    }

    public function book(Request $request)
    {
        try {
            $books = Book::select("b_id", "book_name", "author", "cover_image");
            if ($request->filled('book_name')) {
                $books = $books->where('book_name', 'like', '%' . $request->book_name . '%');
            }
            if ($request->filled('author')) {
                $books = $books->where('author', 'like', '%' . $request->author . '%');
            }
            $returnData = $books->get();
            return $this->respondWithSuccess($returnData);
        } catch (\Exception $e) {
            return $this->respondWithError($e);
        }
    }
    public function userBook(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                "user_id" => "required|exists:users,u_id",
                "book_id" => "required|exists:books,b_id",
                "issue_date" => "required|date_format:Y-m-d",
            ]);
            if ($validator->fails()) {
                return $this->respondWithValidationError($validator->errors()->first());
            }
            $userBooking = DB::table('user_books')->select("id")->where(['status' => 0, "user_id" => $request->user_id, "book_id" => $request->book_id])->get();
            if (count($userBooking)) {
                return $this->respondWithError("This books is already issues");
            }
            $now = \Carbon\Carbon::now()->format('Y-m-d');
            DB::table('user_books')->insert(["user_id" => $request->user_id, "book_id" => $request->book_id, "issue_date" => $request->issue_date, "created_at" => $now]);
            DB::commit();
            return $this->respondWithSuccess();
        } catch (\Exception $e) {
            DB::rollback();
            return $this->respondWithError($e);
        }
    }
    public function returnUserBook(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                "return_date" => "required|date_format:Y-m-d",
            ]);
            if ($validator->fails()) {
                return $this->respondWithValidationError($validator->errors()->first());
            }
            $returnDate = $request->return_date;
            $userBooking = DB::table('user_books')->select("id", "user_id", "book_id", "issue_date", "status")->where(['status' => 0, "id" => $id])->get();
            if (!count($userBooking)) {
                return $this->respondWithError();
            }
            DB::table('user_books')->where("id", $id)->update(["return_date" => $returnDate, "status" => 1]);
            DB::commit();
            return $this->respondWithSuccess();
        } catch (\Exception $e) {
            DB::rollback();
            return $this->respondWithError($e);
        }
    }
    public function getUserBook(Request $request)
    {
        try {
            $returnData = DB::table('user_books')->select("id", "user_id", "book_id", "issue_date")->where('status', 0)->get();
            return $this->respondWithSuccess($returnData);
        } catch (\Exception $e) {
            return $this->respondWithError($e);
        }
    }

    public function userRentalBook(Request $request, $id = null)
    {
        try {
            $user = User::query();
            if (isset($id)) {
                $user = $user->where("u_id", $id);
            }
            $returnData = $user->with('books')->get();
            return $this->respondWithSuccess($returnData);
        } catch (\Exception $e) {
            return $this->respondWithError($e);
        }
    }
}
