<?php

namespace MobileCart\CoreBundle\EventListener\Content;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ContentList
 * @package MobileCart\CoreBundle\EventListener\Content
 */
class ContentList
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

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
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @return $this
     */
    public function setRouter(\Symfony\Component\Routing\RouterInterface $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param CoreEvent $event
     */
    public function onContentList(CoreEvent $event)
    {
        $massActions = [
            [
                'label'         => 'Delete Contents',
                'input_label'   => 'Confirm Mass-Delete ?',
                'input'         => 'mass_delete',
                'input_type'    => 'select',
                'input_options' => [
                    ['value' => 0, 'label' => 'No'],
                    ['value' => 1, 'label' => 'Yes'],
                ],
                'url' => $this->getRouter()->generate('cart_admin_content_mass_delete'),
                'external' => 0,
            ],
        ];

        $columns = [
            [
                'key' => 'id',
                'label' => 'ID',
                'sort' => true,
            ],
            [
                'key' => 'name',
                'label' => 'Name',
                'sort' => true,
            ],
            [
                'key' => 'slug',
                'label' => 'URL Slug',
                'sort' => true,
            ],
        ];

        if ($event->isJsonResponse()) {
            $event->setResponse(new JsonResponse($event->getReturnData()));
        } else {

            if ($event->getSection() == CoreEvent::SECTION_BACKEND) {

                $event->setReturnData('mass_actions', $massActions);
                $event->setReturnData('columns', $columns);

                $event->setResponse($this->getThemeService()->renderAdmin(
                    'Content:index.html.twig',
                    $event->getReturnData()
                ));

            } else {

                $event->setResponse($this->getThemeService()->renderFrontend(
                    'Content:index.html.twig',
                    $event->getReturnData()
                ));

            }
        }
    }
}
