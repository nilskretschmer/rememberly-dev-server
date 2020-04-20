<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Events\TodolistCreated;
use App\Events\TodolistRemoved;
use App\Todolist;
use Illuminate\Support\Facades\Log;

class TodolistController extends Controller
{
        /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    public function fetchAll() {
        $todolists = Todolist::all();
        // return response()->json($todolists)
        return $todolists;
    }

    public function store(Request $request) {
        Log::alert("User with ID: " . $request->user()->id);
        $todolist = new Todolist;
        $todolist->owner = $request->user()->id;
        $todolist->title = $request->input('title');
        $todolist->save();
        broadcast(new TodolistCreated($todolist));
        return response()->json($todolist);
    }

    public function delete($id) {
        $todolist = Todolist::find($id);
        broadcast(new TodolistRemoved($todolist));
        Todolist::destroy($id);
        return \response()->json("deleted");
    }
}
