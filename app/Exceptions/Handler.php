<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Traits\ApiResponseTrait;
use App\Exceptions\CompteNotFoundException;

class Handler extends ExceptionHandler
{
    use ApiResponseTrait;

     /**
      * The list of the inputs that are never flashed to the session on validation exceptions.
      *
      * @var array<int, string>
      */
     protected $dontFlash = [
         'current_password',
         'password',
         'password_confirmation',
     ];

     /**
      * Register the exception handling callbacks for the application.
      */
     public function register(): void
     {
         $this->renderable(function (CompteNotFoundException $e, $request) {
             if ($request->expectsJson()) {
                 return $this->errorResponse($e->getMessage(), $e->getCode());
             }
         });

         $this->reportable(function (Throwable $e) {
             //
         });
     }
}
