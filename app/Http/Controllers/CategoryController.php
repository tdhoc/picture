<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Validator;

use Illuminate\Support\MessageBag;

use Illuminate\Support\Facades\Auth;

use App\Http\Model\Category;

use App\Http\Model\Subcategory;;

use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
    * Get category page
    *
    * @return category view
    */
    public function getCategory(Request $request){
        $category = Category::all();
        $subcategory = Subcategory::all();
        return view('admin.category', array('categories' => $category, 'subcategories' => $subcategory));
    }

    /**
    * Add category
    *
    * @param $request 
    *
    * @return json 
    */
    public function postAddCategory(Request $request){

        $rules = [
          'name' => 'required|unique:Category',
        ];
  
        $message = [
          'name.unique' => 'Category already exists',
          'name.required' => 'Category is required',
        ];
  
        $validator = Validator::make($request->all(), $rules, $message);
  
        if ($validator->fails()) {
            return response() -> json([
                'error' => true,
                'message' => $validator->errors()
            ], 200);
        } else {
            $category = new Category;
            $category->name = $request->name;
            $category->save();

            return response() -> json([
                'error' => false,
                'message' => 'success'
            ], 200);
        }
    }

    /**
    * Edit category
    *
    * @param $request 
    *
    * @return json 
    */
    public function postEditCategory(Request $request){

        $rules = [
          'editid' => 'required',
          'editname' => 'required',
        ];
  
        $message = [
          'editid.required' => 'Category ID is required',
          'editname.required' => 'Category is required',
        ];
  
        $validator = Validator::make($request->all(), $rules, $message);
  
        if ($validator->fails()) {
            return response() -> json([
                'error' => true,
                'message' => $validator->errors()
            ], 200);
        } else {
            $category = Category::where('id', $request->editid)->first();
            $category->id = $request->editid;
            $category->name = $request->editname;
            $category->save();

            return response() -> json([
                'error' => false,
                'message' => 'success'
            ], 200);
        }
    }

    /**
    * Delete Category
    *
    * @param $request
    *
    * @return json
    */
    public function postDeleteCategory(Request $request){
        $category = Category::where('id', $request->deleteid)->first();
        $category->delete();
        return response() -> json([
                    'error' => false,
              ], 200);
    }

    /**
    * Add subcategory
    *
    * @param $request 
    *
    * @return json 
    */
    public function postAddSubcategory(Request $request){

        $rules = [
          'name' => 'required|unique:Subcategory',
          'category_id' => 'required',
        ];
  
        $message = [
          'name.unique' => 'Sub category already exists',
          'name.required' => 'Sub category is required',
          'category_id.required' => 'Category is required',
        ];
  
        $validator = Validator::make($request->all(), $rules, $message);
  
        if ($validator->fails()) {
            return response() -> json([
                'error' => true,
                'message' => $validator->errors()
            ], 200);
        } else {
            $subcategory = new Subcategory;
            $subcategory->name = $request->name;
            $subcategory->category_id = $request->category_id;
            $subcategory->save();

            return response() -> json([
                'error' => false,
                'message' => 'success'
            ], 200);
        }
    }

        /**
    * Edit subcategory
    *
    * @param $request 
    *
    * @return json 
    */
    public function postEditSubcategory(Request $request){

        $rules = [
          'editid' => 'required',
          'editname' => 'required',
          'edittype' => 'required',
        ];
  
        $message = [
          'editid.required' => 'Sub Category ID is required',
          'editname.required' => 'Sub Category is required',
          'edittype.required' => 'Category type is required',
        ];
  
        $validator = Validator::make($request->all(), $rules, $message);
  
        if ($validator->fails()) {
            return response() -> json([
                'error' => true,
                'message' => $validator->errors()
            ], 200);
        } else {
            $subcategory = Subcategory::where('id', $request->editid)->first();
            $subcategory->id = $request->editid;
            $subcategory->name = $request->editname;
            $subcategory->category_id = $request->edittype;
            $subcategory->save();

            return response() -> json([
                'error' => false,
                'message' => 'success'
            ], 200);
        }
    }

    /**
    * Delete Subcategory
    *
    * @param $request
    *
    * @return json
    */
    public function postDeleteSubcategory(Request $request){
        $subcategory = Subcategory::where('id', $request->deleteid)->first();
        $subcategory->delete();
        return response() -> json([
                    'error' => false,
              ], 200);
    }
}