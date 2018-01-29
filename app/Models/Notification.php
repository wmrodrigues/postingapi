<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model {
    protected $table = "notification";
    protected $fillable = array("description", "fromuserid", "touserid", "read");
}