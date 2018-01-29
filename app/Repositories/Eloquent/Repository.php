<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Container\Container as App;

/**
 * Class Repository
 * @package App\Repositories\Eloquent
*/

abstract class Repository implements RepositoryInterface {
    private $app;
    protected $model;

    public function __construct(App $app) {
        $this->app = $app;
        $this->makeModel();
    }

    abstract function model();

    public function get($columns = array("*")) {
        return $this->model->get($columns);
    }

    public function paginate($currentPage = 0, $pageSize = 10, $columns = array("*")) {
        return $this->model->paginate($pageSize, $columns, "page", $currentPage);
    }

    public function save($model) {
        return $model->save();
    }

    public function create(array $data) {
        return $this->model->create($data);
    }

    public function update(array $data, $id, $attribute = "id") {
        return $this->model->where($attribute, "=", $id)->update($data);
    }

    public function delete($id) {
        return $this->model->destroy($id);
    }

    public function getById($id, $columns = array("*")) {
        return $this->model->find($id, $columns);
    }

    public function getBy($attribute, $value, $columns = array("*")) {
        return $this->model->where($attribute, "=", $value)->first($columns);
    }

    private function makeModel() {
        $model = $this->app->make($this->model());
        if (!$model instanceof Model) {
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }
}