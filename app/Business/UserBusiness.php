<?php

namespace App\Business;

use App\Repositories\UserRepository as UserRepository;
use App\Business\Contracts\BusinessInterface;
use App\Exceptions\EntityValidationException;
use App\Facades\CacheFacade;

class UserBusiness implements BusinessInterface {
    private $userRepository;

    public function __construct(UserRepository $ur) {
        $this->userRepository = $ur;
    }

    public function paginate($pager) {
        return $this->userRepository->paginate($pager->currentPage, $pager->pageSize);
    }

    public function getById($id) {
        return $this->userRepository->getById($id);
    }

    public function save($entity) {
        if (!empty($entity->id)) {
            $data = $this->getById($entity->id);
            if (empty($data)) {
                throw new EntityValidationException("Invalid property id.");
            }

            $data->login = $entity->login;
            $data->password = $entity->password;
            $data->subscriber = $entity->subscriber;
            $data->email = $entity->email;
            $data->balance = $entity->balance;

            return $this->userRepository->save($data);
        }

        // sending a brand new entity
        return $this->userRepository->save($entity);
    }

    public function delete($id) {
        $this->userRepository->delete($id);
    }
    
    public function canMakePost($userid) {
        $lastPost = CacheFacade::getUserLastCommentTime($userid);
        if ($lastPost == 0) {
            return true;
        } else {
            $start = new \DateTime("@$lastPost");
            $end = new \DateTime("@" . time());
            $diff = $start->diff($end);
            // user make only one post per second
            return ($diff->s > 0);
        }
    }

    public function updateBalance($userid, $coins) {
        $data = $this->userRepository->getCurrentBalance($userid);
        $data->balance += $coins;
        $this->userRepository->updateBalance($userid, $data->balance);
    }

    public function getByLogin($login) {
        return $this->userRepository->getBy("login", $login);
    }

    public function getByLoginPassword($login, $password) {
        return $this->userRepository->getByLoginPassword($login, $password);
    }
}