<?php

namespace AppBundle\Controller;

use AppBundle\Form\BlogPostType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 *
 * @RouteResource("post")
 */
class BlogPostsController extends FOSRestController implements ClassResourceInterface
{
    private function getBlogPostRepository()
    {
        return $this->get('AppBundle.BlogPostRepository');
    }
    
    /**
     * Gets an individual Blog Post
     *
     * @param int $id
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @ApiDoc(
     *     output="AppBundle\Entity\BlogPost",
     *     statusCodes={
     *         200 = "Returned when successful",
     *         404 = "Return when not found"
     *     }
     * )
     */
    public function getAction(int $id){
        $blogPost =  $this->getBlogPostRepository()->createFindOneByIdQuery($id)->getSingleResult();
    
        if ($blogPost === null) {
            return new View(null, Response::HTTP_NOT_FOUND);
        }

        return $blogPost;
    }
    
    /**
     * Gets a collection of BlogPosts
     *
     * @return array
     *
     * @ApiDoc(
     *     output="AppBundle\Entity\BlogPost",
     *     statusCodes={
     *         200 = "Returned when successful",
     *         404 = "Return when not found"
     *     }
     * )
     */
    public function cgetAction()
    {
        return $this->getBlogPostRepository()->createFindAllQuery()->getResult();
    }
    
    /**
     * Creates a new BlogPost
     *
     * @param Request $request
     * @return View|\Symfony\Component\Form\Form
     *
     * @ApiDoc(
     *     input="AppBundle\Form\Type\BlogPostType",
     *     output="AppBundle\Entity\BlogPost",
     *     statusCodes={
     *         201 = "Returned when a new BlogPost has been successful created",
     *         404 = "Return when not found"
     *     }
     * )
     */
    public function postAction(Request $request)
    {
        $form = $this->createForm(BlogPostType::class, null, [
            'csrf_protection' => false,        
        ]);
        
        $form->submit($request->request->all());
        
        if (!$form->isValid()) {
            return $form;
        }

        $blogPost = $form->getData();
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($blogPost);
        $em->flush();
        $routeOptions = [
            'id' => $blogPost->getId(),
            '_format' => $request->get('_format'),
        ];
        return $this->routeRedirectView('get_post', $routeOptions, Response::HTTP_CREATED);
    }
}