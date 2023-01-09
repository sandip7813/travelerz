<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Interest;

class InterestController extends Controller
{
    protected $statusArray = [
        1 => 'Active',
        0 => 'Inactive',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $interests_qry = Interest::select('*');

        if( $request->filled('int_title') ){
            $interests_qry->where('name', 'like', '%' . $request->int_title . '%');
        }

        if( $request->filled('int_status') ){
            $interests_qry->where('status', $request->int_status);
        }

        $interests = $interests_qry->orderby('id','desc')->paginate(15);

        return view('admin.interest.index', compact('interests'))->with([ 'statusArray' => $this->statusArray ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.interest.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $response = [];

        $response['status'] = '';

        try {
            $interest_title = $request->interest_title ?? [];

            //+++++++++++++++++++++++++ VALIDATION :: Start +++++++++++++++++++++++++//
            if( empty($interest_title) ){
                return response()->json(['status' => 'failed', 'error' => ['message' => 'No interest title found!']]);
            }

            if( !empty($interest_title) ){
                $cat_val_exists = 0;

                foreach($interest_title as $cat){
                    if( trim($cat) != '' ){
                        $cat_val_exists++;
                    }
                }

                if( $cat_val_exists == 0 ){
                    return response()->json(['status' => 'failed', 'error' => ['message' => 'No interest title found!']]);
                }
            }
            //+++++++++++++++++++++++++ VALIDATION :: End +++++++++++++++++++++++++//

            if( count($interest_title) > 0 ){
                foreach($interest_title as $cat){
                    if( trim($cat) != '' ){
                        Interest::create([
                            'name' => $cat,
                            'slug' => Interest::generateSlug($cat),
                            'type' => 'blog'
                        ]);
                    }
                }
            }

            $response['status'] = 'success';
        } catch (\Exception $e) {
            report($e);
            return response()->json(['status' => 'failed', 'error' => ['message' => $e->getMessage()], 'e' => $e]);
        }

        return response()->json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /* public function show($id)
    {
        //
    } */

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $uuid
     * @return \Illuminate\Http\Response
     */
    public function edit($uuid)
    {
        $interest = Interest::where('uuid', $uuid)->first();

        return view('admin.interest.edit', compact('interest'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $uuid)
    {
        $response = [];

        $response['status'] = '';

        try {
            $interest_uuid = $uuid ?? '';

            if( $interest_uuid == '' ){
                return response()->json(['status' => 'failed', 'error' => ['message' => 'No interest found!']]);
            }

            $interest_name = $request->interest_name ? trim($request->interest_name) : '';
            $interest_status = $request->interest_status ?? null;

            if( empty($interest_name) ){
                return response()->json(['status' => 'failed', 'error' => ['message' => 'No interest title found!']]);
            }

            if( is_null($interest_status) ){
                return response()->json(['status' => 'failed', 'error' => ['message' => 'Invalid interest status']]);
            }

            $interest = Interest::where('uuid', $interest_uuid)->first();

            $interest->name = $interest_name;
            $interest->slug = Interest::generateSlug($interest_name);
            $interest->status = $interest_status;

            $interest->save();            

            $response['status'] = 'success';
        } catch (\Exception $e) {
            report($e);
            return response()->json(['status' => 'failed', 'error' => ['message' => $e->getMessage()], 'e' => $e]);
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy($uuid)
    {
        $response = [];

        $response['status'] = '';

        try {
            if( !isset($uuid) || ($uuid == '') ){
                return response()->json(['status' => 'failed', 'error' => ['message' => 'No interest found!']]);
            }

            $interest = Interest::where('uuid', $uuid)->first();
            $interest->delete();            

            $response['status'] = 'success';
        } catch (\Exception $e) {
            report($e);
            return response()->json(['status' => 'failed', 'error' => ['message' => $e->getMessage()], 'e' => $e]);
        }

        return response()->json($response);
    }

    public function changeInterestStatus(Request $request){
        $response = [];

        $response['status'] = '';

        try {
            $uuid = $request->uuid ?? '';

            if( $uuid == '' ){
                return response()->json(['status' => 'failed', 'error' => ['message' => 'No interest found!']]);
            }

            $interest = Interest::where('uuid', $uuid)->first();

            $interest->status = ($interest->status == '1') ? '0' : '1';
            $interest->save();            

            $response['status'] = 'success';
        } catch (\Exception $e) {
            report($e);
            return response()->json(['status' => 'failed', 'error' => ['message' => $e->getMessage()], 'e' => $e]);
        }

        return response()->json($response);
    }
}
