<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class ProductCreateReturn
 * @package MobileCart\CoreBundle\EventListener\Product
 */
class ProductCreateReturn
{
    protected $router;

    protected $session;

    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function setSession($session)
    {
        $this->session = $session;
        return $this;
    }

    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param CoreEvent $event
     */
    public function onProductCreateReturn(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $entity = $event->getEntity();
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        //$contentType = $request->headers->get('Accept');

        $params = ['id' => $entity->getId()];
        $route = 'cart_admin_product_edit';
        $url = $this->getRouter()->generate($route, $params);

        $response = '';
        switch($format) {
            case 'json':
                $returnData = [
                    'success' => 1,
                    'entity' => $entity->getData(),
                    'redirect_url' => $url,
                ];
                $response = new JsonResponse($returnData);
                break;
            default:

                if ($event->getRequest()->getSession() && $event->getMessages()) {
                    foreach($event->getMessages() as $code => $messages) {
                        if (!$messages) {
                            continue;
                        }
                        foreach($messages as $message) {
                            $event->getRequest()->getSession()->getFlashBag()->add($code, $message);
                        }
                    }
                }

                $response = new RedirectResponse($url);
                break;
        }

        $event->setReturnData($returnData)
            ->setResponse($response);
    }
}
