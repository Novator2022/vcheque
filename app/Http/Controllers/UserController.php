<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $search = '%'.$request->input('search','').'%';
        $item = User::with(['contractors'])->where(function($query)use($search){
            $query->where('name','like',$search)
                ->orWhere('email','like',$search)
                ->orWhere('phone','like',$search);
        });
        $item->when($request->has('role') && mb_strlen($request->role),function($query)use($request){
            $query->where('role',$request->role);
        });
        return response()->json($item->get(),200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
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
            'role' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:6'
        ]);
        if ($validator->fails()) return response()->json($validator->errors(),500,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
        $data['password'] = bcrypt($data['password']);
        $item = User::create($data);
        $item->load(['contractors']);
        event( new \App\Events\LogEvent($item, $request->user(), $action="create", $type="user"));
        return response()->json($item,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $data=$request->all();
        $validator = Validator::make($data, [
            'email' => 'required|string|email|max:255',
            'phone' => 'string|max:20'
        ]);
        if ($validator->fails()) return response()->json($validator->errors(),500,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
        if(isset($data['password'])){
            if(mb_strlen($data['password']))$data['password'] = bcrypt($data['password']);
            else unset($data['password']);
        }
        $user->update($data);
        $user->load(['contractors']);
        event( new \App\Events\LogEvent($user, $request->user(), $action="update", $type="user"));
        return response()->json($user,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->load(['contractors']);
        $user->delete();
        event( new \App\Events\LogEvent($user, $request->user(), $action="delete", $type="user"));
        return response()->json($user,200,['Content-Type' => 'application/json; charset=utf-8'],JSON_UNESCAPED_UNICODE);
    }
}
