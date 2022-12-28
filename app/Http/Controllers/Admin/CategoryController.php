<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Categories;

class CategoryController extends Controller
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
        $categories_qry = Categories::select('*');

        if( $request->filled('cat_title') ){
            $categories_qry->where('name', 'like', '%' . $request->cat_title . '%');
        }

        if( $request->filled('cat_status') ){
            $categories_qry->where('status', $request->cat_status);
        }

        $categories = $categories_qry->orderby('id','desc')->paginate(15);

        return view('admin.category.index', compact('categories'))->with([ 'statusArray' => $this->statusArray ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.category.create');
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
            $category_title = $request->category_title ?? [];

            //+++++++++++++++++++++++++ VALIDATION :: Start +++++++++++++++++++++++++//
            if( empty($category_title) ){
                return response()->json(['status' => 'failed', 'error' => ['message' => 'No category title found!']]);
            }

            if( !empty($category_title) ){
                $cat_val_exists = 0;

                foreach($category_title as $cat){
                    if( trim($cat) != '' ){
                        $cat_val_exists++;
                    }
                }

                if( $cat_val_exists == 0 ){
                    return response()->json(['status' => 'failed', 'error' => ['message' => 'No category title found!']]);
                }
            }
            //+++++++++++++++++++++++++ VALIDATION :: End +++++++++++++++++++++++++//

            if( count($category_title) > 0 ){
                foreach($category_title as $cat){
                    if( trim($cat) != '' ){
                        Categories::create([
                            'name' => $cat,
                            'slug' => Categories::generateSlug($cat),
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
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function edit($uuid)
    {
        $category = Categories::where('uuid', $uuid)->first();

        return view('admin.category.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $uuid)
    {
        $response = [];

        $response['status'] = '';

        try {
            $category_uuid = $uuid ?? '';

            if( $category_uuid == '' ){
                return response()->json(['status' => 'failed', 'error' => ['message' => 'No category found!']]);
            }

            $category_name = $request->category_name ? trim($request->category_name) : '';
            $category_status = $request->category_status ?? null;

            if( empty($category_name) ){
                return response()->json(['status' => 'failed', 'error' => ['message' => 'No category title found!']]);
            }

            if( is_null($category_status) ){
                return response()->json(['status' => 'failed', 'error' => ['message' => 'Invalid category status']]);
            }

            $category = Categories::where('uuid', $category_uuid)->first();

            $category->name = $category_name;
            $category->slug = Categories::generateSlug($category_name);
            $category->status = $category_status;

            $category->save();            

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
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy($uuid)
    {
        $response = [];

        $response['status'] = '';

        try {
            if( !isset($uuid) || ($uuid == '') ){
                return response()->json(['status' => 'failed', 'error' => ['message' => 'No category found!']]);
            }

            $category = Categories::where('uuid', $uuid)->first();
            $category->delete();            

            $response['status'] = 'success';
        } catch (\Exception $e) {
            report($e);
            return response()->json(['status' => 'failed', 'error' => ['message' => $e->getMessage()], 'e' => $e]);
        }

        return response()->json($response);
    }

    public function changeCategoryStatus(Request $request){
        $response = [];

        $response['status'] = '';

        try {
            $uuid = $request->uuid ?? '';

            if( $uuid == '' ){
                return response()->json(['status' => 'failed', 'error' => ['message' => 'No category found!']]);
            }

            $category = Categories::where('uuid', $uuid)->first();

            $category->status = ($category->status == '1') ? '0' : '1';
            $category->save();            

            $response['status'] = 'success';
        } catch (\Exception $e) {
            report($e);
            return response()->json(['status' => 'failed', 'error' => ['message' => $e->getMessage()], 'e' => $e]);
        }

        return response()->json($response);
    }
}
