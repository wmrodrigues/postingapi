<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Business\UserBusiness as UserBusiness;
use App\Models\User as User;
use App\Exceptions\EntityValidationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use App\Http\Helpers;

class AccountsController extends Controller {
    private $userBusiness;
    private $request;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request, UserBusiness $ub) {
        $this->request = $request;
        $this->userBusiness = $ub;
    }

    public function requestToken(Request $request) {
        try {
            $expires = env('TOKEN_EXPIRES_SECONDS');
            $username = $request->header('username');
            $password = $request->header('password');
            $date = new \DateTime();
            $date->modify("+$expires seconds");

            //search on database for the user entity
            $user = $this->userBusiness->getByLoginPassword($username, Crypt::encrypt($password));
            if (!$user) {
                return $this->createBadRequestResponse("No user found with the provided credentials");
            }

            $token = new \stdClass();
            $token->access_token = Crypt::encrypt($username . '|' . $date->format('Y-m-d H:i:s'));
            $token->expires_in = $expires;
            $token->date = $date->format('Y-m-d H:i:s');

            return $this->createDefaultResponse($token);

        } catch(Exception $e) {
            return $this->createInternalServerErrorResponse($e->getMessage());
        }
    }

    public function validateToken(Request $request) {
        try {
            $token = $request->header('token');

            $token = Crypt::decrypt($token);
            $values = explode('|', $token);

            $date = $values[TokenData::DATETIME];
            $now = new \DateTime();
            $expires = new \DateTime($date);
            if ($now < $expires) {
                echo 'Authorized!';
            }

            return $this->createDefaultResponse($values);

        } catch(Exception $e) {
            return $this->createInternalServerErrorResponse($e->getMessage());
        }
    }
}