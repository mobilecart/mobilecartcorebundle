<?php

namespace MobileCart\CoreBundle\EventListener\Category;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CategoryNewReturn
 * @package MobileCart\CoreBundle\EventListener\Category
 */
class CategoryNewReturn
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
     * @param CoreEvent $event
     */
    public function onCategoryNewReturn(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $objectType = EntityConstants::CATEGORY;

        $tplSections = [];
        $tplSections['images'] = [
            'section_id'   => 'images',
            'label'        => 'Images',
            'template'     => $this->getThemeService()->getTemplatePath('admin') . 'Widgets/Image:uploader.html.twig',
            'js_template'  => $this->getThemeService()->getTemplatePath('admin') . 'Widgets/Image:uploader_js.html.twig',
            'images'       => [],
            'image_sizes'  => $this->getImageService()->getImageConfigs($objectType),
            'upload_query' => "?object_type={$objectType}",
        ];

        $event->setReturnData('entity', $entity);
        $event->setReturnData('form', $event->getReturnData('form')->createView());
        $event->setReturnData('template_sections', $tplSections);

        $event->setResponse($this->getThemeService()->render('admin', 'Category:new.html.twig', $event->getReturnData()));
    }
}
