<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Categories;
use App\Models\Medias;

use Illuminate\Support\Str;
use Validator;
use Image;
use Illuminate\Support\Facades\File; 

use Auth;

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
        $categories_qry = Categories::with('icon_image');

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
            $validator_array = [];

            $validator_array['category_name'] = 'required|max:255';
            $validator_array['category_icon'] = 'required|mimes:jpeg,jpg,png,gif|max:10000';

            $validator = Validator::make($request->all(), $validator_array);

            $validator_errors = implode('<br>', $validator->errors()->all());

            if ($validator->fails()) {
                return response()->json(['status' => 'failed', 'error' => ['message' => $validator_errors]]);
            }

            $category_name = $request->category_name;

            $category = Categories::create([
                            'name' => $category_name,
                            'slug' => Categories::generateSlug($category_name),
                            'type' => 'blog'
                        ]);
            
            if($request->hasFile('category_icon')) {
                $category_icon = $request->file('category_icon');
                $iconMake = Image::make($category_icon);

                $iconName = time() . '-' . uniqid() . '.' . $category_icon->getClientOriginalExtension();

                $iconDir = 'images/icon-files/';

                $destinationPath = public_path($iconDir);
                $iconMake->save($destinationPath . $iconName);

                Medias::create([
                    'user_id' => Auth::user()->id,
                    'file_type' => 'image',
                    'source_type' => 'category',
                    'source_uuid' => $category->uuid,
                    'name' => $iconName,
                    'is_active' => 1
                ]);
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
        $category = Categories::with('icon_image')
                                ->where('uuid', $uuid)->first();

        return view('admin.category.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function updateCategory(Request $request, $uuid)
    {
        $response = [];

        $response['status'] = '';

        try {
            $category_uuid = $uuid ?? '';

            if( $category_uuid == '' ){
                return response()->json(['status' => 'failed', 'error' => ['message' => 'No category found!']]);
            }

            $validator_array = [];

            $validator_array['category_name'] = 'required|max:255';
            $validator_array['category_icon'] = 'mimes:jpeg,jpg,png,gif|max:10000';
            $validator_array['category_status'] = 'required';

            $validator = Validator::make($request->all(), $validator_array);

            $validator_errors = implode('<br>', $validator->errors()->all());

            if ($validator->fails()) {
                return response()->json(['status' => 'failed', 'error' => ['message' => $validator_errors]]);
            }

            $category_name = $request->category_name;
            $category_status = $request->category_status;
            
            $category = Categories::where('uuid', $category_uuid)->first();

            if($request->hasFile('category_icon')) {
                $category_icon = $request->file('category_icon');
                $iconMake = Image::make($category_icon);

                $iconName = time() . '-' . uniqid() . '.' . $category_icon->getClientOriginalExtension();

                $iconDir = 'images/icon-files/';

                $destinationPath = public_path($iconDir);
                $iconMake->save($destinationPath . $iconName);

                //Delete image if exists
                $this->deleteCategoryIcon($category->uuid);

                Medias::create([
                    'user_id' => Auth::user()->id,
                    'file_type' => 'image',
                    'source_type' => 'category',
                    'source_uuid' => $category->uuid,
                    'name' => $iconName,
                    'is_active' => 1
                ]);

                $response['icon_name'] = $iconName;
            }

            $category->name = $category_name;
            //$category->slug = Categories::generateSlug($category_name);
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

    private function deleteCategoryIcon($category_uuid){
        $media = Medias::where('source_type', 'category')
                        ->where('source_uuid', $category_uuid)
                        ->first();

        $icon_name = $media->name ?? null;

        if( isset($media->uuid) ){
            $media->delete();

            $image_dir = 'images/';
            $dir_icons = config('filesystems.image_folder.icon-files') . '/';
            $file_path_icons = public_path($image_dir . $dir_icons . $icon_name);

            if( File::exists($file_path_icons) ){
                File::delete($file_path_icons);
            }
        }
    }
}
