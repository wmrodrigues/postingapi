<?php

namespace App\Business;

use App\Repositories\PostRepository as PostRepository;
use App\Business\Contracts\BusinessInterface;
use App\Exceptions\EntityValidationException;

class PostBusiness implements BusinessInterface {
    private $postRepository;

    public function __construct(PostRepository $pr) {
        $this->postRepository = $pr;
    }

    public function paginate($pager) {
        return $this->postRepository->paginate($pager->currentPage, $pager->pageSize);
    }

    public function getById($id) {
        return $this->postRepository->getById($id);
    }

    public function save($entity) {
        if (!empty($entity->id)) {
            $data = $this->getById($entity->id);
            if (empty($data)) {
                throw new EntityValidationException("Invalid property id.");
            }

            $data->userid = $entity->userid;
            $data->description = $entity->description;
            $data->posttype = $entity->posttype;

            return $this->postRepository->save($data);
        }

        // sending a brand new entity
        return $this->postRepository->save($entity);
    }

    public function delete($id) {
        $this->postRepository->delete($id);
    }
}