<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MobileCart\CoreBundle\Entity\Content;
use MobileCart\CoreBundle\Form\ContentType;

/**
 * Content controller.
 *
 * @Route("/admin")
 */
class DashboardController extends Controller
{

    /**
     * Display dashboard
     *
     * @Route("/", name="cart_admin_dashboard")
     * @Method("GET")
     */
    public function indexAction()
    {
        $returnData = [];

        // todo : observer

        $tplPath = $this->get('cart.theme')->getTemplatePath('admin');
        $view = $tplPath . 'Dashboard:index.html.twig';
        return $this->render($view, $returnData);
    }

}
