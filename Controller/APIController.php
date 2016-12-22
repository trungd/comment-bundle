<?php

namespace Dvtrung\CommentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use SpotBundle\Entity\Spot;
use RegionBundle\Entity\Region;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use PhotoBundle\Entity\Photo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use CommentBundle\CommentBundle;
use CommentBundle\Entity\Thread;
use CommentBundle\Entity\Comment;
use AppBundle\Controller\APIControllerBase;

/**
* Spot controller.
*
* @Route("/api/comment")
*/
class APIController extends APIControllerBase
{
    /**
    * @Route("/{id}", name="api_comment", options={"expose"=true}, requirements={"id": "\d+"})
    * @Method({"GET", "DELETE"})
    */
    public function apiComment($id, Request $request)
    {
        if ($request->getMethod() == 'GET') {
            $comment = $this->get('comment')->getById($id);
            if (!$comment) return $this->fail('Comment not found.');
            else return $this->success($this->get('comment')->getJSON($comment, $request->get('fields')));
        } else if ($request->getMethod() == "DELETE") {
            $comment = $this->get('comment')->getById($id);
            $em = $this->getDoctrine()->getManager();
            $em->remove($comment);
            $em->flush();
            return $this->success(null);
        }
    }

    /**
    * @Route("/{code}/list", name="api_comment_list", options={"expose"=true})
    * @Method({"GET"})
    */
    public function apiCommentListAction($code, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $thread = $this->get('comment')->getThreadByCode($code);
        $parent = $request->get('parent');
        if (!$thread) {
            $thread = new Thread();
            $thread->setCode($code);
            $em->persist($thread);
            $em->flush();
        }

        return $this->success(
            $this->get('comment')->getJSONArray($this->get('comment')->getComments($thread, $parent), $request->get('fields'))
        );
    }

    /**
    * @Route("/thread/{objType}/{objId}/count", name="api_comment_count", options={"expose"=true})
    * @Method({"GET"})
    */
    public function apiCommentCountAction($objType, $objId, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $thread = $this->get('comment')->getThreadByCode($objType.'-'.$objId);
        $parent = $request->get('parent');
        if (!$thread) return $this->success(['commentCount' => 0]);
        $threads = $this->get('comment')->getComments($thread, $parent);
        return $this->success(['commentCount' => count($threads)]);
    }

    /**
    * @Route("/{id}/permission", name="api_comment_permission", options={"expose"=true}, requirements={"id": "\d+"})
    * @Method({"GET"})
    */
    public function apiCommentPermissionAction($id, Request $request)
    {
        if ($request->getMethod() == 'GET') {
            $comment = $this->get('comment')->getById($id);
            $user = $this->getAPIUser();
            if (!$comment) return $this->fail('Comment not found.');
            else return $this->success([
                'canDelete' => $user && ($user->getId() == $comment->getUser()->getId()),
            ]);
        }
    }

    /**
    * @Route("/thread/{objType}/{objId}/comment", name="api_comment_post", options={"expose"=true})
    * @Method({"POST"})
    */
    public function apiCommentPostAction($objType, $objId, Request $request) {
        $thread = $this->get('comment')->getThreadByCode($objType."-".$objId);
        $user = $this->getAPIUser();

        if (!$user) return $this->failNotAllowed();
        if (strlen($request->request->get('content')) == 0) return $this->fail('Chưa nhập nội dung');

        $em = $this->getDoctrine()->getManager();

        $comment = new Comment();
        $comment->setThread($thread)
        ->setUser($user)
        ->setContent($request->request->get('content'))
        ->setParent($this->get('comment')->getById($request->request->get('parent')))
        ;

        $em->persist($comment);
        $em->flush();

        if (!$comment->getParent()) $this->get('user.activity.add')->addCommentActivity($comment);

        return $this->success($this->get('comment')->getJSON($comment, $request->get('fields')));
    }
}
