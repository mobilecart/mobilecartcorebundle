<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RewriteController extends Controller
{

    // todo : look at CoreBundle/Routing/RewriteLoader

    public function indexAction(Request $request)
    {
        $slug = $request->get('slug', '');

        // todo: lookup url_rewrite row

        $em = $this->getDoctrine()->getManager();
        $rewrite = $em->getRepository($this->container->getParameter('cart.repo.item'))->findOneBy(array(
            'slug' => $slug,
        ));

        if (!$rewrite) {
            throw $this->createNotFoundException('Unable to find a Product, Category, or Content page.');
        }

        $params = (array) json_decode($rewrite->getParamsJson());

        // todo: determine if loading single entity

        $class = basename(str_replace('\\', '/', get_class($item)));
        switch($class) {
            case 'Category':
                return $this->forward('MobileCartCoreBundle:Frontend\Product:index', array(
                    'cat_slug' => $slug,
                    'category' => $item,
                ));
                break;
            case 'Product':
                return $this->forward('MobileCartCoreBundle:Frontend\Product:view', array(
                    'slug' => $slug,
                    'item' => $item,
                ));
                break;
            case 'Content':

                break;
            case 'Tag':

                break;
            default:
                //
                break;
        }

        throw $this->createNotFoundException('Unable to find a Product, Category, or Content page.');
    }

}
