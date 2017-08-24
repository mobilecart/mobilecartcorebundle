<?php

namespace MobileCart\CoreBundle\EventListener\Content;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class ContentEditReturn
 * @package MobileCart\CoreBundle\EventListener\Content
 */
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
     * @param CoreEvent $event
     */
    public function onContentEditReturn(CoreEvent $event)
    {
        $entity = $event->getEntity();
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

        $tplSections = [];
        $tplSections['slots'] = [
            'section_id' => 'slots',
            'label' => 'Sections',
            'template'     => $this->getThemeService()->getTemplatePath('admin') . 'Content:slots.html.twig',
            'js_template'  => $this->getThemeService()->getTemplatePath('admin') . 'Content:slots_js.html.twig',
            'slots' => $slots,
            'upload_query' => '',
        ];

        $tplSections['images'] = [
            'section_id'   => 'images',
            'label'        => 'Images',
            'template'     => $this->getThemeService()->getTemplatePath('admin') . 'Widgets/Image:uploader.html.twig',
            'js_template'  => $this->getThemeService()->getTemplatePath('admin') . 'Widgets/Image:uploader_js.html.twig',
            'images'       => $entity->getImages(),
            'image_sizes'  => $this->getImageService()->getImageConfigs($objectType),
            'upload_query' => "?object_type={$objectType}&object_id={$entity->getId()}",
        ];

        $event->setReturnData('entity', $entity);
        $event->setReturnData('form', $event->getReturnData('form')->createView());
        $event->setReturnData('template_sections', $tplSections);

        $event->setResponse($this->getThemeService()->render('admin', 'Content:edit.html.twig', $event->getReturnData()));
    }
}
