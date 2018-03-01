<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ProductViewReturn
 * @package MobileCart\CoreBundle\EventListener\Product
 */
class ProductViewReturn
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
     * @var \MobileCart\CoreBundle\Service\SearchServiceInterface
     */
    protected $search;

    /**
     * @param \MobileCart\CoreBundle\Service\SearchServiceInterface $search
     * @param $objectType
     * @return $this
     */
    public function setSearch(\MobileCart\CoreBundle\Service\SearchServiceInterface $search, $objectType)
    {
        $this->search = $search->setObjectType($objectType);
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\SearchServiceInterface
     */
    public function getSearch()
    {
        return $this->search;
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
    public function onProductViewReturn(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\Product $entity */
        $entity = $event->getEntity();

        $event->flashMessages();

        if ($event->isJsonResponse()) {

            $event->setResponse(new JsonResponse([
                'success' => true,
                'entity' => $entity->getData(),
            ]));

        } else {

            $event->setReturnData('form', $event->getForm()->createView());
            $event->setReturnData('entity', $event->getEntity());
            $event->setReturnData('search', $this->getSearch());

            $configData = @ (array) json_decode($entity->getConfig());
            $event->setReturnData('config_data', $configData);

            $customTpl = $event->getCustomTemplate();
            if (!$customTpl && $event->getEntity()->getCustomTemplate()) {
                $customTpl = $event->getEntity()->getCustomTemplate();
            }

            $template = $customTpl
                ? $customTpl
                : 'Product:view.html.twig';

            $event->setResponse($this->getThemeService()->renderFrontend(
                $template,
                $event->getReturnData()
            ));
        }
    }
}
