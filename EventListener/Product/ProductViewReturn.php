<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Entity\Product;

class ProductViewReturn
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

    public function onProductViewReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $product = $event->getEntity();

        $request = $event->getRequest();
        $config = @ (array) json_decode($product->getConfig());
        $typeSections = [];
        $objectType = EntityConstants::PRODUCT;
        $format = $request->get('format', '');

        $response = '';
        switch($format) {
            case 'json':

                $returnData = $product->getData();
                $response = new JsonResponse($returnData);

                break;
            default:

                $template = $event->getEntity()->getCustomTemplate()
                    ? $event->getEntity()->getCustomTemplate()
                    : 'Product:view.html.twig';

                $response = $this->getThemeService()
                    ->render('frontend', $template, $returnData);

                break;
        }

        $event->setReturnData($returnData)
            ->setResponse($response);
    }
}
