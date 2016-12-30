<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

class CheckoutUpdateBillingAddress
{
    protected $event;

    protected $formFactory;

    protected $checkoutSessionService;

    protected $passwordEncoder;

    protected $entityService;

    protected $router;

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

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

    public function setSecurityPasswordEncoder($passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        return $this;
    }

    public function getSecurityPasswordEncoder()
    {
        return $this->passwordEncoder;
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

    public function onCheckoutUpdateBillingAddress(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $request = $event->getRequest();
        $formType = $event->getForm();
        $entity = $event->getEntity();

        $cart = $this->getCheckoutSessionService()
            ->getCartSessionService()
            ->getCart();

        $cartCustomer = $cart->getCustomer();

        $form = $this->getFormFactory()->create($formType, $entity, [
            'action' => $event->getAction(),
            'method' => $event->getMethod(),
            'translation_domain' => 'checkout',
            'validation_groups' => 'billing_address',
        ]);

        $requestData = $request->request->all();
        $form->submit($requestData);
        $isValid = $form->isValid();

        if (isset($requestData['email']) && !$cartCustomer->getId()) {
            $customerEntity = $this->getEntityService()->findOneBy(EntityConstants::CUSTOMER, [
                'email' => $requestData['email'],
            ]);

            if ($customerEntity) {
                $isValid = false;
                $invalid['email'] = ['Email exists. Please login before checkout'];
            }
        }

        $messages = [];
        $invalid = [];

        if ($isValid) {

            $customerEntity = $cartCustomer->getId()
                ? $this->getEntityService()->find(EntityConstants::CUSTOMER, $cartCustomer->getId())
                : $this->getEntityService()->getInstance(EntityConstants::CUSTOMER);

            $formData = $form->getData();
            foreach($form->all() as $childKey => $child) {

                // security concerns
                //  blacklist security-related fields
                //  and other fields which should not be updated here

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
                ])) {
                    continue;
                }

                $value = $formData->get($childKey);
                switch($childKey) {
                    case 'password':
                        if (!$customerEntity->getId()
                            && $value
                            && strlen($value) >= 6
                        ) {
                            $encoder = $this->getSecurityPasswordEncoder();
                            $encoded = $encoder->encodePassword($customerEntity, $value);
                            $customerEntity->setHash($encoded);
                            // hash should never be stored in session
                        }
                        break;
                    case 'billing_name':

                        if (is_null($value)) {
                            continue;
                        }

                        $parts = explode(' ', $value);
                        $count = count($parts);
                        $firstName = $parts[0];
                        $lastName = '';
                        if ($count == 2) {
                            $lastName = $parts[1];
                        } elseif ($count > 2) {
                            unset($parts[0]);
                            $lastName = implode(' ', $parts);
                        }

                        if (!$customerEntity->getFirstName() && $firstName) {
                            $customerEntity->set('first_name', $firstName);
                        }

                        if (!$customerEntity->getLastName() && $lastName) {
                            $customerEntity->set('last_name', $lastName);
                        }

                        if ($value != $customerEntity->getBillingName()) {
                            $customerEntity->set('billing_name', $value);
                        }

                        $cartCustomer->set('first_name', $firstName);
                        $cartCustomer->set('last_name', $lastName);
                        $cartCustomer->set('billing_name', $value);

                        break;
                    case 'email':
                        // this field will only be here when guest checkout is enabled
                        if (!$customerEntity->getId()
                            && strlen($value) > 5
                        ) {
                            $customerEntity->set('email', $value);
                            $cartCustomer->set('email', $value);
                        }
                        break;
                    default:

                        if (is_null($value)) {
                            continue;
                        }

                        $customerEntity->set($childKey, $value);
                        $cartCustomer->set($childKey, $value);
                        break;
                }
            }

            // we do not check for ID here, only email address
            //  because it could be a new customer being created
            if (strlen($customerEntity->getEmail()) > 5) {
                try {
                    $this->getEntityService()->persist($customerEntity);
                    if ($customerEntity->getId()) {
                        $this->getCheckoutSessionService()->getCartSessionService()->setCustomerEntity($customerEntity);
                    }
                } catch(\Exception $e) { }
            }

            // todo: if tax is enabled and shipping is disabled, then apply tax to billing

            // todo : set totals to returnData

            $cart = $this->getCheckoutSessionService()
                ->getCartSessionService()
                //->collectShippingMethods('main') // avoid collecting shipping methods unless cart changes or shipping info changes
                ->collectTotals()
                ->getCart();

            $cart->setCustomer($cartCustomer);

            $returnData['cart'] = $cart;

        } else {

            foreach($form->all() as $childKey => $child) {
                $errors = $child->getErrors();
                if ($errors->count()) {
                    if (!isset($invalid[$childKey])) {
                        $invalid[$childKey] = [];
                    }
                    foreach($errors as $error) {
                        $invalid[$childKey][] = $error->getMessage();
                    }
                }
            }
        }

        $this->getCheckoutSessionService()->setIsValidBillingAddress($isValid);

        $returnData['success'] = $isValid;
        $returnData['messages'] = $messages;
        $returnData['invalid'] = $invalid;

        $cartService = $this->getCheckoutSessionService()->getCartSessionService()->getCartService();
        if ($isValid && !$cartService->getIsSpaEnabled()) {
            if ($cartService->getShippingService()->getIsShippingEnabled()) {
                $returnData['redirect_url'] = $this->getRouter()->generate('cart_checkout_shipping_address', []);
            } else {
                $returnData['redirect_url'] = $this->getRouter()->generate('cart_checkout_totals_discounts', []);
            }
        }

        $response = new JsonResponse($returnData);

        $event->setReturnData($returnData)
            ->setResponse($response);
    }
}
