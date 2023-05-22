<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Membership;
use App\Models\Transaction;

use Stripe\StripeClient;
use Carbon\Carbon;

class MembershipController extends Controller
{
    private $stripe, $user;

    public function __construct(){
        $this->middleware('auth:api');
        $this->user = auth('api')->user();

        $this->stripe = new StripeClient(config('stripe.api_keys.secret_key'));
    }

    /* public function allMemberships(){
        return Membership::all();
    } */

    public function generateStripeCustomer(){
        $user_data = $this->user;
        $stripe_customer_id = $user_data->stripe_customer_id;

        if( is_null($stripe_customer_id) ){
            $customer_obj = $this->stripe->customers->create([
                'name' => $user_data->name,
                'email' => $user_data->email,
                'phone' => $user_data->phone,
            ]);

            $stripe_customer_id = $customer_obj->id;

            $user_data->stripe_customer_id = $stripe_customer_id;
            $user_data->save();
        }

        return response()->json([
            'success' => true,
            'stripe_customer_id' => $stripe_customer_id
        ], 200);
    }

    public function savePaymentDetails(Request $request){
        try{
            $payment_intent_id = $request->payment_intent_id ?? null;
            $membership_uuid = $request->membership_uuid ?? null;

            if( is_null($payment_intent_id) || is_null($membership_uuid) ){
                return response()->json(['success' => false, 'message' => 'Invalid request!'], 400);
            }

            $membership = Membership::where('uuid', $membership_uuid)
                                    ->where('status', '1')->first();

            if( !isset($membership->id) ){
                return response()->json(['success' => false, 'message' => 'Membership not found!'], 400);
            }

            $membership_name = $membership->name ?? null;
            $membership_amount = $membership->amount ?? null;
            $membership_duration = $membership->duration ?? null;
            
            $payment_intent_details = $this->stripe->paymentIntents->retrieve($payment_intent_id);
            $payment_status = $payment_intent_details->status ?? null;

            $transaction = Transaction::create([
                'payment_intent_id' => $payment_intent_details->id ?? null,
                'charge_id' => $payment_intent_details->latest_charge ?? null,
                'payment_status' => $payment_status,
                'amount' => isset($payment_intent_details->amount) ? ($payment_intent_details->amount / 100) : null,
                'membership_uuid' => $membership_uuid,
                'membership_details' => $membership->toArray(),
            ]);

            if( $payment_status == 'succeeded' ){
                $user_data = $this->user;

                $membership_expiry = is_null($membership_duration) ? Carbon::now()->addDays($membership_duration) : now()->parse($user_data->membership_expiry)->addDays($membership_duration);

                $currentDateTime = Carbon::now();
                $expiry_date = $user_data->membership_expiry;
                $checkDateTime = $currentDateTime->gt($expiry_date);
                
                if( is_null($membership_duration) || $checkDateTime ){
                    $membership_expiry = Carbon::now()->addDays($membership_duration);
                }
                else{
                    $membership_expiry = now()->parse($expiry_date)->addDays($membership_duration);
                }

                $user_data->membership = $membership_name;
                $user_data->membership_expiry = $membership_expiry;
                $user_data->membership_uuid = $membership_uuid;
                $user_data->transaction_id = $transaction->id;

                $user_data->save();
            }

            return response()->json([
                'success' => true,
                'membership_expiry' => $membership_expiry
            ], 200);
        }
        catch(\Exception $e){
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function getPaymentDetails(){
        try{
            $payment_intent_id = 'pi_3N94vQSEXvi8Sffx1R73oR1B';
            $payment_intent_details = $this->stripe->paymentIntents->retrieve($payment_intent_id);

            print_r($payment_intent_details);
        }
        catch(\Exception $e){
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
