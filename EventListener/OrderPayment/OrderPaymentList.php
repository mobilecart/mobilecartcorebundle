<?php

namespace MobileCart\CoreBundle\EventListener\OrderPayment;

use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class OrderPaymentList
 * @package MobileCart\CoreBundle\EventListener\OrderPayment
 */
class OrderPaymentList
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
    public function onOrderPaymentList(CoreEvent $event)
    {
        $event->setReturnData('mass_actions', []);

        // allow a previous listener to define the columns
        if (!$event->getReturnData('columns', [])) {

            $event->setReturnData('columns', [
                [
                    'key' => 'id',
                    'label' => 'ID',
                    'sort' => true,
                ],
                [
                    'key' => 'label',
                    'label' => 'Service',
                    'sort' => true,
                ],
                [
                    'key' => 'base_amount',
                    'label' => 'Amount',
                    'sort' => true,
                ],
                [
                    'key' => 'created_at',
                    'label' => 'Created At',
                    'sort' => true,
                ],
            ]);
        }

        if ($event->isJsonResponse()) {

            $event->setResponse(new JsonResponse($event->getReturnData()));

        } else {

            $event->setResponse($this->getThemeService()->renderAdmin(
                'OrderPayment:index.html.twig',
                $event->getReturnData()
            ));
        }
    }
}
