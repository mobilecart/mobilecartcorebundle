<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Controller\REST;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use MobileCart\CoreBundle\Entity\Order;
use MobileCart\CoreBundle\Shipping\RateRequest;

/**
 * Shipping API controller.
 *  TODO : look at moving this out of the REST directory
 *   and into a "normal" event listener
 *
 * @Route("/api/shipping")
 */
class ShippingController extends Controller
{
    /**
     * Find shipping methods based on criteria
     *
     * @Route("/", name="api_shipping_rates")
     * @Method("GET")
     */
    public function ratesAction(Request $request)
    {
        $postcode = $request->get('postcode', '');
        $countryId = $request->get('country_id', '');
        $region = $request->get('region', '');

        $request = new RateRequest();
        $request->fromArray([
            'to_array'   => 1,
            'hide_costs' => 1, // todo:
            'postcode'   => $postcode,
            'country_id' => $countryId,
            'region'     => $region,
        ]);

        $rates = $this->get('cart.shipping')
            ->collectShippingRates($request);

        // hide costs
        if ($rates) {
            foreach($rates as $i => $rate) {
                unset($rates[$i]['cost']);
                unset($rates[$i]['handling_cost']);
            }
        }

        return new JsonResponse(array_values($rates));
    }

    /**
     * Find shipping methods based on criteria
     *
     * @Route("/methods", name="api_shipping_methods")
     * @Method("GET")
     */
    public function methodsAction(Request $request)
    {
        $postcode = $request->get('postcode', '');
        $countryId = $request->get('country_id', '');
        $region = $request->get('region', '');

        $request = new RateRequest();
        $request->fromArray([
            'include_all' => 1,
            'to_array'    => 1,
            'postcode'    => $postcode,
            'country_id'  => $countryId,
            'region'      => $region,
        ]);

        $rates = $this->get('cart.shipping')
            ->collectRates($request);

        // hide costs
        if ($rates) {
            foreach($rates as $i => $rate) {
                unset($rates[$i]['cost']);
                unset($rates[$i]['handling_cost']);
            }
        }


        return new JsonResponse($rates);
    }
}
