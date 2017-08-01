<?php

namespace MobileCart\CoreBundle\EventListener\Content;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ContentViewReturn
 * @package MobileCart\CoreBundle\EventListener\Content
 */
class ContentViewReturn
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
     * @return mixed
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
    public function onContentViewReturn(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $entity = $event->getEntity();

        $request = $event->getRequest();
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

                $returnData['entity'] = $entity;

                $customTpl = $event->getCustomTemplate();
                if (!$customTpl && $event->getEntity()->getCustomTemplate()) {
                    $customTpl = $event->getEntity()->getCustomTemplate();
                }

                $template = $customTpl
                    ? $customTpl
                    : 'Content:view.html.twig';

                $response = $this->getThemeService()
                    ->render('frontend', $template, $returnData);

                break;
        }

        $event->setReturnData($returnData)
            ->setResponse($response);
    }
}
