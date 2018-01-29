<?php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\Repository;

class UserRepository extends Repository {
    function model() {
        return "App\Models\Users";
    }

    public function updateBalance($userid, $coins) {
        $this->model->where("id", $userid)
                        ->update(["balance" => $coins]);
    }

    public function getCurrentBalance($userid) {
        return $this->model->where("id", $userid)
                            ->select("id", "balance")
                            ->first();
    }

    public function getByLoginPassword($login, $password) {
        return $this->model->where(array(
                                    array("login", "=", $login),
                                    array("password", "=", $password)
                                ))
                                ->get();
    }
}