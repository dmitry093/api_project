<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageNotFoundController extends Controller
{

    public function pageNotFoundAction()
    {
        throw new NotFoundHttpException('Page not found in API');
    }

}