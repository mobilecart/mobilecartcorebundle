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
     * @param \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     * @return $this
     */
    public function setEntityService(\MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface $entityService)
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
        $event->flashMessages();

        $event->setReturnData('entity', $event->getEntity());

        if ($event->isJsonResponse()) {

            $event->setResponse(new JsonResponse([
                'success' => $event->getSuccess(),
                'entity' => $event->getEntity()->getData(),
                'messages' => $event->getMessages(),
            ]));

        } else {

            $customTpl = $event->getCustomTemplate();
            if (!$customTpl && $event->getEntity()->getCustomTemplate()) {
                $customTpl = $event->getEntity()->getCustomTemplate();
            }

            $template = $customTpl
                ? $customTpl
                : 'Content:view.html.twig';

            $event->setResponse($this->getThemeService()->renderFrontend(
                $template,
                $event->getReturnData()
            ));
        }
    }
}
