<?php

namespace App\Repositories\Contracts;

/**
 * Inteface that holds all standard data methods
*/
interface RepositoryInterface {
    public function get($columns = array("*"));
    public function paginate($currentPage = 0, $pageSize = 10, $columns = array("*"));
    public function create(array $data);
    public function update(array $data, $id);
    public function save($model);
    public function delete($id);
    public function getById($id, $columns = array("*"));
    public function getBy($field, $value, $columns = array("*"));
}