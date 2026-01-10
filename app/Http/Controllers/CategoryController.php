<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(Request $request){
        $item = Category::when($request->has('user_id'),function($query)use($request){
           $users = $request->user_id;
           $users=is_array($users)?$users:[$users];
           $query->whereIn('user_id',$users)
           ;
        });
        if(in_array($request->input('deactivated',false),["on",1,"checked"]) ){
           $item->onlyTrashed();
        }
        if( $request->has('search') ){
            $search = '%'.$request->search.'%';
            $item->where( function($query)use($search){
                $query->orWhere('name','like',$search);
                $query->orWhere('description','like',$search);
                $query->orWhere('metadata','like',$search);
            });
        }
        $items = $item->get();
        return response()->json($items,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
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
        $data=$request->all();
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            // 'description' => 'string|max:2048',
            // 'nds' => 'required',
            // 'quantity' => 'numeric',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(),500,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
        $item = Category::create($data);
        return response()->json($item,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $category)
    {
        $data=$request->all();
        $category = Category::withTrashed()->find($category);
        if($request->has('restore'))$category->restore();
        else $category->update($data);
        return response()->json($category,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json($category,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }
}
