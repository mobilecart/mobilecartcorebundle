<?php

namespace MobileCart\CoreBundle\EventListener\Content;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ContentNewReturn
 * @package MobileCart\CoreBundle\EventListener\Content
 */
class ContentNewReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
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
    public function onContentNewReturn(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $objectType = $event->getObjectType();

        $tplSections = [];

        $tplSections['slots'] = [
            'section_id' => 'slots',
            'label' => 'Sections',
            'template'     => $this->getThemeService()->getTemplatePath('admin') . 'Content:slots.html.twig',
            'js_template'  => $this->getThemeService()->getTemplatePath('admin') . 'Content:slots_js.html.twig',
            'slots' => [],
            'upload_query' => '',
        ];

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
        $event->setReturnData('form', $event->getForm()->createView());
        $event->setReturnData('template_sections', $tplSections);

        $event->setResponse($this->getThemeService()->render('admin', 'Content:new.html.twig', $event->getReturnData()));
    }
}
