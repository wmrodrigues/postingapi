<?php

namespace App\Business\Contracts;

interface BusinessInterface {
    public function paginate($pager);
    public function getById($id);
    public function save($entity);
    public function delete($id);
}