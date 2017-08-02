<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CustomerList
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerList
{

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
     * @param CoreEvent $event
     */
    public function onCustomerList(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');

        $returnData['mass_actions'] =
        [
            [
                'label'         => 'Delete Customers',
                'input_label'   => 'Confirm Mass-Delete ?',
                'input'         => 'mass_delete',
                'input_type'    => 'select',
                'input_options' => [
                    ['value' => 0, 'label' => 'No'],
                    ['value' => 1, 'label' => 'Yes'],
                ],
                'url'      => $this->getRouter()->generate('cart_admin_customer_mass_delete'),
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
                'key' => 'first_name',
                'label' => 'First Name',
                'sort' => 1,
            ],
            [
                'key' => 'last_name',
                'label' => 'Last Name',
                'sort' => 1,
            ],
            [
                'key' => 'email',
                'label' => 'Email',
                'sort' => 1,
            ],
            [
                'key' => 'created_at',
                'label' => 'Created At',
                'sort' => 1,
            ],
        ];

        if (isset($returnData['search'])) {
            $search = $returnData['search'];
            $sortBy = $search->getSortBy();
            $sortDir = $search->getSortDir();
            if ($sortBy) {
                foreach($returnData['columns'] as $k => $colData) {
                    if ($colData['key'] == $sortBy) {
                        $returnData['columns'][$k]['isActive'] = 1;
                        $returnData['columns'][$k]['direction'] = $sortDir;
                        break;
                    }
                }
            }
        }

        $response = '';
        switch($format) {
            case 'json':
                $response = new JsonResponse($returnData);
                break;
            default:

                if ($codeMessages = $event->getMessages()) {
                    foreach($codeMessages as $code => $messages) {
                        if (!$messages) {
                            continue;
                        }
                        foreach($messages as $message) {
                            $event->getRequest()->getSession()->getFlashBag()->add($code, $message);
                        }
                    }
                }

                $response = $this->getThemeService()
                    ->render('admin', 'Customer:index.html.twig', $returnData);

                break;
        }

        $event->setReturnData($returnData)
            ->setResponse($response);
    }
}
