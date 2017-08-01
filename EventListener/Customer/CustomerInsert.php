<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerInsert
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerInsert
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

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
     * @param CoreEvent $event
     */
    public function onCustomerInsert(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $request = $event->getRequest();
        $entity = $event->getEntity();
        $formData = $event->getFormData();

        // Customer shipping info
        $isShippingSame = isset($formData['is_shipping_same'])
            ? $formData['is_shipping_same']
            : false;

        if ($isShippingSame) {
            $entity->setIsShippingSame(true);
            $entity->copyBillingToShipping();
        }

        // encode password, handle hash
        if (isset($formData['password']['first']) && $formData['password']['first']) {
            $encoder = $this->getSecurityPasswordEncoder();
            $encoded = $encoder->encodePassword($entity, $formData['password']['first']);
            $entity->setHash($encoded);
        }

        $entity->setCreatedAt(new \DateTime('now'));

        $this->getEntityService()->persist($entity);

        if ($formData) {

            $this->getEntityService()
                ->persistVariants($entity, $formData);

        }

        if ($entity && $request->getSession()) {
            $request->getSession()->getFlashBag()->add(
                'success',
                'Customer Created!'
            );
        }

        $event->setReturnData($returnData);
    }
}
