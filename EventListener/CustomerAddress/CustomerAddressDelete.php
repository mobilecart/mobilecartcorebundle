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
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
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
        $success = false;

        try {
            $this->getEntityService()->remove($entity);
            $success = true;
            $event->addSuccessMessage('Customer Address Deleted !');
        } catch(\Exception $e) {
            $event->addErrorMessage('Exception occurred during delete');
        }

        if (!$this->getCartService()->getIsAdminUser()) {
            $this->getCartService()->setCustomerEntity($customer);
        }

        $url = $this->getRouter()->generate('customer_addresses', []);
        $event->flashMessages();

        $event->addReturnData([
            'success' => $success,
            'redirect_url' => $url,
        ]);

        if ($event->isJsonResponse()) {
            $event->setResponse(new JsonResponse($event->getReturnData()));
        } else {
            $event->setResponse(new RedirectResponse($url));
        }
    }
}
