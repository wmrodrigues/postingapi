<?php

namespace App\Business;

use App\Repositories\TransactionRepository as TransactionRepository;
use App\Business\Contracts\BusinessInterface;
use App\Exceptions\EntityValidationException;
use App\Business\UserBusiness as UserBusiness;
use App\Facades\CacheFacade;
use App\Models\Transaction;

class TransactionBusiness implements BusinessInterface {
    private $transactionRepository;
    private $userBusiness;

    public function __construct(TransactionRepository $tr, UserBusiness $ub) {
        $this->transactionRepository = $tr;
        $this->userBusiness = $ub;
    }

    public function paginate($pager) {
        return $this->transactionRepository->paginate($pager->currentPage, $pager->pageSize);
    }

    public function getById($id) {
        return $this->transactionRepository->getById($id);
    }

    public function save($entity) {
        if (!empty($entity->id)) {
            $data = $this->getById($entity->id);
            if (empty($data)) {
                throw new EntityValidationException("Invalid property id.");
            }

            $data->userid = $entity->userid;
            $data->postid = $entity->postid;
            $data->commentid = $entity->commentid;
            $data->coins = $entity->coins;
            $data->parentid = $entity->parentid;

            return $this->transactionRepository->save($data);
        }

        // sending a brand new entity
        return $this->transactionRepository->save($entity);
    }

    public function delete($id) {
        $this->transactionRepository->delete($id);
    }
    
    public function generateHightlightTransaction($data) {
        $this->save($data);
        $coins = $data->coins;
        // aways using a fallback, just in case
        $retention = env("RETENTION_COIN_PERCENT", 5);
        // we do this because we're using coins, an integer stuff, thats why we can't deal with decimal numbers
        $data->coins = ceil($data->coins * ($retention/100));
        $coins += $data->coins;
        
        $child = new Transaction();
        $child->parentid = $data->id;
        $child->userid = $data->userid;
        $child->postid = $data->postid;
        $child->commentid = $data->commentid;
        $child->coins = $data->coins;
        $this->save($child);

        $this->userBusiness->updateBalance($data->userid, -$coins);

        CacheFacade::updateUserBalance($data->userid, -$coins);
    }
}