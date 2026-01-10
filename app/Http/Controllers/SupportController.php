<?php

namespace App\Http\Controllers;

use App\Support;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupportController extends Controller
{
    public function index(Request $request){
        $item = Support::with(['user'])->when($request->has('user_id'),function($query)use($request){
            $users = $request->user_id;
            $users=is_array($users)?$users:[$users];
            $query->whereIn('user_id',$users)
            ;
        });
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
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string'
        ]);
        if ($validator->fails()) return response()->json($validator->errors(),500,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
        if(!isset($data['data']))$data["data"]=[];
        $item = Support::create($data);
        return response()->json($item,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Support  $support
     * @return \Illuminate\Http\Response
     */
    public function show(Support $support)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Support  $support
     * @return \Illuminate\Http\Response
     */
    public function edit(Support $support)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Support  $support
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Support $support)
    {
        $data=$request->all();
        $support->update($data);
        return response()->json($support,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Support  $support
     * @return \Illuminate\Http\Response
     */
    public function destroy(Support $support)
    {
        $support->delete();
        return response()->json($support,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }
}
