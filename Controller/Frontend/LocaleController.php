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
 * Locale controller.
 *
 */
class LocaleController extends Controller
{
	/**
     * @Route("/locale/{code}", name="cart_locale")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $localeService = $this->container->get('cart.locale');
        $code = $request->get('code', '');
        if ($localeService->hasLocale($code)) {
            $request->getSession()->set('_locale', $code);
        }

        $redirect = $request->headers->get('referer');
        if (!$redirect) {
            $redirect = $this->generateUrl('cart_home');
        }

        return new RedirectResponse($redirect);
    }

}