<?php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\Repository;

class TransactionRepository extends Repository {
    function model() {
        return "App\Models\Transaction";
    }
}