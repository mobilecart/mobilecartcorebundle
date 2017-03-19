<?php

namespace MobileCart\CoreBundle\EventListener\ItemVarOption;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ItemVarOptionList
 * @package MobileCart\CoreBundle\EventListener\ItemVarOption
 */
class ItemVarOptionList
{

    protected $router;

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

    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param Event $event
     */
    public function onItemVarOptionList(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $response = '';
        $returnData['mass_actions'] =
        [
            [
                'label'         => 'Delete ItemVarOptions',
                'input_label'   => 'Confirm Mass-Delete ?',
                'input'         => 'mass_delete',
                'input_type'    => 'select',
                'input_options' => [
                    ['value' => 0, 'label' => 'No'],
                    ['value' => 1, 'label' => 'Yes'],
                ],
                'url' => $this->router->generate('cart_admin_item_var_option_mass_delete'),
                'external' => 0,
            ],
        ];

        $returnData['columns'] =
        [
            [
                'key' => 'id',
                'label' => 'ID',
                'sort' => 1,
            ],
            [
                'key' => 'item_var_name',
                'label' => 'Custom Field',
                'sort' => 1,
            ],
            [
                'key' => 'value',
                'label' => 'Value',
                'sort' => 1,
            ],
            [
                'key' => 'is_in_stock',
                'label' => 'Is In Stock',
                'sort' => 1,
            ],
        ];

        switch($format) {
            case 'json':
                $response = new JsonResponse($returnData);
                break;
            //case 'xml':
            //
            //    break;
            default:

                $response = $this->getThemeService()
                    ->render('admin', 'ItemVarOption:index.html.twig', $returnData);

                break;
        }

        $event->setReturnData($returnData);
        $event->setResponse($response);
    }
}
