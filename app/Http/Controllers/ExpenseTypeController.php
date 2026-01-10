<?php

namespace App\Http\Controllers;

use Log;
use App\ExpenseType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpenseTypeController extends Controller
{
    public function index(Request $request){
        $item = ExpenseType::when($request->has('user_id'),function($query)use($request){
            $users = $request->user_id;
            $users=is_array($users)?$users:[$users];
            $query->whereIn('user_id',$users)
            ;
        });
        if(in_array($request->input('deactivated',false),["on",1,"checked"]) ){
            $item->onlyTrashed();
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
            'name' => 'required'
        ]);
        if ($validator->fails()) return response()->json($validator->errors(),500,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
        if(!isset($data['data']))$data["data"]=[];
        $item = ExpenseType::create($data);
        return response()->json($item,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ExpenseType  $expenseType
     * @return \Illuminate\Http\Response
     */
    public function show(ExpenseType $expenseType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ExpenseType  $expenseType
     * @return \Illuminate\Http\Response
     */
    public function edit(ExpenseType $expenseType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ExpenseType  $expenseType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $expenseType)
    {
        $data=$request->all();
        $expenseType = ExpenseType::withTrashed()->find($expenseType);
        if($request->has('restore'))$expenseType->restore();
        else $expenseType->update($data);
        return response()->json($expenseType,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ExpenseType  $expenseType
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, ExpenseType $expenseType){
        Log::debug('ExpenseType@destroy '.json_encode($expenseType));
        $response = $expenseType->toArray();
        $expenseType->delete();
        return response()->json($response,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }
}
