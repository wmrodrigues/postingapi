<?php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\Repository;

class NotificationRepository extends Repository {
    function model() {
        return "App\Models\Notification";
    }

    public function getByUserId($userid, $currentPage = 0, $pageSize = 10) {
        $now = new \DateTime();
        return $this->model->join("users", "notification.fromuserid", "=", "users.id")
                            ->where(array(
                                array("touserid", "=", $userid),
                                array("expiration", ">", $now)
                                // it should bring only non expired notifications
                            ))
                            ->select("notification.id"
                                    , "notification.fromuserid"
                                    , "notification.created_at"
                                    , "notification.description"
                                    , "notification.expiration"
                                    , "users.login")
                            ->paginate($pageSize, array("*"), "page", $currentPage);
    }
}