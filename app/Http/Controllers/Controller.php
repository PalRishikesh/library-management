<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function respondWithSuccess($data = null)
    {
        return response()->json(['success' => true,'data' =>  $data], Response::HTTP_OK);
    }
    public function respondWithValidationError($error= null)
    {
        return response()->json(['success' => false,'error' => $error],Response::HTTP_UNPROCESSABLE_ENTITY);
    }
    public function respondWithError($error_message = null,$data = null)
    {
        $error = ($error_message) ? $error_message : "Something went wrong.";
        return response()->json(['success' => false,'error' =>  $error,'data'=>$data], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

}
