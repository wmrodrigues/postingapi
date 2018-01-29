<?php

namespace App\Business;

use App\Repositories\NotificationRepository as NotificationRepository;
use App\Business\Contracts\BusinessInterface;
use App\Exceptions\EntityValidationException;
use App\Facades\CacheFacade;
use Illuminate\Support\Facades\Mail;

class NotificationBusiness implements BusinessInterface {
    private $notificationRepository;

    public function __construct(NotificationRepository $nr) {
        $this->notificationRepository = $nr;
    }

    public function paginate($pager) {
        return $this->notificationRepository->paginate($pager->currentPage, $pager->pageSize);
    }

    public function getById($id) {
        return $this->notificationRepository->getById($id);
    }

    public function save($entity) {
        if (!empty($entity->id)) {
            $data = $this->getById($entity->id);
            if (empty($data)) {
                throw new EntityValidationException("Invalid property id.");
            }

            $data->description = $entity->description;
            $data->fromuserid = $entity->fromuserid;
            $data->touserid = $entity->touserid;
            $data->read = $entity->read;

            return $this->notificationRepository->save($data);
        }

        // sending a brand new entity
        return $this->notificationRepository->save($entity);
    }

    public function delete($id) {
        $this->notificationRepository->delete($id);
    }

    public function sendNotificationEmail($data) {
        // Mail::raw("$data->description", function($msg) use($data) {
        //     $msg->to(array($data->tousermailaddress));
        //     $msg->from(array($data->fromusermailaddress));
        // });
    }

    public function getByUserId($userid, $pager) {
        return $this->notificationRepository->getByUserId($userid, $pager->currentPage, $pager->pageSize);
    }
}