<?php

namespace MobileCart\CoreBundle\EventListener\ContentSlot;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Form\ContentSlotType;

/**
 * Class ContentSlotAdminForm
 * @package MobileCart\CoreBundle\EventListener\ContentSlot
 */
class ContentSlotAdminForm
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var string
     */
    protected $formTypeClass = '';

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeConfig
     */
    protected $themeConfig;

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
     * @param $themeConfig
     * @return $this
     */
    public function setThemeConfig($themeConfig)
    {
        $this->themeConfig = $themeConfig;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ThemeConfig
     */
    public function getThemeConfig()
    {
        return $this->themeConfig;
    }

    /**
     * @param CoreEvent $event
     */
    public function onContentSlotAdminForm(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $form = $this->getFormFactory()->create($this->getFormTypeClass(), $entity, [
            'action' => $event->getFormAction(),
            'method' => $event->getFormMethod(),
        ]);

        $event->setForm($form);
        $event->setReturnData('form_sections', []);
    }
}
