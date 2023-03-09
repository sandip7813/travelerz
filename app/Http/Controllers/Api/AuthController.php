<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\OtpVerification;
use App\Models\PasswordReset;

use Auth;
use Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerificationOtp;
use App\Mail\OtpVerificationSuccessful;
use App\Mail\ResetPassword;
use App\Mail\PasswordChangedSuccessful;

class AuthController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api', ['except' => ['login', 'register', 'verifyOtp', 'resendOtp', 'forgetPassword', 'resetPassword', 'resetPasswordSubmit', 'socialLogin']]);
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
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if( !auth('api')->attempt(['email' => $request->email, 'password' => $request->password, 'status' => 1]) ){
            $inactive_user = User::where('email', $request->email)->first();
            return response()->json(['success' => false, 'user_uuid' => $inactive_user->uuid ?? null, 'message' => 'User Not Active'], 400);
        }

        if( !auth('api')->attempt(['email' => $request->email, 'password' => $request->password, 'role' => 0]) ){
            return response()->json(['success' => false, 'message' => 'Invalid User'], 400);
        }

        return $this->createToken($token);
    }

    /* 
    // Google login
    public function redirectToGoogle()
    {
        //return Socialite::driver('google')->redirect();
        return Socialite::driver('google')->stateless()->user();
    }

    // Google callback
    public function handleGoogleCallback()
    {
        $user = Socialite::driver('google')->user();

        $this->_registerOrLoginUser($user);

        // Return home after login
        return redirect()->route('home');
    }

    // Facebook login
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();

        // Socialite::with('facebook')->stateless()->redirect()->getTargetUrl()

        // $fb_user = Socialite::with('facebook')->stateless()->user();
    }

    // Facebook callback
    public function handleFacebookCallback()
    {
        $user = Socialite::driver('facebook')->user();

        $this->_registerOrLoginUser($user);

        // Return home after login
        return redirect()->route('home');
    } 

    protected function _registerOrLoginUser($data)
    {
        // $user = User::where('email', '=', $data->email)->first();
        // if (!$user) {
        //     $user = new User();
        //     $user->name = $data->name;
        //     $user->email = $data->email;
        //     $user->provider_id = $data->id;
        //     $user->avatar = $data->avatar;
        //     $user->save();
        // }

        // Auth::login($user);

        print_r($data); exit;
    }
    */

    /**
     * Social Login
     */
    public function socialLogin(Request $request){
        /* $provider = 'facebook'; // or $request->input('provider_name') for multiple providers
        $token = $request->input('access_token');
        $providerUser = Socialite::driver($provider)->userFromToken($token);
        $user = User::where('provider_name', $provider)->where('provider_id', $providerUser->id)->first();
        if($user == null){
            $user = User::create([
                'provider_name' => $provider,
                'provider_id' => $providerUser->id,
            ]);
        }
        $token = $user->createToken(env('APP_NAME'))->accessToken;
        return response()->json([
            'success' => true,
            'token' => $token
        ]); */

        $provider_name = $request->input('provider_name', 'facebook');
        $access_token = $request->access_token ?? null;

        if( is_null($access_token) ){
            return response()->json(['success' => false, 'message' => 'No access token received!'], 400);
        }

        $provider = '';
        if( strpos($provider_name, 'facebook') !== false ) {
            $provider = 'facebook';
        }
        elseif( strpos($provider_name, 'google') !== false ) {
            $provider = 'google';
        }

        $providerUser = Socialite::driver($provider)->stateless()->userFromToken($access_token);

        // \Log::Info( print_r($providerUser, true) );

        $user_exists = User::where('email', '=', $providerUser->email)
                    ->where('provider_name', '!=', $provider)
                    ->exists();

        if($user_exists){
            return response()->json(['success' => false, 'message' => 'This email id is already in use!'], 400);
        }

        $user = User::where('provider_name', $provider)
                    ->where('provider_id', $providerUser->id)
                    ->first();

        $random_password = OtpVerification::generate_otp();
        
        if (!isset($user->id)) {
            $user = new User();
            $user->name = $providerUser->name;
            $user->email = $providerUser->email;
            $user->password = bcrypt($random_password);
            $user->provider_name = $provider;
            $user->provider_id = $providerUser->id;
            $user->status = '1';
            $user->save();
        }

        /* $userCred = ['email' => $providerUser->email, 'password' => $random_password];
        $token = auth('api')->attempt($userCred);
        \Log::Info('Token: ' . $token); */
        /* $createToken = $this->createToken($token);
        \Log::Info( print_r($createToken, true) ); */

        //Auth::login($user);
        //auth('api')->login($user);

        //$loggedInUser = auth('api')->attempt(['email' => $providerUser->email, 'password' => $random_password]);
        /* $loggedInUser = auth('api')->user();
        \Log::Info( print_r($loggedInUser, true) );

        //$createToken = $user->createToken(env('APP_NAME'))->accessToken;
        $userCred = ['email' => $providerUser->email, 'password' => $random_password];
        $token = auth('api')->attempt($userCred);
        \Log::Info('Token: ' . $token);
        $createToken = $this->createToken($token);
        \Log::Info( print_r($createToken, true) );
        
        return response()->json([
            'access_token' => $access_token,
            'token_type' => 'bearer',
            //'expires_in' => auth('api')->factory()->getTTL()*60,
            'user' => $user
        ]); */
        //return $createToken;

        //$token = $user->createToken(env('APP_NAME'))->accessToken;

        /* $refreshToken = $this->refreshToken();
        \Log::Info( print_r($refreshToken, true) );
        return $refreshToken; */

        auth('api')->login($user);

        return response()->json([
            'access_token' => $access_token,
            'token_type' => 'bearer',
            //'expires_in' => auth('api')->factory()->getTTL()*60,
            'user' => auth('api')->user()
        ]);
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
            return response()->json(['success' => false, 'message' => 'Incorrect OTP'], 400);
        }
        if($verify_otp->status == 'used'){
            return response()->json(['success' => false, 'message' => 'OTP was used!'], 400);
        }

        $current_time = Carbon::now();
    
        $otp_created_in_minutes = Carbon::createFromFormat('Y-m-d H:i:s', $verify_otp->created_at)->diffInMinutes($current_time);
    
        if( $otp_created_in_minutes > 5 ){
            return response()->json(['success' => false, 'message' => 'OTP Expired!'], 400);
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
        ], 200);
    }

    public function resendOtp(Request $request){
        try{
            $user_uuid = $request->user_uuid ?? null;
            $otp_type = $request->otp_type ?? 'email';

            $user = User::where('uuid', $user_uuid)->first();

            if( !isset($user->id) ){
                return response()->json(['success' => false, 'message' => 'User not found!'], 400);
            }
            if( in_array($otp_type, ['email', 'email_and_mobile']) && !is_null($user->email_verified_at) ){
                return response()->json(['success' => false, 'message' => 'User email id already verified!'], 400);
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
                'success' => true,
                'message' => 'OTP sent successfully!',
                'user_uuid' => $user_uuid
            ], 201);
        }
        catch(\Exception $e){
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function forgetPassword(Request $request){
        try{
            $email = $request->email ?? null;

            if( is_null($email) ){
                return response()->json(['success' => false, 'message' => 'Email not found!'], 400);
            }

            $user = User::where('email', $email)->first();

            if( !isset($user->id) ){
                return response()->json(['success' => false, 'message' => 'User not found!'], 400);
            }

            $token = Str::random(50);
            $url = config('app.url') . '/reset-password?token=' . $token;
            
            $mailDetails = [
                'name' => $user->name,
                'token' => $token
            ];

            Mail::to($user->email)->send(new ResetPassword($mailDetails));

            $current_time = Carbon::now()->format('Y-m-d H:i:s');

            PasswordReset::updateOrCreate(
                ['email' => $email],
                [
                    'email' => $email,
                    'token' => $token,
                    'created_at' => $current_time
                ]
            );

            return response()->json([
                'success' => true, 
                'message' => 'Please check your email to reset your password',
                'reset_password_url' => $url
            ], 200);
        }
        catch(\Exception $e){
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function resetPassword(Request $request){
        try{
            $token = $request->token ?? null;

            if( !is_null($token) ){
                return response()->json(['success' => false, 'message' => 'Token not found!'], 400);
            }

            $resetData = PasswordReset::where('token', $token)->first();

            if( !isset($resetData->email) ){
                return response()->json(['success' => false, 'message' => 'Invalid token!'], 400);
            }

            $user = User::where('email', $email)->first();

            return response()->json([
                'success' => true,
                'user' => $user->uuid
            ], 200);
        }
        catch(\Exception $e){
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function resetPasswordSubmit(Request $request){
        try{
            $request->validate([
                'password' => 'required|string|min:6|confirmed',
                'user_uuid' => 'required'
            ]);

            $user_uuid = $request->user_uuid ?? null;

            $user = User::where('uuid', $user_uuid)->first();

            $user->password = Hash::make($request->password);
            $user->save();

            PasswordReset::where('email', $user->email)->delete();

            $mailDetails = [
                'name' => $user->name,
            ];

            Mail::to($user->email)->send(new PasswordChangedSuccessful($mailDetails));

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully!',
                'user_uuid' => $user_uuid
            ], 200);
        }
        catch(\Exception $e){
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
