<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Entity\Product;

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
    public function onProductViewReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $entity = $event->getEntity();
        $form = $event->getForm();
        $returnData['entity'] = $entity;

        $request = $event->getRequest();

        $typeSections = [];
        $objectType = EntityConstants::PRODUCT;
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');

        $response = '';
        switch($format) {
            case 'json':

                $returnData = [
                    'entity' => $entity->getData(),
                ];
                $response = new JsonResponse($returnData);
                break;
            default:

                $returnData['config_data'] = @ (array) json_decode($entity->getConfig());
                $returnData['form'] = $form->createView();
                $returnData['search'] = $event->getSearch();

                $customTpl = $event->getCustomTemplate();
                if (!$customTpl && $event->getEntity()->getCustomTemplate()) {
                    $customTpl = $event->getEntity()->getCustomTemplate();
                }

                $template = $customTpl
                    ? $customTpl
                    : 'Product:view.html.twig';

                $response = $this->getThemeService()
                    ->render('frontend', $template, $returnData);

                break;
        }

        $event->setReturnData($returnData)
            ->setResponse($response);
    }
}
