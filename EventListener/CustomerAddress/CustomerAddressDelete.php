<?php

namespace MobileCart\CoreBundle\EventListener\CustomerAddress;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class CustomerAddressDelete
 * @package MobileCart\CoreBundle\EventListener\CustomerAddress
 */
class CustomerAddressDelete
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

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
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
    }

    /**
     * @param $cartService
     * @return $this
     */
    public function setCartService($cartService)
    {
        $this->cartService = $cartService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->cartService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCustomerAddressDelete(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\CustomerAddress $entity */
        $entity = $event->getEntity();
        $customer = $entity->getCustomer();
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $success = false;

        try {
            $this->getEntityService()->remove($entity);
            $success = true;
            $event->addSuccessMessage('Customer Address Deleted!');
        } catch(\Exception $e) {
            $event->addErrorMessage('Exception occurred during delete');
        }

        if (!$this->getCartService()->getIsAdminUser()) {
            $this->getCartService()->setCustomerEntity($customer);
        }

        $url = $this->getRouter()->generate('customer_addresses', []);

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

        $event->addReturnData([
            'success' => $success,
            'entity' => $entity->getData(),
            'redirect_url' => $url,
            'messages' => $event->getMessages(),
        ]);

        switch($format) {
            case 'json':
                $event->setResponse(new JsonResponse($event->getReturnData()));
                break;
            default:
                $event->setResponse(new RedirectResponse($url));
                break;
        }
    }
}
