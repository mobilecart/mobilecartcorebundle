<?php

namespace MobileCart\CoreBundle\EventListener\ItemVarSetVar;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ItemVarSetVarList
 * @package MobileCart\CoreBundle\EventListener\ItemVarSetVar
 */
class ItemVarSetVarList
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
    public function onItemVarSetVarList(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $response = '';
        $returnData['mass_actions'] =
        [
            [
                'label'         => 'Delete Variant Maps',
                'input_label'   => 'Confirm Mass-Delete ?',
                'input'         => 'mass_delete',
                'input_type'    => 'select',
                'input_options' => [
                    ['value' => 0, 'label' => 'No'],
                    ['value' => 1, 'label' => 'Yes'],
                ],
                'url' => $this->getRouter()->generate('cart_admin_item_var_set_var_mass_delete'),
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
                'key' => 'item_var_set_name',
                'label' => 'Variant Set',
                'sort' => 1,
            ],
            [
                'key' => 'item_var_name',
                'label' => 'Variant',
                'sort' => 1,
            ],
        ];

        switch($format) {
            case 'json':
                $response = new JsonResponse($returnData);
                break;
            default:

                $response = $this->getThemeService()
                    ->render('admin', 'ItemVarSetVar:index.html.twig', $returnData);

                break;
        }

        $event->setReturnData($returnData);
        $event->setResponse($response);
    }
}
