<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TodoItem extends Model
{
    //
    protected $fillable = ['todo_text', 'completed']; 
}
