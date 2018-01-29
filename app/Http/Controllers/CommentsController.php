<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Business\CommentBusiness as CommentBusiness;
use App\Models\Comment as Comment;
use App\Exceptions\EntityValidatioException;
use Illuminate\Support\Collection;
use App\Exceptions\EntityValidationException;
use App\Http\Helpers\TokenData;
use Illuminate\Support\Facades\Crypt;

class CommentsController extends Controller {
    private $commentBusiness;
    private $request;

    public function __construct(Request $request, CommentBusiness $cb) {
        $this->request = $request;
        $this->commentBusiness = $cb;
    }

    public function getByPostId($postid) {
        try {
            $pager = $this->getPager($this->request);
            if (empty($postid)) {
                return $this->createBadRequestResponse("postid parameter is required!");
            }

            $data = $this->commentBusiness->getByPostId($postid, $pager);
            if (empty($data)) {
                return $this->createEmptyResponse();
            }

            return $this->createPagedResponse($data);
        } catch(EntityValidationException $e) {
            return $this->createPreConditionFailedResponse($e->getMessage());
        } catch(\Exception $e) {
            return $this->createInternalServerErrorResponse($e->getMessage());
        }
    }

    public function getByUserId($userid) {
        try {
            $pager = $this->getPager($this->request);
            if (empty($userid)) {
                return $this->createBadRequestResponse("userid parameter is required!");
            }

            $data = $this->commentBusiness->getByUserId($userid, $pager);
            if (empty($data)) {
                return $this->createEmptyResponse();
            }

            return $this->createPagedResponse($data);
        } catch(EntityValidationException $e) {
            return $this->createPreConditionFailedResponse($e->getMessage());
        } catch(\Exception $e) {
            return $this->createInternalServerErrorResponse($e->getMessage());
        }
    }

    public function post() {
        try {
            $data = $this->request->all();
            if (empty($data)) {
                return $this->createBadRequestResponse();
            }

            $entity = new \stdClass();
            $entity->fromuserid = isset($data["fromuserid"]) ? $data["fromuserid"] : 0;
            $entity->postid = isset($data["postid"]) ? $data["postid"] : 0;
            $entity->description = isset($data["description"]) ? $data["description"] : "";
            $entity->highlight = isset($data["highlight"]) ? $data["highlight"] : 0;
            $entity->coins = isset($data["coins"]) ? $data["coins"] : 0;

            $result = $this->commentBusiness->createComment($entity);

            return $this->createCreatedResponse($entity->id);

        } catch(EntityValidationException $e) {
            return $this->createPreConditionFailedResponse($e->getMessage());
        } catch(\Exception $e) {
            return $this->createInternalServerErrorResponse($e->getMessage());
        }
    }

    public function delete(Request $request, $id) {
        try {
            $values = $this->getAuthTokenData($request);
            $username = $values[TokenData::USERNAME];

            $this->commentBusiness->remove($id, $username);
            return $this->createDefaultResponse();

        } catch(EntityValidationException $e) {
            return $this->createPreConditionFailedResponse($e->getMessage());
        } catch(\Exception $e) {
            return $this->createInternalServerErrorResponse($e->getMessage());
        }
    }

    public function deleteUserComments(Request $request, $postid, $userid) {
        try {
            $values = $this->getAuthTokenData($request);
            $username = $values[TokenData::USERNAME];

            $this->commentBusiness->removeUserComments($postid, $userid, $username);

            return $this->createDefaultResponse();
        } catch(EntityValidationException $e) {
            return $this->createPreConditionFailedResponse($e->getMessage());
        } catch(\Exception $e) {
            return $this->createInternalServerErrorResponse($e->getMessage());
        }
    }
}