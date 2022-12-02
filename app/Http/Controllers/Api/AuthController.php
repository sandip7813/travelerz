<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;

use Auth;
use Validator;
use Carbon\Carbon;

use App\Models\OtpVerification;

use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerificationOtp;
use App\Mail\OtpVerificationSuccessful;

class AuthController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api', ['except' => ['login', 'register', 'verifyOtp', 'resendOtp']]);
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'phone' => 'required|numeric|digits:10|unique:users'
        ]);

        if( $validator->fails() ){
            return response()->json($validator->errors()->toJson(), 422);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            [
                'password' => bcrypt($request->password),
                'date_of_birth' => ( $request->date_of_birth && !empty($request->date_of_birth) ) ? Carbon::createFromFormat('d/m/Y', $request->date_of_birth)->format('Y-m-d') : NULL,
            ],
        ));

        //++++++++++++++++++++ SEND OTP EMAIL & UPDATE DATABASE :: Start ++++++++++++++++++++//
        $email_otp = OtpVerification::generate_otp();
        
        $mailDetails = [
            'name' => $request->name,
            'otp' => $email_otp
        ];

        Mail::to($request->email)->send(new EmailVerificationOtp($mailDetails));

        OtpVerification::create([
            'otp' => $email_otp,
            'user_uuid' => $user->uuid,
            'otp_type' => 'email'
        ]);
        //++++++++++++++++++++ SEND OTP EMAIL & UPDATE DATABASE :: End ++++++++++++++++++++//

        return response()->json([
            'message' => 'Registration Successful! OTP Sent!',
            'user_uuid' => $user->uuid
        ], 201);
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        if( $validator->fails() ){
            return response()->json($validator->errors()->toJson(), 422);
        }

        $token = auth('api')->attempt($validator->validated());

        if( !$token ){
            return response()->json(['error' => true, 'message' => 'Unauthorized'], 401);
        }

        if( !auth('api')->attempt(['email' => $request->email, 'password' => $request->password, 'status' => 1]) ){
            return response()->json(['error' => true, 'message' => 'User Not Active'], 400);
        }

        if( !auth('api')->attempt(['email' => $request->email, 'password' => $request->password, 'role' => 0]) ){
            return response()->json(['error' => true, 'message' => 'Invalid User'], 400);
        }

        return $this->createToken($token);
    }

    public function myProfile() {
        return response()->json(auth('api')->user());
    }

    public function logout(){
        auth('api')->logout();
        return response()->json(['message' => 'User signed out successfully!']);
    }

    public function createToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL()*60,
            'user' => auth('api')->user()
        ]);
    }

    public function refreshToken() {
        return $this->createToken(auth('api')->refresh());
    }

    public function verifyOtp(Request $request){
        $validator = Validator::make($request->all(), [
            'user_uuid' => 'required',
            'otp' => 'required'
        ]);
    
        if( $validator->fails() ){
            return response()->json($validator->errors()->toJson(), 400);
        }
    
        $user_uuid = $request->user_uuid ?? null;
        $otp = $request->otp ?? null;
    
        $verify_otp = OtpVerification::where('otp', $otp)
                                        ->where('user_uuid', $user_uuid)
                                        ->where('status', '!=', 'cancelled')
                                        ->first();
        
        if( !isset($verify_otp->id) ){
            return response()->json(['error' => true, 'message' => 'Incorrect OTP'], 400);
        }
        if($verify_otp->status == 'used'){
            return response()->json(['error' => true, 'message' => 'OTP was used!'], 400);
        }

        $current_time = \Carbon\Carbon::now();
    
        $otp_created_in_minutes = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $verify_otp->created_at)->diffInMinutes($current_time);
    
        if( $otp_created_in_minutes > 5 ){
            return response()->json(['error' => true, 'message' => 'OTP Expired!'], 400);
        }

        //+++++++++++++++ UPDATE OTP TABLE :: Start +++++++++++++++//
        $verify_otp->status = 'used';
        $verify_otp->save();
        //+++++++++++++++ UPDATE OTP TABLE :: End +++++++++++++++//

        //+++++++++++++++ UPDATE USER TABLE :: Start +++++++++++++++//
        $otp_type = $verify_otp->otp_type;

        $user = User::where('uuid', $user_uuid)->first();

        if( in_array($otp_type, ['email', 'email_and_mobile']) ){
            $user->email_verified_at = $current_time;
        }
        if( in_array($otp_type, ['phone', 'email_and_mobile']) ){
            $user->phone_verified_at = $current_time;
        }

        $user->status = 1;
        $user->save();
        //+++++++++++++++ UPDATE USER TABLE :: End +++++++++++++++//

        //+++++++++++++++ VERIFICATION SUCCESSFUL EMAIL :: Start +++++++++++++++//
        $mailDetails = [
            'name' => $user->name
        ];

        Mail::to($user->email)->send(new OtpVerificationSuccessful($mailDetails));
        //+++++++++++++++ VERIFICATION SUCCESSFUL EMAIL :: End +++++++++++++++//

        return response()->json([
            'message' => 'User verification successful!',
            'user_uuid' => $user_uuid
        ], 201);
    }

    public function resendOtp(Request $request){
        $user_uuid = $request->user_uuid ?? null;
        $otp_type = $request->otp_type ?? 'email';

        $user = User::where('uuid', $user_uuid)->first();

        if( !isset($user->id) ){
            return response()->json(['error' => true, 'message' => 'User not found!'], 400);
        }
        if( in_array($otp_type, ['email', 'email_and_mobile']) && !is_null($user->email_verified_at) ){
            return response()->json(['error' => true, 'message' => 'User email id already verified!'], 400);
        }
        
        $email_otp = OtpVerification::generate_otp();
        
        if( in_array($otp_type, ['email', 'email_and_mobile']) ){
            $mailDetails = [
                'name' => $user->name,
                'otp' => $email_otp
            ];

            Mail::to($user->email)->send(new EmailVerificationOtp($mailDetails));

            OtpVerification::where('user_uuid', $user_uuid)->update(['status' => 'cancelled']);

            OtpVerification::create([
                'otp' => $email_otp,
                'user_uuid' => $user_uuid,
                'otp_type' => 'email'
            ]);
        }

        return response()->json([
            'message' => 'OTP sent successfully!',
            'user_uuid' => $user_uuid
        ], 201);
    }
}
