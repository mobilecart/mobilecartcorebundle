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

use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MobileCart\CoreBundle\Entity\Order;
use MobileCart\CoreBundle\Shipping\RateRequest;

class ShippingController extends Controller
{

    public function ratesAction(Request $request)
    {
        $postcode = $request->get('postcode', '');
        $countryId = $request->get('country_id', '');
        $region = $request->get('region', '');
        $customerAddressId = $request->get('address_id', 0);
        $productIds = explode(',', $request->get('product_ids', ''));

        if ($customerAddressId) {
            // load the postcode, country_id, region from the customer address

            $address = $this->get('cart.entity')->find(EntityConstants::CUSTOMER_ADDRESS, $customerAddressId);
            if ($address) {
                $postcode = $address->getPostcode();
                $countryId = $address->getCountryId();
                $region = $address->getRegion();
            }
        }

        $cartItems = [];
        $items = $this->get('cart.session')->getItems();
        if ($items) {
            foreach($items as $item) {
                if (in_array($item->getProductId(), $productIds)) {
                    $cartItems[] = $item;
                }
            }
        }

        $request = new RateRequest();
        $request->fromArray([
            'to_array'   => 1,
            'hide_costs' => 1, // todo:
            'postcode'   => $postcode,
            'country_id' => $countryId,
            'region'     => $region,
            'cart_items' => $cartItems,
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

}
