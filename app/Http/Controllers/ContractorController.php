<?php

namespace App\Http\Controllers;

use Log;
use App\User;
use App\Contractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContractorController extends Controller
{
    public function index(Request $request){
        $item = Contractor::with(['user'])->when($request->has('user_id'),function($query)use($request){
            $users = $request->user_id;
            $users=is_array($users)?$users:[$users];
            $query->whereIn('user_id',$users)
            ;
        });
        if(in_array($request->user()->role,['user','manager'])){
            $users = [$request->user()->id];
            $users = array_merge( $users, User::where('manager_id',$request->user()->id)->pluck('id')->toArray() );
            $item->whereIn('user_id',$users);
        }
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
            'name' => 'required',
            'inn' => 'required',
            'kpp' => 'required',
            'address' => 'required',
            'user_id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) return response()->json($validator->errors(),500,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
        $item = Contractor::create($data);
        $item->load(['user']);
        return response()->json($item,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Contractor  $contractor
     * @return \Illuminate\Http\Response
     */
    public function show(Contractor $contractor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Contractor  $contractor
     * @return \Illuminate\Http\Response
     */
    public function edit(Contractor $contractor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Contractor  $contractor
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $contractor)
    {
        $data=$request->all();
        $contractor = Contractor::withTrashed()->find($contractor);
        if($request->has('restore'))$contractor->restore();
        else $contractor->update($data);
        $item->load(['user']);
        return response()->json($contractor,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Contractor  $contractor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contractor $contractor){
        $item = $contractor;
        $item->load(['user']);
        $contractor->delete();
        return response()->json($item,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }
}
