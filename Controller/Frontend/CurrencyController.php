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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Currency controller.
 *
 */
class CurrencyController extends Controller
{
	/**
     * @Route("/currency/{code}", name="cart_currency")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $currencyService = $this->container->get('cart.currency');
        $code = $request->get('code', '');
        if ($currencyService->hasRate($code)) {
            $this->get('cart.session')->setCurrency($code);
        }

        $redirect = $request->headers->get('referer');
        if (!$redirect) {
            $redirect = $this->generateUrl('cart_home');
        }

        return new RedirectResponse($redirect);
    }

}