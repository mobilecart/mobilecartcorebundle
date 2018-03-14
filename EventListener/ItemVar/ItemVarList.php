<?php

namespace MobileCart\CoreBundle\EventListener\ItemVar;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ItemVarList
 * @package MobileCart\CoreBundle\EventListener\ItemVar
 */
class ItemVarList
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
    public function onItemVarList(CoreEvent $event)
    {
        $event->setReturnData('mass_actions', [
            [
                'label'         => 'Delete Custom Fields',
                'input_label'   => 'Confirm Mass-Delete ?',
                'input'         => 'mass_delete',
                'input_type'    => 'select',
                'input_options' => [
                    ['value' => 0, 'label' => 'No'],
                    ['value' => 1, 'label' => 'Yes'],
                ],
                'url' => $this->getRouter()->generate('cart_admin_item_var_mass_delete'),
                'external' => 0,
            ],
        ]);

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
                [
                    'key' => 'code',
                    'label' => 'Code',
                    'sort' => true,
                ],
                [
                    'key' => 'form_input',
                    'label' => 'Form Input',
                    'sort' => true,
                ],
                [
                    'key' => 'action',
                    'label' => 'Actions',
                    'sort' => false,
                ],
            ]);
        }

        if ($event->isJsonResponse()) {

            $event->setResponse(new JsonResponse($event->getReturnData()));

        } else {

            $result = $event->getReturnData('result');
            if ($result && $result['entities']) {
                foreach($result['entities'] as $idx => $data) {
                    if (in_array($data['form_input'], ['select', 'multiselect'])) {
                        $result['entities'][$idx]['action'] = $this->getRouter()->generate('cart_admin_item_var_option', ['item_var_id' => $data['id']]);
                    } else {
                        $result['entities'][$idx]['action'] = '';
                    }
                }
                $event->setReturnData('result', $result);
            }

            $event->setResponse($this->getThemeService()->renderAdmin(
                'ItemVar:index.html.twig',
                $event->getReturnData()
            ));
        }
    }
}
