<?php

namespace MobileCart\CoreBundle\EventListener\ContentSlot;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Form\ContentSlotType;
use MobileCart\CoreBundle\Constants\EntityConstants;

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

    protected $formFactory;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeConfig
     */
    protected $themeConfig;

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

    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    public function getFormFactory()
    {
        return $this->formFactory;
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
     * @param Event $event
     */
    public function onContentSlotAdminForm(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $entity = $event->getEntity();

        $formType = new ContentSlotType();
        $form = $this->getFormFactory()->create($formType, $entity, [
            'action' => $event->getAction(),
            'method' => $event->getMethod(),
        ]);

        $returnData['form_sections'] = [];
        $returnData['form'] = $form;

        $event->setForm($form)
            ->setReturnData($returnData);
    }
}
