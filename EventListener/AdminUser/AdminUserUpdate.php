<?php

namespace MobileCart\CoreBundle\EventListener\AdminUser;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class AdminUserUpdate
 * @package MobileCart\CoreBundle\EventListener\AdminUser
 */
class AdminUserUpdate
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
    public function onAdminUserUpdate(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\AdminUser $entity */
        $entity = $event->getEntity();
        $formData = $event->getFormData();

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
            $event->addSuccessMessage('Admin User Updated!');
        } catch(\Exception $e) {
            $event->addErrorMessage('An error occurred while saving Admin User');
        }
    }
}
