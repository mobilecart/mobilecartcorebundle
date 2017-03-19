<?php

namespace MobileCart\CoreBundle\EventListener\Content;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Constants\EntityConstants;

class ContentEditReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\ImageService
     */
    protected $imageService;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

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

    /**
     * @param $imageService
     * @return $this
     */
    public function setImageService($imageService)
    {
        $this->imageService = $imageService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ImageService
     */
    public function getImageService()
    {
        return $this->imageService;
    }

    /**
     * @param $themeService
     * @return $this
     */
    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ThemeService
     */
    public function getThemeService()
    {
        return $this->themeService;
    }

    /**
     * @param Event $event
     */
    public function onContentEditReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $entity = $event->getEntity();

        $typeSections = [];

        $objectType = EntityConstants::CONTENT;

        $slots = $entity->getSlots();
        $sortOrders = [];
        if ($slots) {
            foreach($slots as $i => $slot) {
                $sortOrder = $slot->getSortOrder();
                if (!$sortOrder) {
                    $sortOrder = 1;
                }
                $sortOrders[$i] = $sortOrder;
            }

            asort($sortOrders);

            $newSlots = [];
            foreach($sortOrders as $i => $sortOrder) {
                $newSlots[] = $slots[$i];
            }

            $slots = $newSlots;
        }

        $typeSections['slots'] = [
            'section_id' => 'slots',
            'label' => 'Sections',
            'template'     => $this->getThemeService()->getTemplatePath('admin') . 'Content:slots.html.twig',
            'js_template'  => $this->getThemeService()->getTemplatePath('admin') . 'Content:slots_js.html.twig',
            'slots' => $slots,
            'upload_query' => '',
        ];

        $typeSections['images'] = [
            'section_id'   => 'images',
            'label'        => 'Images',
            'template'     => $this->getThemeService()->getTemplatePath('admin') . 'Widgets/Image:uploader.html.twig',
            'js_template'  => $this->getThemeService()->getTemplatePath('admin') . 'Widgets/Image:uploader_js.html.twig',
            'images'       => $entity->getImages(),
            'image_sizes'  => $this->getImageService()->getImageConfigs($objectType),
            'upload_query' => "?object_type={$objectType}&object_id={$entity->getId()}",
        ];

        $returnData['template_sections'] = $typeSections;

        $form = $returnData['form'];
        $returnData['form'] = $form->createView();
        $returnData['entity'] = $entity;

        $response = $this->getThemeService()
            ->render('admin', 'Content:edit.html.twig', $returnData);

        $event->setReturnData($returnData);
        $event->setResponse($response);
    }
}
