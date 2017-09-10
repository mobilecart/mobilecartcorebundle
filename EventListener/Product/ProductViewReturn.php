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
    public function onProductViewReturn(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');

        switch($format) {
            case 'json':
                $event->setResponse(new JsonResponse([
                    'entity' => $entity->getData(),
                ]));
                break;
            default:

                $event->setReturnData('form', $event->getReturnData('form')->createView());
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

                $event->setResponse($this->getThemeService()->render(
                    'frontend',
                    $template,
                    $event->getReturnData()
                ));
                break;
        }
    }
}
