<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Events\TodolistCreated;
use App\Events\TodolistRemoved;
use App\Todolist;

class TodolistController extends Controller
{
    public function fetchAll() {
        $todolists = Todolist::all();
        // return response()->json($todolists)
        return $todolists;
    }

    public function store(Request $request) {
        $todolist = Todolist::create($request->all());
        \broadcast(new TodolistCreated($todolist));
        return response()->json("added");
    }

    public function delete($id) {
        $todolist = Todolist::find($id);
        \broadcast(new TodolistRemoved($todolist));
        Todolist::destroy($id);
        return \response()->json("deleted");
    }
}
