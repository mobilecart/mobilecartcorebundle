<?php

namespace MobileCart\CoreBundle\EventListener\OrderShipment;

use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class OrderShipmentList
 * @package MobileCart\CoreBundle\EventListener\OrderShipment
 */
class OrderShipmentList
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
    public function onOrderShipmentList(CoreEvent $event)
    {
        $event->setReturnData('mass_actions', []);

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
                'key' => 'company_name',
                'label' => 'Company',
                'sort' => true,
            ],
            [
                'key' => 'company',
                'label' => 'Provider',
                'sort' => true,
            ],
            [
                'key' => 'method',
                'label' => 'Method',
                'sort' => true,
            ],
            [
                'key' => 'created_at',
                'label' => 'Created At',
                'sort' => true,
            ],
        ]);

        switch($event->getRequestAccept()) {
            case CoreEvent::JSON:
                $event->setResponse(new JsonResponse($event->getReturnData()));
                break;
            default:
                $event->setResponse($this->getThemeService()->renderAdmin(
                    'OrderShipment:index.html.twig',
                    $event->getReturnData()
                ));
                break;
        }
    }
}
