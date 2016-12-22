<?php

namespace Dvtrung\CommentBundle\Service;

use Doctrine\ORM\EntityManager;
use AppBundle\Service\EntityServiceBase;

class Comment extends EntityServiceBase
{
    protected $em;

    const REPOSITORY_NAME = "CommentBundle:Comment";
    const DEFAULT_FIELDS = "id,objType,objId,userId";

    public function __construct(EntityManager $entityManager) {
        $this->em = $entityManager;
    }

    public function getJSONField($obj, $field) {
        $thread_codes = explode('-', $obj->getThread()->getCode());
        switch ($field) {
            case 'id':              return $obj->getId();
            case 'threadCode':      return $obj->getThread()->getCode();
            case 'objType':         return $thread_codes[0];
            case 'objId':           return $thread_codes[1];
            case 'userId':          return $obj->getUser()->getId();
            case 'content':         return $obj->getContent();
            case 'createdAt':       return $obj->getCreatedAt();
        }
    }

    public function getThreadByCode($thread_code) {
        $repository = $this->em->getRepository('CommentBundle:Thread');
        return $repository->findOneByCode($thread_code);
    }

    public function getComments($thread, $parent) {
        $repository = $this->em->getRepository('CommentBundle:Comment');
        return $repository->findBy([
            'thread' => $thread->getId(),
            'parent' => $parent
        ], ['createdAt' => 'ASC']);
    }
}
