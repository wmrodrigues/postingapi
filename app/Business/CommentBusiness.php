<?php

namespace App\Business;

use App\Repositories\CommentRepository as CommentRepository;
use App\Business\Contracts\BusinessInterface;
use App\Exceptions\EntityValidationException;
use App\Facades\CacheFacade;
use App\Business\UserBusiness as UserBusiness;
use App\Business\TransactionBusiness as TransactionBusiness;
use App\Business\PostBusiness as PostBusiness;
use App\Models\Comment;
use App\Models\Transaction;
use App\Models\Notification;
use App\Business\NotificationBusiness as NotificationBusiness;

class CommentBusiness implements BusinessInterface {
    private $commentRepository;
    private $userBusiness;
    private $transactionBusiness;
    private $postBusiness;
    private $notificationBusiness;

    public function __construct(CommentRepository $cr, UserBusiness $ub, TransactionBusiness $tb, PostBusiness $pb, NotificationBusiness $nb) {
        $this->commentRepository = $cr;
        $this->userBusiness = $ub;
        $this->transactionBusiness = $tb;
        $this->postBusiness = $pb;
        $this->notificationBusiness = $nb;
    }

    public function paginate($pager) {
        return $this->commentRepository->paginate($pager->currentPage, $pager->pageSize);
    }

    public function getById($id) {
        return $this->commentRepository->getById($id);
    }

    public function save($entity) {
        if (!empty($entity->id)) {
            $data = $this->getById($entity->id);
            if (empty($data)) {
                throw new EntityValidationException("Invalid property id.");
            }

            $data->userid = $entity->userid;
            $data->postid = $entity->postid;
            $data->description = $entity->description;
            $data->highlight = $entity->highlight;

            return $this->commentRepository->save($data);
        }

        // sending a brand new entity
        return $this->commentRepository->save($entity);
    }

    public function delete($id) {
        $this->commentRepository->delete($id);
    }
    
    public function getByPostId($postid, $pager) {
        $data = CacheFacade::getPostComments($postid);
        if (empty($data)) {
            $data = $this->commentRepository->getByPostId($postid);
            CacheFacade::putPostComments($data);
        }
        
        $data = collect($data);
        $data = $data->forPage($pager->currentPage, $pager->pageSize);
        return $data;
    }

    public function getByUserId($userid, $pager) {
        $data = CacheFacade::getUserComments($userid);
        if (empty($data)) {
            $data = $this->commentRepository->getByUserId($userid);
            CacheFacade::putUserComments($data);
        }

        $data = collect($data);
        $data = $data->forPage($pager->currentPage, $pager->pageSize);
        return $data;
    }

    public function createComment($data) {
        $post = $this->postBusiness->getById($data->postid);
        if (!empty($post)) {
            $fromuser = $this->userBusiness->getById($data->fromuserid);
            $touser = $this->userBusiness->getById($post->userid);
            if (!empty($fromuser)) {
                if (!empty($touser)) {

                    $highlightexpiration = new \DateTime();
                    // non subscriber user can't comment on another non subscriber user post, unless a highlight is requested
                    if (!$fromuser->subscriber && !$touser->subscriber && !$data->highlight) {
                        throw new EntityValidationException("Non subscriber users can't comment on others non subscriber users posts, unless a highlight is requested.");
                    } else {
                        /*
                        only this cases are accepted...
                        $fromuser->subscriber ||
                        (!$fromuser->subscriber && $touser->subscriber) ||
                        ($fromuser->subscriber && !$touser->subscriber) ||
                        */
                        // checking the user balance to post a comment
                        if ($data->highlight) {
                            $userBalance = CacheFacade::getUserBalance($data->fromuserid);
                            // user balance must be enough to pay the highlight and the retention fee
                            if ($userBalance < $data->coins + ceil($data->coins/100 * env("RETENTION_COIN_PERCENT", 5))) {
                                throw new EntityValidationException("User has not enough balance to comment on post.");
                            }
                            $highlightexpiration->add(new \DateInterval("PT$data->coins" . "M"));
                            // checking the ammount of comment por second
                            if (!$this->userBusiness->canMakePost($data->fromuserid)) {
                                throw new EntityValidationException("User cannot make a comment right now.");
                            }
                        }

                        // in fact, after all validations, here we create the comment!
                        $comment = new Comment();
                        $comment->userid = $fromuser->id;
                        $comment->postid = $post->id;
                        $comment->description = $data->description;
                        $comment->highlight = $data->highlight;
                        $comment->highlightexpiration = $highlightexpiration;
                        $comment->coins = $data->coins;
                        if ($this->save($comment)) {
                            $data->id = $comment->id;
                            $comment->login = $fromuser->login;
                            if ($comment->highlight) {
                                // generate a transaction for highlighting the comment
                                $transaction = new Transaction();
                                $transaction->userid = $fromuser->id;
                                $transaction->postid = $post->id;
                                $transaction->commentid = $comment->id;
                                $transaction->coins = $data->coins;
                                // in here we already update the user balance!
                                $this->transactionBusiness->generateHightlightTransaction($transaction);
                            }
                            
                            $now = new \DateTime();
                            $now = $now->format("d/m/Y H:i:s");
                            // send a notification to post owner
                            $notification = new Notification();
                            $notification->description = "Hey there! The user $fromuser->login has just commented on a post of yours in $now ! Click here and see what you have got!";
                            $notification->fromuserid = $fromuser->id;
                            $notification->touserid = $touser->id;
                            $expiration = new \DateTime();
                            $expiration->add(new \DateInterval("PT" . env("NOTIFICATION_EXPIRATION_HOURS", 7) . "H"));
                            $notification->expiration = $expiration;
                            $this->notificationBusiness->save($notification);

                            $notification->fromusermailaddress = $fromuser->email;
                            $notification->tousermailaddress = $touser->email;
                            
                            // send a e-mail with the notification data
                            $this->notificationBusiness->sendNotificationEmail($notification);

                            CacheFacade::updateUserLastCommentTime($fromuser->id);
                            CacheFacade::putPostComment($comment);
                            CacheFacade::putUserComment($comment);
                        } else {
                            throw new Exception("For some unknown reason, it wasn't possible to create your comment, please try again latter.");
                        }
                    }
                } else {
                    throw new EntityValidationException("No user was found with id: $data->touserid");
                }
            } else {
                throw new EntityValidationException("No user was found with id: $data->fromuserid");
            }
        } else {
            throw new EntityValidationException("No post was found with id: $data->postid");
        }
    }

    public function remove($id, $login) {
        $owner = $this->userBusiness->getByLogin($login);
        if ($owner) {
            $comment = $this->getById($id);
            if ($comment) {
                if ($comment->userid == $owner->id) {
                    // this is  the comment owner, so the we allow the comment to be removed
                    $this->delete($id);
                } else {
                    $post = $this->postBusiness->getById($comment->postid);
                    if ($post->userid == $owner->id) {
                        // this is the post owner, thats why he can remove the comment
                        $this->delete($id);
                    } else {
                        throw new EntityValidationException("The user with login $login cannot remove this comment because he is not its owner, neither the post owner.");
                    }
                }
            } else {
                throw new EntityValidationException("No comment was found with id $id provided.");
            }
        } else {
            throw new EntityValidationException("No user was found with login $login.");
        }
    }

    public function removeUserComments($postid, $userid, $login) {
        $owner = $this->userBusiness->getByLogin($login);
        if ($owner) {
            $post = $this->postBusiness->getById($postid);
            if ($post) {
                if ($post->userid == $owner->id) {
                    // once the user is the owner of the post, then he is allowed to remove all comments from a certain user
                    $this->commentRepository->removeByUserId($userid);
                } else {
                    throw new EntityValidationException("The user with login $login cannot remove the comments of this post because he is not its owner.");
                }
            } else {
                throw new EntityValidationException("No post was found with id $postid provided.");
            }
        } else {
            throw new EntityValidationException("No user was found with login $login");
        }
    }
}