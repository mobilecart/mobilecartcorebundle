<?php

namespace MobileCart\CoreBundle\EventListener\Category;

use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Component\EventDispatcher\Event;

class CategoryEditReturn
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
    public function onCategoryEditReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $entity = $event->getEntity();

        $objectType = EntityConstants::CATEGORY;

        $tplSections = [];
        $tplSections['images'] = [
            'section_id'   => 'images',
            'label'        => 'Images',
            'template'     => $this->getThemeService()->getTemplatePath('admin') . 'Widgets/Image:uploader.html.twig',
            'js_template'  => $this->getThemeService()->getTemplatePath('admin') . 'Widgets/Image:uploader_js.html.twig',
            'images'       => $entity->getImages(),
            'image_sizes'  => $this->getImageService()->getImageConfigs($objectType),
            'upload_query' => "?object_type={$objectType}&object_id={$entity->getId()}",
        ];

        $returnData['template_sections'] = $tplSections;

        $form = $returnData['form'];
        $returnData['form'] = $form->createView();
        $returnData['entity'] = $entity;

        $response = $this->getThemeService()
            ->render('admin', 'Category:edit.html.twig', $returnData);

        $event->setReturnData($returnData);
        $event->setResponse($response);
    }
}
