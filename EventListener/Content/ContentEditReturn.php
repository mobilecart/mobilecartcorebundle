<?php

namespace MobileCart\CoreBundle\EventListener\Content;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Constants\EntityConstants;

class ContentEditReturn
{
    protected $entityService;

    protected $imageService;

    protected $themeService;

    protected $event;

    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    protected function getEvent()
    {
        return $this->event;
    }

    protected function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

    public function setImageService($imageService)
    {
        $this->imageService = $imageService;
        return $this;
    }

    public function getImageService()
    {
        return $this->imageService;
    }

    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    public function getThemeService()
    {
        return $this->themeService;
    }

    public function onContentEditReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

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
            'template'     => $this->getThemeService()->getTemplatePath('admin') . 'Widgets/Content:slots.html.twig',
            'js_template'  => $this->getThemeService()->getTemplatePath('admin') . 'Widgets/Content:slots_js.html.twig',
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
