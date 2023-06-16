<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Categories;

class CategoryController extends Controller
{
    public function getAllActiveCategories(){
        $categories = Categories::where('status', '1')->get()->map->only('id', 'uuid', 'name', 'slug', 'created_at');

        return response()->json([
            'success' => true,
            'categories' => $categories
        ], 200);
    }

    public function getCategory(Request $request){
        $id = $request->id ?? null;
        $uuid = $request->uuid ?? null;

        if( is_null($id) && is_null($uuid) ){
            return response()->json(['success' => false, 'message' => 'Invalid request!'], 400);
        }

        $categoryQry = Categories::select('id', 'uuid', 'name', 'slug', 'created_at')->where('status', '1');

        if( !is_null($id) ){
            $categoryQry->whereId($id);
        }

        if( !is_null($uuid) ){
            $categoryQry->where('uuid', $uuid);
        }

        return $categoryQry->first()->toArray();
    }
}
