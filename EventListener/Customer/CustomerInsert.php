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
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
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
     * @param \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     * @return $this
     */
    public function setEntityService(\MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface $entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
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
        /** @var \MobileCart\CoreBundle\Entity\Customer $entity */
        $entity = $event->getEntity();
        $formData = $event->getFormData();

        if ($event->getFormData('is_shipping_same', false)) {
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

        try {
            $this->getEntityService()->persist($entity);
            $event->setSuccess(true);
            $event->addSuccessMessage('Customer Created !');
            if ($formData) {
                $this->getEntityService()->persistVariants($entity, $formData);
            }
        } catch(\Exception $e) {
            $event->addErrorMessage('An error occurred while saving the Customer');
        }
    }
}
