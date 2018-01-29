<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Helpers\Pager;
use Illuminate\Routing\UrlGenerator;
use App\Exceptions\EntityValidationException;
use Illuminate\Support\Facades\Crypt;

class Controller extends BaseController {
    public function getPager($request) {
        $pager = new Pager();
        
        $pager->pageSize = !$request->hasHeader('pageSize') ? 10 : $request->header('pageSize');
        $pager->currentPage = !$request->hasHeader('currentPage') ? 0 : $request->header('currentPage');

        $pager->currentPage++;

        return $pager;
    }

    public function createPagedResponse($data) {
        $items = array();
        foreach ($data as $item) {
            array_push($items, $item);
        }

        return response()->json($items);
    }

    public function createDefaultResponse($data = null) {
        return response()->json($data, 200);
    }

    public function createCreatedResponse($data) {
        return response()->json($data, 201);
    }

    public function createEmptyResponse() {
        return response()->make("", 204);
    }

    public function createBadRequestResponse($data = null) {
        return response()->json('poorly formed data. =( '.$data, 400);
    }

    public function createPreConditionFailedResponse($data) {
        return response()->json($data, 412);
    }

    public function createInternalServerErrorResponse($data) {
        return response()->json($data, 500);
    }

    public function getAuthTokenData($request) {
        $token = $request->header('Authorization');
        if (!empty($token)) {
            $split = explode(" ", $token);
            if (strtolower($split[0]) == "bearer") {
                $token = $split[1];
                $token = Crypt::decrypt($token);
                $values = explode('|', $token);
                return $values;
            }
        }
        return null;
    }
}
