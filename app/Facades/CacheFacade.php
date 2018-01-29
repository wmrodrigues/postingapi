<?php

namespace App\Facades;

use App\Repositories\UserRepository as UserRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Container\Container;

class CacheFacade {
    const CACHE_USERBALANCES = "userbalances";
    const CACHE_USERCOMMENTTIMES = "usercommenttimes";
    const CACHE_POSTCOMMENTS = "postcomments";
    const CACHE_USERCOMMENTS = "usercomments";

    public static function getUserBalance($userid) {
        $userBalances = Cache::get(static::CACHE_USERBALANCES);
        $userBalances = collect($userBalances);
        $value = $userBalances->where("id", $userid)->first();
        if (empty($value)) {
            $ur = new UserRepository(new Container());
            $value = $ur->getCurrentBalance($userid);
            $userBalances->put($userid, $value);
            Cache::put(static::CACHE_USERBALANCES, $userBalances, env("CACHE_MINUTES", 10));
        }
        return $value->balance;
    }

    public static function updateUserBalance($userid, $balance) {
        $userBalances = Cache::get(static::CACHE_USERBALANCES);
        $userBalances = collect($userBalances);
        $value = $userBalances->where("id", $userid)->first();
        if (empty($value)) {
            $ur = new UserRepository();
            $value = $ur->getCurrentBalance($userid);
            $value->balance += $balance;
            $userBalances->put($value);
        } else {
            $value->balance += $balance;
            $userBalances->put($userid, $value);
        }

        Cache::put(static::CACHE_USERBALANCES, $userBalances, env("CACHE_MINUTES", 10));
    }

    public static function updateUserLastCommentTime($userid, $time = 0) {
        $time = $time ?: time();
        
        $times = Cache::get(static::CACHE_USERCOMMENTTIMES);
        $times = collect($times);
        $value = $times->where("id", $userid)->first();
        if (empty($value)) {
            $value = new \stdClass();
            $value->id = $userid;
            $value->time = $time;
            $times->put($userid, $value);
        } else {
            $times->put($userid, $time);
        }
        Cache::put(static::CACHE_USERCOMMENTTIMES, $times, env("CACHE_MINUTES", 10));
    }

    public static function getUserLastCommentTime($userid) {
        $times = Cache::get(static::CACHE_USERCOMMENTTIMES);
        $times = collect($times);
        $value = $times->where("id", $userid)->first();
        if (!empty($value)) {
            return $value->time;
        }

        return 0;
    }

    public static function getPostComments($postid) {
        $comments = Cache::get(static::CACHE_POSTCOMMENTS);
        $now = new \DateTime();
        $comments = collect($comments);
        $values = $comments->where("postid", $postid)->all();

        $values = collect($values);
        $highlight = $values->filter(function($item) use($now) {
                                return $item->highlight && new \DateTime($item->highlightexpiration) > $now;
                            })
                            ->sortByDesc(function($item) {
                                return $item->expiration . "-" . $item->coins;
                            })
                            ->first();
        if ($highlight) {
            // put the highlithed item on first position
            $values = $values->filter(function($item) use($highlight) {
                                        return $item->id != $highlight->id;
                                })
                                ->sortByDesc("created_at");
            $values->prepend($highlight);
            return $values->all();
        } else {
            return $values->sortByDesc("created_at")->all();
        }
    }

    public static function putPostComment($data) {
        $comments = Cache::get(static::CACHE_POSTCOMMENTS);
        $comments = collect($comments);
        $comments->push($data);
        $comments = $comments->all();
        Cache::put(static::CACHE_POSTCOMMENTS, $comments, env("CACHE_MINUTES", 10));
    }

    public static function putPostComments($data) {
        $comments = Cache::get(static::CACHE_POSTCOMMENTS);
        $comments = collect($comments);
        // insert a whole list
        $comments  = $comments->merge($data)->all();
        Cache::put(static::CACHE_POSTCOMMENTS, $comments, env("CACHE_MINUTES", 10));
    }

    public static function getUserComments($userid) {
        $comments = Cache::get(static::CACHE_USERCOMMENTS);
        $now = new \DateTime();
        $comments = collect($comments);
        $values = $comments->where("userid", $userid)->all();
        return $values;
    }

    public static function putUserComment($data) {
        $comments = Cache::get(static::CACHE_USERCOMMENTS);
        $comments = collect($comments);
        $comments->push($data);
        $comments = $comments->all();
        Cache::put(static::CACHE_USERCOMMENTS, $comments, env("CACHE_MINUTES", 10));
    }

    public static function putUserComments($data) {
        $comments = Cache::get(static::CACHE_USERCOMMENTS);
        $comments = collect($comments);
        // insert a whole list
        $comments  = $comments->merge($data);
        Cache::put(static::CACHE_USERCOMMENTS, $comments, env("CACHE_MINUTES", 10));
    }
}