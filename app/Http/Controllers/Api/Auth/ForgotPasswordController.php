<?php
namespace App\Http\Controllers\Api\Auth;
use App\Http\Controllers\Controller;
use App\Notifications\PasswordForgotNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
//    public function forgotPassword(Request $request)
//    {
//        $validate = Validator::make($request->all(),[
//            'email' => 'required|email|exists:users',
//        ]);
//        if($validate->fails()){
//            return response()->json([
//                'errors' => $validate->errors()
//            ],422);
//        }
//        $email = $request->email;
//        $token = Str::random(65);
//        DB::table('password_resets')->insert([
//            'email' => $email,
//            'token' => $token,
//            'created_at' => now()->addHour(1)
//        ]);
//        //mail send
//        Mail::send('mail.password_reset',['token'=> $token ], function ($msg) use ($email){
//            $msg->to($email);
//            $msg->subject('Password Reset Email');
//        });
//        return response()->json([
//            'message' => 'Password Reset Link Send Successfully,Please check your mail'
//        ],200);
//    }
    public function forgotPassword(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|email|exists:users',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'errors' => $validate->errors()
            ], 422);
        }

        $email = $request->email;
        $token = Str::random(65);
        // Check if a record for the email already exists
        $existingReset = DB::table('password_resets')->where('email', $email)->first();
        if ($existingReset) {
            // If a record exists, update the token and created_at time
            DB::table('password_resets')
                ->where('email', $email)
                ->update([
                    'token' => $token,
                    'created_at' => now()->addHour(1)
                ]);
        } else {
            // If no record exists, insert a new one
            DB::table('password_resets')->insert([
                'email' => $email,
                'token' => $token,
                'created_at' => now()->addHour(1)
            ]);
        }
        // Mail send
        Mail::send('mail.password_reset', ['token' => $token], function ($msg) use ($email) {
            $msg->to($email);
            $msg->subject('Password Reset Email');
        });
        return response()->json([
            'message' => 'Password Reset Link Sent Successfully. Please check your email.'
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $validate = Validator::make($request->all(),[
            'password' => 'required|min:4|confirmed',
            'token' => 'required|exists:password_resets',
        ]);
        if($validate->fails()){
            return response()->json([
                'errors' => $validate->errors()
            ],422);
        }

        $token = DB::table('password_resets')->where('token',$request->token)->first();
        if($token){
            DB::table('users')->where('email',$token->email)->update([
                'password' => bcrypt($request->password)
            ]);
            DB::table('password_resets')->where('email',$token->email)->delete();
            return response()->json([
                'message' => 'Password Reset Successfully'
            ],200);
        }else{
            return response()->json([
                'message' => 'Token Expired'
            ],422);

        }


    }
}
