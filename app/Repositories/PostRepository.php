<?php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\Repository;

class PostRepository extends Repository {
    function model() {
        return "App\Models\Post";
    }
}