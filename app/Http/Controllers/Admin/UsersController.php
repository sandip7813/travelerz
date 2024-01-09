<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;

use Carbon\Carbon;

class UsersController extends Controller
{
    protected $statusArray = [
        0 => 'New',
        1 => 'Active',
        2 => 'Inactive',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users_qry = User::with(['profile_picture'])
                            ->where('role', '0');

        if( $request->filled('uname') ){
            $users_qry->where('name', 'like', '%' . $request->uname . '%');
        }

        if( $request->filled('uemail') ){
            $users_qry->where('email', 'like', '%' . $request->uemail . '%');
        }

        if( $request->filled('uphone') ){
            $users_qry->where('phone', 'like', '%' . $request->uphone . '%');
        }

        if( $request->filled('ustatus') ){
            $users_qry->where('status', $request->ustatus);
        }

        $users = $users_qry->orderby('id','desc')->paginate(15);

        return view('admin.users.index', compact('users'))->with([ 'statusArray' => $this->statusArray ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($uuid)
    {
        $user = User::with(['profile_picture', 'banner_picture', 'followings', 'followers', 'user_country', 'user_state', 'friends'])
                    ->where('uuid', $uuid)->first();

        return view('admin.users.show', compact('user'))->with([ 'statusArray' => $this->statusArray ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($uuid)
    {
        $user = User::where('uuid', $uuid)->first();

        return view('admin.users.edit', compact('user'));
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
            if( !$request->filled('user_name') ){
                return response()->json(['status' => 'failed', 'error' => ['message' => 'User name is missing'], 422]);
            }

            $country = $request->country ?? null;
            $state = $request->state ?? null;
            $city = $request->city ?? null;

            $status = $request->user_status ?? null;

            $user_data = User::where('uuid', $uuid)->first();

            $user_data->name = $request->user_name;
            $user_data->date_of_birth = ( $request->date_of_birth && !empty($request->date_of_birth) ) ? Carbon::createFromFormat('d/m/Y', $request->date_of_birth)->format('Y-m-d') : NULL;
            $user_data->gender = $request->gender ?? null;
            $user_data->about_me = $request->about_user ?? null;
            $user_data->country_id = $country;
            $user_data->state_id = $state;

            if( !is_null($country) && !is_null($state) && !is_null($city) ){
                $user_data->city = $request->city ?? null;
            }

            if( !is_null($status) ){
                $user_data->status = $status;
            }

            $user_data->save();

            $response['status'] = 'success';

            return response()->json($response);
        } catch (\Exception $e) {
            report($e);
            return response()->json(['status' => 'failed', 'error' => ['message' => $e->getMessage()], 'e' => $e]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($uuid)
    {
        $response = [];

        $response['status'] = '';

        try {
            if( !isset($uuid) || ($uuid == '') ){
                return response()->json(['status' => 'failed', 'error' => ['message' => 'No user found!']]);
            }

            $user = User::where('uuid', $uuid)->first();
            $user->delete();

            $response['status'] = 'success';
        } catch (\Exception $e) {
            report($e);
            return response()->json(['status' => 'failed', 'error' => ['message' => $e->getMessage()], 'e' => $e]);
        }

        return response()->json($response);
    }

    public function userPosts(Request $request, $uuid){
        $items_per_page = config('common.items_per_page');
        $start = $request->start ?? 0;

        $user = User::with('profile_picture')->where('uuid', $uuid)->first();

        if($start > 0){
            $posts = $user->posts_by_user()->orderBy('updated_at', 'DESC')->offset($start)->limit($items_per_page)->get();
        }
        else{
            $posts = $user->posts_by_user()->orderBy('updated_at', 'DESC')->paginate($items_per_page);
        }

        $html_view = view('admin.users._user-posts-tab', compact('user', 'posts'))->render();

        $response['status'] = 'success';
        $response['next'] = $start + $items_per_page;
        $response['records_count'] = $posts->count();
        $response['html_view'] = $html_view;

        return response()->json($response);
    }


}
