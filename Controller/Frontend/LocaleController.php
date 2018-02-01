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
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class LocaleController
 * @package MobileCart\CoreBundle\Controller\Frontend
 */
class LocaleController extends Controller
{
    /**
     * Update locale and redirect
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