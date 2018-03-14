<?php

namespace MobileCart\CoreBundle\EventListener\Category;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CategoryList
 * @package MobileCart\CoreBundle\EventListener\Category
 */
class CategoryList
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
    public function onCategoryList(CoreEvent $event)
    {
        // allow a previous listener to define the columns
        if (!$event->getReturnData('columns', [])) {

            $event->setReturnData('columns', [
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
            ]);
        }

        if ($event->isJsonResponse()) {

            $event->setResponse(new JsonResponse($event->getReturnData()));

        } else {

            if ($event->isBackendSection()) {

                $event->setReturnData('mass_actions', [
                    [
                        'label'         => 'Delete Categories',
                        'input_label'   => 'Confirm Mass-Delete ?',
                        'input'         => 'mass_delete',
                        'input_type'    => 'select',
                        'input_options' => [
                            ['value' => 0, 'label' => 'No'],
                            ['value' => 1, 'label' => 'Yes'],
                        ],
                        'url' => $this->router->generate('cart_admin_category_mass_delete'),
                        'external' => 0,
                    ],
                ]);

                $template = $event->getCustomTemplate()
                    ? $event->getCustomTemplate()
                    : 'Category:index.html.twig';

                $event->setResponse($this->getThemeService()->renderAdmin(
                    $template,
                    $event->getReturnData()
                ));

            } else {

                $template = $event->getCustomTemplate()
                    ? $event->getCustomTemplate()
                    : 'Category:index.html.twig';

                $event->setResponse($this->getThemeService()->renderFrontend(
                    $template,
                    $event->getReturnData()
                ));
            }
        }
    }
}
