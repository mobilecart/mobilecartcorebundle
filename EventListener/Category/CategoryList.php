<?php

namespace MobileCart\CoreBundle\EventListener\Category;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

class CategoryList
{

    protected $router;

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

    public function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
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

    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function onCategoryList(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $request = $event->getRequest();

        $format = $request->get('format', '');

        $response = '';

        $returnData['columns'] = [
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

        switch($event->getSection()) {
            case ($format == 'json'):
            case CoreEvent::SECTION_API:

                $response = new JsonResponse($returnData);

                break;
            case CoreEvent::SECTION_BACKEND:

                $returnData['mass_actions'] = [
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

                $response = $this->getThemeService()
                    ->render('admin', 'Category:index.html.twig', $returnData);

                break;
            case CoreEvent::SECTION_FRONTEND:

                $response = $this->getThemeService()
                    ->render('frontend', 'Category:index.html.twig', $returnData);

                break;
            default:

                break;
        }

        $event->setReturnData($returnData);
        $event->setResponse($response);
    }
}
