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
        $columns = [
            [
                'key' => 'id',
                'label' => 'ID',
                'sort' => 1,
            ],
            [
                'key' => 'name',
                'label' => 'Name',
                'sort' => 1,
            ],
        ];

        $search = $event->getReturnData('search');
        if ($search) {
            $sortBy = $search->getSortBy();
            $sortDir = $search->getSortDir();
            if ($sortBy) {
                foreach($columns as $k => $colData) {
                    if ($colData['key'] == $sortBy) {
                        $columns[$k]['isActive'] = 1;
                        $columns[$k]['direction'] = $sortDir;
                        break;
                    }
                }
            }
        }

        $event->setReturnData('columns', $columns);

        switch($event->getRequestAccept()) {
            case CoreEvent::JSON:
                $event->setResponse(new JsonResponse($event->getReturnData()));
                break;
            default:

                switch($event->getSection()) {
                    case CoreEvent::SECTION_BACKEND:

                        $massActions = [
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
                        ];

                        $event->setReturnData('mass_actions', $massActions);

                        $template = $event->getCustomTemplate()
                            ? $event->getCustomTemplate()
                            : 'Category:index.html.twig';

                        $event->setResponse($this->getThemeService()->render('admin', $template, $event->getReturnData()));

                        break;
                    case CoreEvent::SECTION_FRONTEND:

                        $template = $event->getCustomTemplate()
                            ? $event->getCustomTemplate()
                            : 'Category:index.html.twig';

                        $event->setResponse($this->getThemeService()->render('frontend', $template, $event->getReturnData()));

                        break;
                    default:

                        $template = $event->getCustomTemplate()
                            ? $event->getCustomTemplate()
                            : 'Category:index.html.twig';

                        $event->setResponse($this->getThemeService()->render('frontend', $template, $event->getReturnData()));

                        break;
                }

                break;
        }
    }
}
