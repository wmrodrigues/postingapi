<?php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\Repository;

class CommentRepository extends Repository {
    function model() {
        return "App\Models\Comment";
    }

    public function getByPostId($postid) {
        return $this->model->join("users", "comment.userid", "=", "users.id")
                            ->where("comment.postid", "=", $postid)
                            ->orderBy("comment.highlightexpiration", "desc")
                            ->orderBy("comment.created_at", "desc")
                            ->select("comment.userid"
                                        , "comment.id"
                                        , "users.login"
                                        , "users.subscriber"
                                        , "comment.highlight"
                                        , "comment.created_at"
                                        , "comment.description"
                                        , "comment.postid"
                                        , "comment.highlightexpiration")
                                ->get();
    }

    public function getByUserId($userid) {
        return $this->model->join("users", "comment.userid", "=", "users.id")
                            ->where("comment.userid", "=", $userid)
                            ->orderBy("comment.highlightexpiration", "desc")
                            ->orderBy("comment.created_at", "desc")
                            ->select("comment.userid"
                                        , "comment.id"
                                        , "users.login"
                                        , "users.subscriber"
                                        , "comment.highlight"
                                        , "comment.created_at"
                                        , "comment.description"
                                        , "comment.postid"
                                        , "comment.highlightexpiration")
                            ->get();
    }

    public function removeByUserId($userid) {
        return $this->model->where("userid", $userid)
                            ->delete();
    }
}