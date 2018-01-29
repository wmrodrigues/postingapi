<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model {
    protected $table = "transaction";
    protected $fillable = array("userid", "postid", "commentid", "coins", "parentid");
}