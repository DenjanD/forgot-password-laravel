<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ForgotRequest;
use App\Http\Requests\ResetRequest;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPasswordMail;
use Illuminate\Support\Facades\Hash;

class ForgotController extends Controller
{
    public function forgot (ForgotRequest $request) {
        $email = $request->input('email');

        if (User::where('email', $email)->doesntExist()) {
            return response([
                'msg' => 'User doesnt exists'
            ],404);
        }

        $token = Str::random(10);

        try {
            DB::table('password_resets')->insert([
                'email' => $email,
                'token' => $token
            ]);

            //send email
            Mail::to($email)->send(new ForgotPasswordMail($email,$token));

            return response([
                'msg' => 'Check your email'
            ]);
        }catch (\Exception $exception) {
            return response([
                'msg' => $exception->getMessage()
            ], 400);
        }
    }

    public function reset (ResetRequest $request) {
        $token = $request->input('token');

        if (!$passwordResets = DB::table('password_resets')->where('token', $token)->first()) {
            return response([
                'msg' => 'Invalid Token'
            ]);
        }

        /** @var User $user */
        if (!$user = User::where('email', $passwordResets->email)->first()) {
            return response([
                'msg' => 'User doesnt exists'
            ]);
        }

        $user->password = Hash::make($request->input('password'));
        $user->save;

        return response([
            'msg' => 'success'
        ]);
    }
}
