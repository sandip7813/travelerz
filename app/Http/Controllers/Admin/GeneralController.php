<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public function generateStateListDropdown(Request $request)
    {
        $response = [];

        $response['status'] = null;

        try {
            $country_id = $request->country ?? NULL;
            $state_id = $request->state ?? NULL;

            $states_list = country_states($country_id);
            $total_states = 0;

            $dropdown_html = '<option value=""> - Select State / Province - </option>';

            if (!is_null($states_list) ){
                $total_states = $states_list->count();

                if($total_states > 0) {
                    foreach ($states_list as $state) {
                        $state_selected = ( $state_id == $state->id ) ? ' selected' : '';
                        $dropdown_html .= '<option value="' . $state->id . '" ' . $state_selected . '>' . $state->name . '</option>';
                    }
                }
            }

            $response['status'] = 'success';
            $response['dropdown_html'] = $dropdown_html;
            $response['total_states'] = $total_states;
            
        } catch (Exception $e) {
            Log::error($e);

            return response()->json(['status' => 'failed', 'error' => ['message' => $e->getMessage()], 'e' => $e]);
        }

        //$response['csrf_token'] = csrf_token();

        return response()->json($response);
    }
}
