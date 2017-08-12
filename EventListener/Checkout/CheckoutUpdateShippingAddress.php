<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CheckoutUpdateShippingAddress
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutUpdateShippingAddress
{

    protected $formFactory;

    /**
     * @var \MobileCart\CoreBundle\Service\CheckoutSessionService
     */
    protected $checkoutSessionService;

    protected $router;

    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * @param $checkoutSession
     * @return $this
     */
    public function setCheckoutSessionService($checkoutSession)
    {
        $this->checkoutSessionService = $checkoutSession;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CheckoutSessionService
     */
    public function getCheckoutSessionService()
    {
        return $this->checkoutSessionService;
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
     * @param $logger
     * @return $this
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param CoreEvent $event
     * @return bool
     */
    public function onCheckoutUpdateShippingAddress(CoreEvent $event)
    {
        if (!$this->getCheckoutSessionService()->getCartSessionService()->getShippingService()->getIsShippingEnabled()) {
            return false;
        }

        $returnData = $event->getReturnData();

        $request = $event->getRequest();
        $formType = $event->getForm();
        $entity = $event->getUser();

        $cartCustomer = $this->getCheckoutSessionService()
            ->getCartSessionService()
            ->getCustomer();

        $customerEntity = $cartCustomer->getId()
            ? $this->getEntityService()->find(EntityConstants::CUSTOMER, $cartCustomer->getId())
            : null;

        $form = $this->getFormFactory()->create($formType, $entity, [
            'action' => $event->getAction(),
            'method' => $event->getMethod(),
            'translation_domain' => 'customer',
        ]);

        $requestData = $request->request->all();

        // check is_shipping_same, copy values
        if (isset($requestData['is_shipping_same']) && $requestData['is_shipping_same']) {
            foreach($requestData as $k => $v) {
                if (substr($k, 0, 8) == 'billing_') {
                    $sk = str_replace('billing_', 'shipping_', $k);
                    if (array_key_exists($sk, $requestData)) {
                        $requestData[$sk] = $v;
                    }
                }
            }
        }

        $sameInfo = true;
        foreach($cartCustomer->getData() as $k => $v) {

            // don't trust user input
            if (in_array($k, ['id', 'email'])) {
                continue;
            }

            if (array_key_exists($k, $requestData) && $requestData[$k] != $v) {
                $sameInfo = false;
            }
        }

        $event->setIsSame($sameInfo);

        $form->submit($requestData);
        $isValid = $form->isValid();

        $messages = [];
        $invalid = [];

        if (!$cartCustomer->getId()) {
            $isValid = false;
            $messages[] = 'You must update your billing information first.';
        }

        $isShippingSame = false;

        if ($isValid) {

            //update customer data in cart session
            $formData = $form->getData();
            foreach($form->all() as $childKey => $child) {

                // blacklist
                if (in_array($childKey, [
                    'id',
                    'item_var_set_id',
                    'hash',
                    'confirm_hash',
                    'item_var_set_id',
                    'failed_logins',
                    'locked_at',
                    'created_at',
                    'last_login_at',
                    'api_key',
                    'is_enabled',
                    'is_expired',
                    'is_locked',
                    'password_updated_at',
                    'is_password_expired',
                    'email',
                    'password',
                ])) {
                    continue;
                }

                $value = $formData->get($childKey);

                switch($childKey) {
                    case 'is_shipping_same':
                        if ($value) {
                            if ($customerEntity) {
                                $cartCustomer->set($childKey, $value);
                                $customerEntity->set($childKey, true);
                            } else {
                                $cartCustomer->set($childKey, $value);
                            }

                            $isShippingSame = true;
                        } else {
                            if ($customerEntity) {
                                $cartCustomer->set($childKey, $value);
                                $customerEntity->set($childKey, false);
                            } else {
                                $cartCustomer->set($childKey, $value);
                            }

                            $isShippingSame = false;
                        }

                        break;
                    default:

                        if (is_null($value)) {
                            continue;
                        }

                        if ($customerEntity) {
                            $cartCustomer->set($childKey, $value);
                            $customerEntity->set($childKey, $value);
                        } else {
                            $cartCustomer->set($childKey, $value);
                        }

                        break;
                }
            }

            // we should have a entity by this point
            if ($customerEntity) {

                if ($isShippingSame) {
                    $customerEntity->copyBillingToShipping();
                }

                try {
                    $this->getEntityService()->persist($customerEntity);
                    if ($customerEntity->getId()) {
                        $this->getCheckoutSessionService()->getCartSessionService()->setCustomerEntity($customerEntity);
                    }
                } catch(\Exception $e) { }
            }

        } else {
            foreach($form->all() as $childKey => $child) {
                $errors = $child->getErrors();
                if ($errors->count()) {
                    $invalid[$childKey] = [];
                    foreach($errors as $error) {
                        $invalid[$childKey][] = $error->getMessage();
                    }
                }
            }
        }

        $this->getCheckoutSessionService()->setIsValidShippingAddress($isValid);

        if (!$event->getIsSame() && $isValid) {
            // only updating the main address
            $this->getCheckoutSessionService()->getCartSessionService()->collectShippingMethods('main');
        }

        $returnData['success'] = $isValid;
        $returnData['messages'] = $messages;
        $returnData['invalid'] = $invalid;

        $cartService = $this->getCheckoutSessionService()->getCartSessionService()->getCartService();
        if ($isValid && !$cartService->getIsSpaEnabled()) {
            $returnData['redirect_url'] = $this->getRouter()->generate('cart_checkout_totals_discounts', []);
        }

        $response = new JsonResponse($returnData);

        $event->setReturnData($returnData)
            ->setResponse($response);
    }
}
