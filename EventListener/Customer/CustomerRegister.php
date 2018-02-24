<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerRegister
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerRegister
{
    protected $passwordEncoder;

    /**
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CurrencyService
     */
    protected $currencyService;

    public function setSecurityPasswordEncoder($passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        return $this;
    }

    public function getSecurityPasswordEncoder()
    {
        return $this->passwordEncoder;
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
     * @param \MobileCart\CoreBundle\Service\CurrencyService $currencyService
     * @return $this
     */
    public function setCurrencyService(\MobileCart\CoreBundle\Service\CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyService
     */
    public function getCurrencyService()
    {
        return $this->currencyService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCustomerRegister(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\Customer $entity */
        $entity = $event->getEntity();
        $formData = $event->getFormData();

        $existing = $this->getEntityService()->findOneBy(EntityConstants::CUSTOMER, [
            'email' => $event->getFormData('email')
        ]);

        if ($existing) {
            $event->addErrorMessage('Customer Already Registered. Did you forget your password ?');
            return;
        }

        $itemVarSet = $this->getEntityService()->findOneBy(EntityConstants::ITEM_VAR_SET, [
            'object_type' => EntityConstants::CUSTOMER,
        ]);

        if ($itemVarSet) {
            $entity->setItemVarSet($itemVarSet);
        }

        // encode password, handle hash
        if (isset($formData['password']['first']) && $formData['password']['first']) {
            $encoder = $this->getSecurityPasswordEncoder();
            $encoded = $encoder->encodePassword($entity, $formData['password']['first']);
            $entity->setHash($encoded);
        }

        $confirmHash = md5(microtime());
        $entity->setConfirmHash($confirmHash);
        $entity->setCreatedAt(new \DateTime('now'));
        $entity->setDefaultCurrency($this->getCurrencyService()->getBaseCurrency());

        if ($entity->getFirstName()) {
            $entity->setBillingName("{$entity->getFirstName()} {$entity->getLastName()}");
        }

        try {
            $this->getEntityService()->persist($entity);
            $event->setSuccess(true);
            $event->addSuccessMessage("You are Registered");
        } catch(\Exception $e) {
            $event->addErrorMessage('An error occurred while saving the Customer');
            $event->setSuccess(false);
        }
    }
}
