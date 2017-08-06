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
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CartSessionService
     */
    protected $cartSessionService;

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
     * @param $entityService
     * @return $this
     */
    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param $cartSessionService
     * @return $this
     */
    public function setCartSessionService($cartSessionService)
    {
        $this->cartSessionService = $cartSessionService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartSessionService
     */
    public function getCartSessionService()
    {
        return $this->cartSessionService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCustomerAddressDelete(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');

        $this->getEntityService()->remove($entity, EntityConstants::CUSTOMER);

        if ($event->getSection() == CoreEvent::SECTION_FRONTEND) {

            // update session info
            $this->getCartSessionService()
                ->setCustomerEntity($event->getCustomer());
        }

        $url = $this->getRouter()->generate('customer_addresses', []);

        $event->addSuccessMessage('Customer Address Deleted!');

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

        switch($format) {
            case 'json':
                $event->setResponse(new JsonResponse([
                    'success' => true,
                    'entity' => $entity->getData(),
                    'redirect_url' => $url,
                    'messages' => $event->getMessages(),
                ]));
                break;
            default:
                $event->setResponse(new RedirectResponse($url));
                break;
        }
    }
}
