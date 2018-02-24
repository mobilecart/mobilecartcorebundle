<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerUpdate
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerUpdate
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    protected $securityPasswordEncoder;

    public function setSecurityPasswordEncoder($encoder)
    {
        $this->securityPasswordEncoder = $encoder;
        return $this;
    }

    public function getSecurityPasswordEncoder()
    {
        return $this->securityPasswordEncoder;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
    }

    /**
     * @param $cartService
     * @return $this
     */
    public function setCartService($cartService)
    {
        $this->cartService = $cartService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->cartService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCustomerUpdate(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\Customer $entity */
        $entity = $event->getEntity();
        $formData = $event->getFormData();

        if (isset($formData['is_shipping_same']) && $formData['is_shipping_same']) {
            $entity->setIsShippingSame(true);
            $entity->copyBillingToShipping();
        }

        // encode password, handle hash
        if (isset($formData['password']['first']) && strlen($formData['password']['first']) > 6) {
            $encoder = $this->getSecurityPasswordEncoder();
            $encoded = $encoder->encodePassword($entity, $formData['password']['first']);
            $entity->setHash($encoded);
            $event->setIsPasswordChanged(true);
        }

        try {
            $this->getEntityService()->persist($entity);
            $event->setSuccess(true);
            $event->addSuccessMessage('Customer Updated !');
            if ($entity->getItemVarSet() && $formData) {
                $this->getEntityService()->persistVariants($entity, $formData);
            }
        } catch(\Exception $e) {
            $event->addErrorMessage('An error occurred while saving Customer');
        }

        if ($event->getSuccess()) {
            if (!$this->getCartService()->getIsAdminUser()) {
                $this->getCartService()->setCustomerEntity($entity);
            }
        }
    }
}
