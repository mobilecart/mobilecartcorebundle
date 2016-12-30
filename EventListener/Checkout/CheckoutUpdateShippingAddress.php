<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

class CheckoutUpdateShippingAddress
{
    protected $event;

    protected $formFactory;

    protected $checkoutSessionService;

    protected $router;

    protected $entityService;

    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    public function getFormFactory()
    {
        return $this->formFactory;
    }

    public function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function setCheckoutSessionService($checkoutSession)
    {
        $this->checkoutSessionService = $checkoutSession;
        return $this;
    }

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

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

    public function onCheckoutUpdateShippingAddress(Event $event)
    {
        if (!$this->getCheckoutSessionService()->getCartSessionService()->getShippingService()->getIsShippingEnabled()) {
            return false;
        }

        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $request = $event->getRequest();
        $formType = $event->getForm();
        $entity = $event->getUser(); // we should always have a user here
            //? $event->getUser()
            //: $event->getEntity();

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
                    $this->getCheckoutSessionService()->getCartSessionService()->setCustomerEntity($customerEntity);
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
