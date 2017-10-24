<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\CartComponent;

/**
 * Class Calculator
 * @package MobileCart\CoreBundle\CartComponent
 */
class Calculator 
{
    const ITEMS = 'items';
    const SHIPMENTS = 'shipments';

    /**
     * @var Cart $cart
     */
    protected $cart;
    
    /**
     * @var array $discountGrid
     */
    protected $discountGrid;
    
    /**
     * Constructor
     *
     * @param Cart
     */
    public function __construct(Cart $cart = null)
    {
        $this->cart = $cart;
        $this->discountGrid = false;
    }

    /**
     * Set cart to this instance
     *
     * @param Cart
     * @return Calculator
     */
    public function setCart(Cart $cart)
    {
        $this->cart = $cart;
        return $this;
    }

    /**
     * Get cart for this instance
     *
     * @return Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * Decorator for float values
     */
    public function currency($value)
    {
        $value = (float) $value;
        return number_format($value, $this->getCart()->getCalculatorPrecision(), '.', '');
    }

    /**
     * Decorator for float values
     */
    public function format($value)
    {
        $value = (float) $value;
        return number_format($value, $this->getCart()->getPrecision(), '.', '');
    }

    /**
     * Get all totals, before discounts are dispersed
     *  except tax is already adjusted from pre-tax discounts
     *
     * @return array Associative array of cart totals
     */
    public function getTotals()
    {
        return [
            'items'       => $this->format($this->getItemTotal()),
            'shipments'   => $this->format($this->getShipmentTotal()),
            'discounts'   => $this->format($this->getDiscountTotal()),
//            'tax'         => $this->format($this->getTaxTotal()),
//            'grand_total' => $this->format($this->getGrandTotal()),
        ];
    }

    /**
     * Get totals with discounts dispersed
     *  tax is already adjusted from pre-tax discounts
     *
     * @return array Associative array of discounted cart totals
     */
    public function getDiscountedTotals()
    {
        return array(
            'items'       => $this->format($this->getDiscountedItemTotal()),
            'shipments'   => $this->format($this->getDiscountedShipmentTotal()),
            'tax'         => $this->format($this->getTaxTotal()),
            'grand_total' => $this->format($this->getGrandTotal()),
        );
    }

    /**
     * Get 'Grand' Total
     * This method is meant to be usable in a 'universal' way.
     *
     * @return string A numerical string, formatted for a float data type
     */
    public function getGrandTotal()
    {
        return $this->currency($this->getItemTotal() + 
                               $this->getShipmentTotal() + 
                               $this->getTaxTotal() - 
                               $this->getDiscountTotal());
    }

    /**
     * Build a grid of discounts vs items/shipments.
     * The idea is to show where discounts are being applied.
     *  Retrieve discounts in order of priority
     *  Apply discounts to Items/Shipments, until they are stopped or fully discounted
     *  Max Discount Amount/Quantity is handled here
     *
     *  
     *  This function assumes the discounts have already met conditions
     *
     * @param bool $recalculate
     * @return array
     */
    public function getDiscountGrid($recalculate = false)
    {
        if (is_array($this->discountGrid) && !$recalculate) {
            return $this->discountGrid;
        } else {
            $this->discountGrid = false;
        }
    
        /*
        General Plan here
            Build arrays with discountable amounts. Assume the full quantity and amount
                these will be subtracted from until:
                    amount is zero
                    item/shipment is 'stopped'
                    discounts are done loopping
            Loop all Discounts in order of priority
                Figure out if the discount has applicable shipments, items
                Initialize discount variables
                    flat amount is known immediately
                    sum of item qty is known
                    portion for general discounts eg toItems, toShipments, is known
                    max amount and qty is known
                    zero counters for current amount and qty
                Loop items
                    check various stopper values:
                        current amount and qty versus max amount and qty
                        item is stopped from a previous discount
                        
        
        //*/
        
        //TODO: enforce maxAmount between items and shipments

        $itemDiscounts = []; // d[itemKey][discountKey] = amount
        $shipmentDiscounts = []; // d[shipmentKey][discountKey] = amount

        $itemAmounts = []; // populate with initial item totals: price x quantity
        $shipmentAmounts = []; // populate with initial shipment totals: flat
        
        $stopped = []; // store item/shipment keys here when they are 'stopped'

        // Load up Items
        if ($this->getCart()->hasItems()) {
            foreach($this->getCart()->getItems() as $itemKey => $item) {
                if (!$item->getIsDiscountable()) {
                    continue;
                }
                $itemAmounts[$itemKey] = $this->currency($item->getPrice() * $item->getQty());
            }
        }

        // Load up Shipments
        if ($this->getCart()->hasShipments()) {
            foreach($this->getCart()->getShipments() as $shipmentKey => $shipment) {
                if (!$shipment->getIsDiscountable()) {
                    continue;
                }
                $shipmentAmounts[$shipmentKey] = $this->currency($shipment->getPrice());
            }
        }

        // Loop Discounts
        if ($this->getCart()->hasDiscounts()) {
            foreach($this->getCart()->getDiscounts() as $discountKey => $discount) {

                //does this discount apply to any Items?
                $discountHasItems = (bool) ($discount->isToItems() ||
                    ($discount->isToSpecified() && $discount->hasItems()));

                //does this discount apply to any Shipments?
                $discountHasShipments = (bool) ($discount->isToShipments() ||
                    ($discount->isToSpecified() && $discount->hasShipments()));

                //get specified Items, if applicable
                $discountItems = ($discount->hasItems()) ? $discount->getProductIds() : [];
                
                //get specified Shipments, if applicable
                $discountShipments = ($discount->hasShipments()) ? $discount->getShipments() : [];
                
                // fluid-like disperse for flat amounts, for generic amounts, percentage amounts
                
                // eg I have 4 toothbrushes, but can only get 2 at 50% off
                // eg I get 4 of the most expensive toothbrushes, get 2 at 50% off, but maxed at 5 dollars
                // eg I get 4 toothbrushes, totaling 12.50, but maxed at 4 dollars
                
                // Proportional uses
                //  $_isPerItem=false, $_to=$toItems, $_as=$asFlat, portion=(flatAmount / qtySum)
                //  $_isPerItem=true, use a function to give best discount, disperse qty's between items
                
                $flatAmount = ($discount->isFlat()) ? $discount->getValue() : 0;
                
                //get portion based on qtySum
                $portion = 0; //($discount->getIsProportional() && ) ?  : 0;
                if ($discount->isToItems() && $discount->getIsProportional()) {
                    //add up the quantities
                    $qtySum = 0;
                    if ($discountHasItems) {
                        switch($discount->getAppliedTo()) {
                            case Discount::APPLIED_TO_ITEMS:
                                foreach($this->getCart()->getItems() as $item) {
                                    if (!$item->getIsDiscountable()) {
                                        continue;
                                    }
                                    $qtySum += $item->getQty();
                                }
                                break;
                            case Discount::APPLIED_TO_SPECIFIED:
                                foreach($discount->getProductIds() as $productId) {
                                    $qtySum += $this->getCart()->findItem('product_id', $productId)->getQty();
                                }
                                break;
                            default:
                                //no-op
                                break;
                        }
                    }
                    
                    if ($qtySum > 0) {
                        $portion = $this->currency($flatAmount / $qtySum);
                    }
                }
                
                //max amount for the whole discount
                $maxAmount = ($discount->getMaxAmount() > 0) ? $discount->getMaxAmount() : 0;
                
                //max quantity to discount
                $maxQty = ($discount->getMaxQty() > 0) ? $discount->getMaxQty() : 0;

                $currentAmount = 0; //add amounts to this, then compare to max amount
                $currentQty = 0; //add amounts to this, then compare to max qty
                
                $maxItemQtys = [];
                
                if ($discountHasItems && $itemAmounts) {
                    foreach($itemAmounts as $itemKey => $itemAmount) {

                        //skip the item if it isnt specified in a specific discount
                        if ($discount->isToSpecified() && !$discount->hasItem($itemKey)) {
                            continue;
                        }
                        
                        //skip the item if it is stopped
                        if (isset($stopped[$itemKey])) {
                            continue;
                        }
                        
                        //break the loop if max qty is reached
                        if ($maxQty > 0 && !$discount->getIsMaxPerItem() && $currentQty >= $maxQty) {
                            break;
                        }
                        
                        //break the loop if max amount is reached
                        if ($maxAmount > 0 && !$discount->getIsMaxPerItem() && $currentAmount >= $maxAmount) {
                            break;
                        }

                        $items = $this->getCart()->getItems();
                        $item = $items[$itemKey];
                        
                        //skip the item if it isnt discountable
                        if (!$item->getIsDiscountable()) {
                            continue;
                        }

                        $discountAmount = 0; //only for this item

                        // Figure out some percentage stuff, and set aside
                        
                        // Percentage amount for a single quantity of this item
                        $percentUnitAmount = 0;

                        if ($discount->isPercent()) {
                            $percent = $discount->getValue();
                            if ($percent >= 1 && $percent <= 100) {
                                $percent = ($discount->getValue() / 100);
                            }

                            $percentUnitAmount = $this->currency($percent * $item->getPrice());
                        }

                        // Figure out some quantity stuff, whether discount is flat or percentage
                        
                        //want maxItemQtys when:
                        // 1. max qty has value, isMaxPerItem is false (doesnt matter if its flat or percentage)
                        //   (dont need to divide qty if it applies to every item)
                        // 2. discount is toItems, isProportional is true
                        // note: it doesnt matter if it is flat or percentage
                        
                        //only retrieve once
                        if (empty($maxItemQtys) && ($maxQty > 0 && !$discount->getIsMaxPerItem())) {
                            $maxItemQtys = $this->getMaxItemQtys($discount, $currentQty, $itemAmounts, $flatAmount, $percentUnitAmount);
                        }
                        
                        //handle maxQty logic, 
                        //biggest difference here is if the maxQty applies to every item or not
                        if ($maxQty > 0) {
                            
                            if ($discount->getIsMaxPerItem()) {
                                
                                //qty is not divided
                                switch($discount->getAppliedAs()) {
                                    case Discount::APPLIED_AS_FLAT:
                                        $discountAmount = $this->currency($flatAmount);
                                        break;
                                    case Discount::APPLIED_AS_PERCENT:
                                        //use maxQty or itemQty, whichever is less
                                        $calcQty = min($maxQty, $item->getQty());
                                        $discountAmount = $this->currency($calcQty * $percentUnitAmount);
                                        break;
                                    default:
                                        //no-op
                                        break;
                                }
                                
                            } else {
                                
                                //need to use our divided qty
                                //multiply qty by flat amount or by single-qty percentage amount
                                
                                $calcQty = $maxItemQtys[$itemKey];
                                
                                switch($discount->getAppliedAs()) {
                                    case Discount::APPLIED_AS_FLAT:
                                        $discountAmount = $this->currency($calcQty * $flatAmount);
                                        break;
                                    case Discount::APPLIED_AS_PERCENT:
                                        $discountAmount = $this->currency($calcQty * $percentUnitAmount);
                                        break;
                                    default:
                                        //no-op
                                        break;
                                }
                                
                                $currentQty += $calcQty;
                            }
                        } else {

                            switch($discount->getAppliedAs()) {
                                case Discount::APPLIED_AS_FLAT:
                                    $calcQty = 1;
                                    $discountAmount = $this->currency($flatAmount);
                                    break;
                                case Discount::APPLIED_AS_PERCENT:
                                    $calcQty = $item->getQty();
                                    $discountAmount = $this->currency($calcQty * $percentUnitAmount);
                                    break;
                                default:
                                    $calcQty = 0;
                                    break;
                            }

                            $currentQty += $calcQty;
                        }
                        
                        //enforce max amount
                        if ($maxAmount > 0 && $discountAmount > $maxAmount) {
                        
                            //could set the difference somewhere
                            $maxDiff = $discountAmount - $maxAmount;
                            
                            //over-ride discount amount
                            $discountAmount = $maxAmount;
                        }

                        if (isset($itemAmounts[$itemKey])) {
                            if ($itemAmounts[$itemKey] > 0) {

                                $diff = 0;
                                if ($itemAmounts[$itemKey] - $discountAmount < 0) {
                                    $diff = $discountAmount - $itemAmounts[$itemKey];
                                    $itemAmounts[$itemKey] = 0;
                                } else {
                                    $itemAmounts[$itemKey] -= $discountAmount;
                                }
                                
                                $itemDiscounts[$itemKey][$discountKey]['amount'] = $this->currency($discountAmount);
                                $itemDiscounts[$itemKey][$discountKey]['overflow'] = $diff;

                                $currentAmount += $this->currency($discountAmount);
                            } else {
                                $itemDiscounts[$itemKey][$discountKey]['amount'] = 0;
                                $itemDiscounts[$itemKey][$discountKey]['overflow'] = $discountAmount;
                            }
                            
                            $itemDiscounts[$itemKey][$discountKey]['is_pre_tax'] = $discount->getIsPreTax();
                            
                            if ($discount->getIsStopper()) {
                                $stopped[$itemKey] = 1;
                            }
                        }
                    }
                }

                if ($discountHasShipments && $this->getCart()->hasShipments()) {
                    foreach($this->getCart()->getShipments() as $shipmentKey => $shipment) {

                        if (!$shipment->getIsDiscountable()) {
                            continue;
                        }

                        if ($discount->isToSpecified() && !$discount->hasShipment($shipmentKey)) {
                            continue;
                        }
                        
                        if (isset($stopped[$shipmentKey])) {
                            continue;
                        }

                        if ($maxAmount > 0 && $currentAmount >= $maxAmount) {
                            break;
                        }

                        //does this apply?
                        if ($maxQty > 0 && $currentQty >= $maxQty) {
                            break;
                        }

                        $discountAmount = 0; //just for this shipment
                        $shipmentAmount = ($discount->getIsCompound()) ? $shipmentAmounts[$shipmentKey] : $shipment->getPrice();

                        if ($discount->isFlat()) {
                            $discountAmount = $discount->getValue();
                        } elseif ($discount->isPercent()) {
                            $percent = $discount->getValue();
                            if ($percent >= 1 && $percent <= 100) {
                                $percent = ($discount->getValue() / 100);
                            }
                            $discountAmount = ($percent * $shipmentAmount);
                        }

                        //enforce ceiling value if a max is set
                        if ($maxAmount > 0 && $discountAmount > $maxAmount) {
                            $discountAmount = $maxAmount;
                            //set diff somewhere
                        }

                        if (isset($shipmentAmounts[$shipmentKey])) {
                            if ($shipmentAmounts[$shipmentKey] > 0) {
                                $diff = 0;
                                if ($shipmentAmounts[$shipmentKey] - $shipmentAmount < 0) {
                                    $diff = $discountAmount - $shipmentAmounts[$shipmentKey];
                                    $shipmentAmounts[$shipmentKey] = 0;
                                    $discountAmount = $diff;
                                } else {
                                    $shipmentAmounts[$shipmentKey] -= $discountAmount;
                                }
                                
                                $shipmentDiscounts[$shipmentKey][$discountKey]['amount'] = $this->currency($discountAmount);
                                $shipmentDiscounts[$shipmentKey][$discountKey]['overflow'] = $diff;

                                $currentAmount += $this->currency($discountAmount);
                            } else {
                                $shipmentDiscounts[$shipmentKey][$discountKey]['amount'] = 0;
                                $shipmentDiscounts[$shipmentKey][$discountKey]['overflow'] = $discountAmount;
                            }
                            
                            $shipmentDiscounts[$shipmentKey][$discountKey]['is_pre_tax'] = $discount->getIsPreTax();
                            
                            if ($discount->getIsStopper()) {
                                $stopped[$shipmentKey] = 1;
                            }
                        }
                    }
                }
            }
        }

        /*
        array(
            'items' => array(
                'item-1' => array(
                    'discount-1' => array(
                        'amount' => float,
                        'overflow' => float,
                        'is_pre_tax' => bool,
                    ),
                    'discount-2' => array(
                        'amount' => float,
                        'overflow' => float,
                        'is_pre_tax' => bool,
                    ),
                ),
                'item-2' => array(
                    'discount-2' => array(
                        'amount' => float,
                        'overflow' => float,
                        'is_pre_tax' => bool,
                    ),
                ),
            ),
            'shipments' => array(
                'shipment-2' => array(
                    'discount-3' => array(
                        'amount' => float,
                        'overflow' => float,
                        'is_pre_tax' => bool,
                    )
                ),
            ),
        )
        //*/

        $discountGrid = [
            self::ITEMS     => $itemDiscounts,
            self::SHIPMENTS => $shipmentDiscounts,
        ];
        
        $this->discountGrid = $discountGrid;
        
        return $discountGrid;
    }

    /**
     * Get maximum number of non-partial quantities based on current quantity
     *
     * @param Discount $discount
     * @param $currentQty
     * @param $itemAmounts
     * @param $flatAmount
     * @param $percentUnitAmount
     * @return array
     */
    public function getMaxItemQtys(Discount $discount, $currentQty, $itemAmounts, $flatAmount, $percentUnitAmount)
    {
        //ignore the maxAmount set on the discount
        //the discountAmount should only be quantity of 1
        
        $unitAmount = 0;
        switch($discount->getAppliedAs()) {
            case Discount::APPLIED_AS_PERCENT:
                $unitAmount = $percentUnitAmount;
                break;
            case Discount::APPLIED_AS_FLAT:
                $unitAmount = $flatAmount;
                break;
            default:
                //no-op
                break;
        }
        
        //subtract from remainingQty, dont touch maxQty
        $maxQty = $remainingQty = $discount->getMaxQty() - $currentQty;
        
        //figure out max amount, whether we use it or not
        $maxAmount = min($discount->getMaxAmount(), $this->currency($unitAmount * $maxQty)); 
        
        //$items = $discount->getItems();
        
        $maxItemQtys = [];
        
        foreach($discount->getProductIds() as $itemKey => $productId) {
            
            if (!$remainingQty) {
                $maxItemQtys[$itemKey] = 0;
                continue;
            }
        
            //figure out the most first, regardless of current qty
            $fullMaxQty = floor($itemAmounts[$itemKey] / $unitAmount);
            
            //get the actual item qty
            $itemQty = $this->getCart()->getItem($itemKey)->getQty();
            
            //figure out which value to use
            $useQty = min(min($fullMaxQty, $remainingQty), $itemQty);
            
            $maxItemQtys[$itemKey] = $useQty;
            
            $remainingQty -= $useQty;
        }
        
        /* TODO revisit this
        if ($remainingQty > 0) {
            //grab a partial qty if possible
            asort($maxItemQtys);
            $tmpKey = reset($maxItemQtys);
            $maxItemQtys[$tmpKey]++;
        }//*/
        
        return $maxItemQtys;
    }

    /**
     * Get discounted item total
     *
     * @return string formatted as float
     */
    public function getDiscountedItemTotal()
    {
        $total = $this->getItemTotal() - $this->getItemDiscountTotal();
        if ($total < 0) {
            $total = 0;
        }
        return $this->currency($total);
    }

    /**
     * Get Item Total, before discounts
     *
     * @return string formatted as float
     */
    public function getItemTotal()
    {
        //always zero if empty
        if (!$this->getCart()->hasItems()) {
            return $this->currency(0);
        }
        
        $itemTotal = 0;
        foreach($this->getCart()->getItems() as $productKey => $item) {
            $price = $this->currency($item->getPrice());
            $qty = $item->getQty();
            $itemTotal += $this->currency($price * $qty);
        }

        return $this->currency($itemTotal);
    }

    /**
     * Get total amount for shipments, after discounts
     *
     * @return string formatted as float
     */
    public function getDiscountedShipmentTotal()
    {
        $total = $this->getShipmentTotal() - $this->getShipmentDiscountTotal();
        if ($total < 0) {
            $total = 0;
        }
        return $this->currency($total);
    }

    /**
     * Get total amount for shipments, before discounts
     *
     * @return string formatted as a numerical float
     */
    public function getShipmentTotal()
    {
        if (!$this->getCart()->hasShipments()) {
            return $this->currency(0);
        }

        $total = 0;
        foreach($this->getCart()->getShipments() as $shipmentKey => $shipment) {
            $total += $this->currency($shipment->getPrice());
        }

        return $this->currency($total);
    }

    /**
     * Get total amount for tax, after discounts
     *
     * @return string formatted as a numerical float
     */
    public function getTaxTotal()
    {
        if (!$this->getCart()->getIncludeTax()) {
            return $this->currency(0);
        }

        $discountedItemTotal = 0;
        $discountedShipmentTotal = 0;

        if ($this->getCart()->getDiscountTaxableLast()) {
            
            //overlap = taxable + discountable - itemTotal;
            //taxable -= overlap;

            if (($this->getTaxableItemTotal() + $this->getPreTaxItemDiscountTotal()) > $this->getItemTotal()) {
                $itemOverlapAmount = $this->currency($this->getTaxableItemTotal() + $this->getPreTaxItemDiscountTotal() - $this->getItemTotal());
                $discountedItemTotal = $this->currency($this->getTaxableItemTotal() - $itemOverlapAmount);
            } else {
                $discountedItemTotal = $this->getTaxableItemTotal() - $this->getPreTaxItemDiscountTotal();
            }

            if (($this->getTaxableShipmentTotal() + $this->getPreTaxShipmentDiscountTotal()) > $this->getShipmentTotal()) {
                $shipmentOverlapAmount = $this->currency($this->getTaxableShipmentTotal() + $this->getPreTaxShipmentDiscountTotal() - $this->getShipmentTotal());
                $discountedShipmentTotal = $this->currency($this->getTaxableShipmentTotal() - $shipmentOverlapAmount);
            } else {
                $discountedShipmentTotal = $this->getTaxableShipmentTotal() - $this->getPreTaxShipmentDiscountTotal();
            }

        } else {

            $discountedItemTotal = $this->getTaxableItemTotal() - $this->getPreTaxItemDiscountTotal();
            if ($discountedItemTotal <= 0) {
                $discountedItemTotal = $this->currency(0);
            }

            $discountedShipmentTotal = $this->getTaxableShipmentTotal() - $this->getPreTaxShipmentDiscountTotal();
            if ($discountedShipmentTotal <= 0) {
                $discountedShipmentTotal = $this->currency(0);
            }
        }

        $taxableTotal = $this->currency($discountedItemTotal + $discountedShipmentTotal);

        $totalTax = $this->currency($this->getCart()->getTaxRate() * $taxableTotal);

        return $this->currency($totalTax);
    }

    /**
     * Get Discount Total
     *  Also ensure that the sum of pre-tax discounts, and post-tax discounts
     *  is not more than is discountable, for both Items and Shipments
     *
     * @return string formatted as a numerical float
     */
    public function getDiscountTotal()
    {
        $total = $this->getItemDiscountTotal() + $this->getShipmentDiscountTotal();

        if ($total > $this->getDiscountableItemTotal() + $this->getDiscountableShipmentTotal()) {
            $total = $this->getDiscountableItemTotal() + $this->getDiscountableShipmentTotal();
        }
        return $this->currency($total);
    }

    /**
     * Get total discount of Items.
     *
     * @return string formatted as a numerical float
     */
    public function getItemDiscountTotal()
    {
        $total = $this->getPreTaxItemDiscountTotal() + $this->getPostTaxItemDiscountTotal();

        if ($total > $this->getDiscountableItemTotal()) {
            $total = $this->getDiscountableItemTotal();
        }

        return $this->currency($total);
    }

    /**
     * Get total discount of non-specified shipments.
     * This method can be used to ensure the sum of pre-tax 
     *  and post-tax discounts to Shipments, is not more than is discountable
     *
     * @return string formatted as a numerical float
     */
    public function getShipmentDiscountTotal()
    {
        $total = $this->getPreTaxShipmentDiscountTotal() + $this->getPostTaxShipmentDiscountTotal();

        if ($total > $this->getDiscountableShipmentTotal()) {
            $total = $this->getDiscountableShipmentTotal();
        }

        return $total;
    }

    /**
     * Get total Item/Shipment discount before tax
     *
     * @return string formatted as a numerical float
     */
    public function getPreTaxDiscountTotal()
    {
        $total = $this->getPreTaxShipmentDiscountTotal() + $this->getPreTaxItemDiscountTotal();

        return $this->currency($total);
    }

    /**
     * Get Discount total after tax
     *
     * @return string formatted as a numerical float
     */
    public function getPostTaxDiscountTotal()
    {
        $total = $this->getPostTaxItemDiscountTotal() + $this->getPostTaxShipmentDiscountTotal();

        return $this->currency($total);
    }

    /**
     * Get total Shipment discount before tax
     *
     * @return string formatted as a numerical float
     */
    public function getPreTaxShipmentDiscountTotal()
    {
        $total = $this->currency(0);

        $discountGrid = $this->getDiscountGrid();
        $shipmentDiscounts = (array) isset($discountGrid['shipments']) ? $discountGrid['shipments'] : [];
        
        if (count($shipmentDiscounts) > 0) {
            foreach($shipmentDiscounts as $shipmentKey => $discounts) {
                if (!count($discounts)) {
                    continue;
                }

                foreach($discounts as $key => $data) {
                    if (isset($data['is_pre_tax']) && (bool) $data['is_pre_tax']) {
                        $total += isset($data['amount']) ? $data['amount'] : 0;
                    }
                }
            }
        }

        // cannot be more than is discountable
        if ($total > $this->getDiscountableShipmentTotal()) {
            $total = $this->getDiscountableShipmentTotal();
        }

        return $this->currency($total);
    }

    /**
     * Get Total Shipment Discount After Tax
     *  By default, only non-specified and discountable items are included
     * 
     * @return string formatted as a numerical float
     */
    public function getPostTaxShipmentDiscountTotal()
    {
        $total = $this->currency(0);

        $discountGrid = $this->getDiscountGrid();
        $shipmentDiscounts = (array) isset($discountGrid['shipments']) ? $discountGrid['shipments'] : [];
        
        if (count($shipmentDiscounts) > 0) {
            foreach($shipmentDiscounts as $shipmentKey => $discounts) {
                if (!count($discounts)) {
                    continue;
                }

                foreach($discounts as $key => $data) {
                    if (isset($data['is_pre_tax']) && !(bool) $data['is_pre_tax']) {
                        $total += isset($data['amount']) ? $data['amount'] : 0;
                    }
                }
            }
        }

        // cannot be more than is discountable
        if ($total > $this->getDiscountableShipmentTotal()) {
            $total = $this->getDiscountableShipmentTotal();
        }

        return $this->currency($total);
    }

    /**
     * Get total Item discount before tax
     *
     * @return string formatted as a numerical float
     */
    public function getPreTaxItemDiscountTotal()
    {
        $total = $this->currency(0);

        $discountGrid = $this->getDiscountGrid();
        $itemDiscounts = (array) isset($discountGrid['items']) ? $discountGrid['items'] : [];
        
        if (count($itemDiscounts) > 0) {
            foreach($itemDiscounts as $itemKey => $discounts) {
                if (!count($discounts)) {
                    continue;
                }

                foreach($discounts as $key => $data) {
                    if (isset($data['is_pre_tax']) && (bool) $data['is_pre_tax']) {
                        $total += isset($data['amount']) ? $data['amount'] : 0;
                    }
                }
            }
        }

        // cannot be more than is discountable
        if ($total > $this->getDiscountableItemTotal()) {
            $total = $this->getDiscountableItemTotal();
        }

        return $this->currency($total);
    }

    /**
     * Get total Item discount after tax
     *
     * @return string formatted as a numerical float
     */
    public function getPostTaxItemDiscountTotal()
    {
        $total = $this->currency(0);

        $discountGrid = $this->getDiscountGrid();
        $itemDiscounts = (array) isset($discountGrid['items']) ? $discountGrid['items'] : [];
        
        if (count($itemDiscounts) > 0) {
            foreach($itemDiscounts as $itemKey => $discounts) {
                if (!count($discounts)) {
                    continue;
                }

                foreach($discounts as $key => $data) {
                    if (isset($data['is_pre_tax']) && !(bool) $data['is_pre_tax']) {
                        $total += isset($data['amount']) ? $data['amount'] : 0;
                    }
                }
            }
        }

        // cannot be more than is discountable
        if ($total > $this->getDiscountableItemTotal()) {
            $total = $this->getDiscountableItemTotal();
        }
        return $this->currency($total);
    }

    /**
     * Get the max amount that can be taxed, for Items and Shipments
     *
     * @return string formatted as a numerical float
     */
    public function getTaxableTotal()
    {
        return $this->currency($this->getTaxableItemTotal() + $this->getTaxableShipmentTotal());
    }

    /**
     * Get the max amount that can be taxed on items
     *
     * @return string formatted as a numerical float
     */
    public function getTaxableItemTotal()
    {
        $total = 0;
        if ($this->getCart()->hasItems()) {
            foreach($this->getCart()->getItems() as $productKey => $item) {
                if ($item->getIsTaxable()) {
                    $total += $this->currency($item->getPrice() * $item->getQty());
                }
            }
        }
        return $this->currency($total);
    }

    /**
     * Get taxable shipment total
     *  , by getting the sum of taxable shipments; regardless of type
     *
     * @return string formatted as a numerical float
     */
    public function getTaxableShipmentTotal()
    {
        $total = 0;
        if ($this->getCart()->hasShipments()) {
            foreach($this->getCart()->getShipments() as $shipmentKey => $shipment) {
                if ($shipment->getIsTaxable()) {
                    $total += $this->currency($shipment->getPrice());
                }
            }
        }
        return $this->currency($total);
    }

    /**
     * Get discountable shipment total
     *
     * @return string formatted as a numerical float
     */
    public function getDiscountableShipmentTotal()
    {
        $total = 0;
        if ($this->getCart()->hasShipments()) {
            foreach($this->getCart()->getShipments() as $shipmentKey => $shipment) {
                if ($shipment->getIsDiscountable()) {
                    $total += $this->currency($shipment->getPrice());
                }
            }
        }
        return $this->currency($total);
    }

    /**
     * Get the max amount that can be discounted from Items
     *
     * @return string formatted as a numerical float
     */
    public function getDiscountableItemTotal()
    {
        $total = 0;
        if ($this->getCart()->hasItems()) {
            foreach($this->getCart()->getItems() as $itemKey => $item) {
                if ($item->getIsDiscountable()) {
                    $total += $this->currency($item->getPrice() * $item->getQty());
                }
            }
        }
        return $this->currency($total);
    }

}
