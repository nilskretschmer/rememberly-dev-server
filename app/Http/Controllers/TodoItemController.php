<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TodoItemController extends Controller
{
    public function fetchAll() {
        $todoItems = TodoItem::all();
        // return response()->json($todolists)
        return $todoItems;
    }

    public function store(Request $request) {
        $todoItem = TodoItem::create($request->all());
        //\broadcast(new TodolistCreated($todolist));
        return response()->json("added");
    }

    public function delete($id) {
        $todoItem = TodoItem::find($id);
        //\broadcast(new TodolistRemoved($todolist));
        TodoItem::destroy($id);
        return \response()->json("deleted");
    }
}
