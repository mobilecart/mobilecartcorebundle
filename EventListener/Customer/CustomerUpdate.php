<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class CustomerUpdate
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerUpdate
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CartSessionService
     */
    protected $cartSessionService;

    protected $securityPasswordEncoder;

    /**
     * @var Event
     */
    protected $event;

    /**
     * @param $event
     * @return $this
     */
    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     */
    protected function getEvent()
    {
        return $this->event;
    }

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
     * @param $entityService
     * @return $this
     */
    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param $cartSessionService
     * @return $this
     */
    public function setCartSessionService($cartSessionService)
    {
        $this->cartSessionService = $cartSessionService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartSessionService
     */
    public function getCartSessionService()
    {
        return $this->cartSessionService;
    }

    /**
     * @param Event $event
     */
    public function onCustomerUpdate(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $entity = $event->getEntity();
        $formData = $event->getFormData();
        $request = $event->getRequest();

        if (isset($formData['is_shipping_same']) && $formData['is_shipping_same']) {
            $entity->setIsShippingSame(true);
            $entity->copyBillingToShipping();
        }

        // encode password, handle hash
        if (isset($formData['password']['first']) && strlen($formData['password']['first']) > 6) {
            $encoder = $this->getSecurityPasswordEncoder();
            $encoded = $encoder->encodePassword($entity, $formData['password']['first']);
            $entity->setHash($encoded);
            $event->setIsPasswordChanged(1);
        }

        $this->getEntityService()->persist($entity);

        if ($entity->getItemVarSet() && $formData) {

            // update var values
            $this->getEntityService()
                ->persistVariants($entity, $formData);

        }

        if ($event->getSection() == CoreEvent::SECTION_FRONTEND) {
            // update session info

            $this->getCartSessionService()
                ->setCustomerEntity($entity);
        }

        if ($entity && $request->getSession()) {
            $request->getSession()->getFlashBag()->add(
                'success',
                'Customer Updated!'
            );
        }

        $event->setReturnData($returnData);
    }
}
