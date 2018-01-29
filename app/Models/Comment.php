<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model {
    protected $table = "comment";
    protected $fillable = array("description", "userid", "postid");
}