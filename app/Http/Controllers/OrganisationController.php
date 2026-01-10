<?php

namespace App\Http\Controllers;

use Log;
use App\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrganisationController extends Controller
{
    public function index(Request $request){
        $search = '%'.$request->input('search','').'%';
        $item = Organisation::with(['expense_type'=>function($query){$query->withTrashed();}])->where(function($query)use($search){
            $query->where('name','like',$search);
        });
        if(in_array($request->input('deactivated',false),["on",1,"checked"])){
            $item->onlyTrashed();
        }
        $items = $item->get();

        $result = array();
        foreach ($items as $item) {
            $org = $item->toArray();
            $org['reach'] = $item->reach();
            $result[] = $org;
        }

        return response()->json($result,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
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
            'name' => 'required|string|max:255'
        ]);
        if ($validator->fails()) return response()->json($validator->errors(),500,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
        if(!isset($data['data']))$data["data"]=[];
        $item = Organisation::create($data);
        $item->load(['expense_type']);
        return response()->json($item,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Organisation  $organisation
     * @return \Illuminate\Http\Response
     */
    public function show(Organisation $organisation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Organisation  $organisation
     * @return \Illuminate\Http\Response
     */
    public function edit(Organisation $organisation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Organisation  $organisation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$organisation)
    {
        $data=$request->all();
        $organisation = Organisation::withTrashed()->find($organisation);
        if($request->has('restore'))$organisation->restore();
        else $organisation->update($data);
        $organisation->load(['expense_type']);
        return response()->json($organisation,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Organisation  $organisation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Organisation $organisation)
    {
        $organisation->delete();
        return response()->json($organisation,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }
}
