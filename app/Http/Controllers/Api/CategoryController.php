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
}
