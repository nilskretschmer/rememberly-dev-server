<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Todolist extends Model
{
    protected $fillable = ['title'];

    public function todoItems() {
        return $this->hasMany('App\TodoItem');
    }
}
