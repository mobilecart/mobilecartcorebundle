<?php

namespace MobileCart\CoreBundle\EventListener\UrlRewrite;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Form\UrlRewriteType;

/**
 * Class UrlRewriteAdminForm
 * @package MobileCart\CoreBundle\EventListener\UrlRewrite
 */
class UrlRewriteAdminForm
{
    /**
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CurrencyService
     */
    protected $currencyService;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var string
     */
    protected $formTypeClass = 'MobileCart\CoreBundle\Form\UrlRewriteType';

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
     * @param $currencyService
     * @return $this
     */
    public function setCurrencyService($currencyService)
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
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @return $this
     */
    public function setFormFactory(\Symfony\Component\Form\FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    /**
     * @return \Symfony\Component\Form\FormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * @param string $formTypeClass
     * @return $this
     */
    public function setFormTypeClass($formTypeClass)
    {
        $this->formTypeClass = $formTypeClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormTypeClass()
    {
        return $this->formTypeClass;
    }

    /**
     * @param CoreEvent $event
     */
    public function onUrlRewriteAdminForm(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\UrlRewrite $entity */
        $entity = $event->getEntity();

        $event->setReturnData('form_sections', [
            'general' => [
                'label' => 'General',
                'id' => 'general',
                'fields' => [
                    'object_type',
                    'object_action',
                    'request_uri',
                    'params_json',
                    'is_redirect',
                    'redirect_url',
                ],
            ],
        ]);

        $event->setForm($this->getFormFactory()->create($this->getFormTypeClass(), $entity, [
            'action' => $event->getFormAction(),
            'method' => $event->getFormMethod(),
        ]));
    }
}
