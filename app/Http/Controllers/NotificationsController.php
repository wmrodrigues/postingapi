<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Business\NotificationBusiness as NotificationBusiness;
use App\Models\Notification as Notification;
use App\Exceptions\EntityValidatioException;
use Illuminate\Support\Collection;
use App\Exceptions\EntityValidationException;
use App\Http\Helpers\TokenData;

class NotificationsController extends Controller {
    private $notificationBusiness;
    private $request;

    public function __construct(Request $request, NotificationBusiness $nb) {
        $this->request = $request;
        $this->notificationBusiness = $nb;
    }

    public function getByUserId($id) {
        try {
            $pager = $this->getPager($this->request);
            if (empty($id)) {
                return $this->createBadRequestResponse("userid parameter is required!");
            }

            $data = $this->notificationBusiness->getByUserId($id, $pager);
            if (empty($data) || empty($data->total())) {
                return $this->createEmptyResponse();
            }

            return $this->createPagedResponse($data);
        } catch(EntityValidationException $e) {
            return $this->createPreConditionFailedResponse($e->getMessage());
        } catch(Exception $e) {
            return $this->createInternalServerErrorResponse($e->getMessage());
        }
    }
}