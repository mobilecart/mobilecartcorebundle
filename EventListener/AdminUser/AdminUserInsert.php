<?php

namespace MobileCart\CoreBundle\EventListener\AdminUser;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class AdminUserInsert
 * @package MobileCart\CoreBundle\EventListener\AdminUser
 */
class AdminUserInsert
{
    /**
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    protected $entityService;

    /**
     * @var \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface
     */
    protected $securityPasswordEncoder;

    /**
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $encoder
     * @return $this
     */
    public function setSecurityPasswordEncoder(\Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $encoder)
    {
        $this->securityPasswordEncoder = $encoder;
        return $this;
    }

    /**
     * @return \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface
     */
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
    public function onAdminUserInsert(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\AdminUser $entity */
        $entity = $event->getEntity();
        $formData = $event->getFormData();

        // encode password, handle hash
        if (isset($formData['password']['first']) && $formData['password']['first']) {
            $encoder = $this->getSecurityPasswordEncoder();
            $encoded = $encoder->encodePassword($entity, $formData['password']['first']);
            $entity->setHash($encoded);
        }

        try {
            $this->getEntityService()->persist($entity);
            $event->setSuccess(true);
            $event->addSuccessMessage('Admin User Created !');
        } catch(\Exception $e) {
            $event->addErrorMessage('An error occurred while saving Admin User');
        }
    }
}
