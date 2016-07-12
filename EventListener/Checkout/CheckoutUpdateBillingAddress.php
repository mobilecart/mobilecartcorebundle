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

    public function onCheckoutUpdateBillingAddress(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $request = $event->getRequest();
        $formType = $event->getForm();
        $entity = $event->getEntity();

        $cartCustomer = $this->getCheckoutSessionService()
            ->getCartSessionService()
            ->getCustomer();

        $form = $this->getFormFactory()->create($formType, $entity, [
            'action' => $event->getAction(),
            'method' => $event->getMethod(),
            'translation_domain' => 'checkout',
            'validation_groups' => 'billing_address',
        ]);

        $requestData = $request->request->all();
        $form->submit($requestData);
        $isValid = $form->isValid();

        $messages = [];
        $invalid = [];

        if ($isValid) {

            $customerEntity = $cartCustomer->getId()
                ? $this->getEntityService()->find(EntityConstants::CUSTOMER, $cartCustomer->getId())
                : $this->getEntityService()->getInstance(EntityConstants::CUSTOMER);

            // allow the order to connect to the customer account
            //  but don't allow the password to update
            if (!$cartCustomer->getId() && isset($requestData['email'])) {

                $aEntity = $this->getEntityService()->findOneBy(EntityConstants::CUSTOMER, [
                    'email' => $requestData['email'],
                ]);

                if ($aEntity) {
                    $customerEntity = $aEntity;
                }
            }

            //update customer data in cart session
            $customerData = [];
            $formData = $form->getData();
            foreach($form->all() as $childKey => $child) {

                // extra precaution
                // potential security hole : guest checkout and updating data on existing user
                if (in_array($childKey, [
                    'id',
                    'hash',
                    'confirm_hash',
                    'item_var_set_id',
                    'failed_logins',
                    'locked_at',
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

                $value = null;
                switch($childKey) {
                    case 'password':
                        if (!$customerEntity->getId()) {
                            $encoder = $this->getSecurityPasswordEncoder();
                            $encoded = $encoder->encodePassword($customerEntity, $formData->get($childKey));
                            $customerEntity->setHash($encoded);
                            $customerData['hash'] = $encoded;
                        }
                        break;
                    case 'billing_name':
                        $value = $formData->get($childKey);
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
                        $customerData['first_name'] = $firstName;
                        $customerData['last_name'] = $lastName;
                        break;
                    default:
                        $value = $formData->get($childKey);
                        break;
                }

                if (is_null($value)) {
                    continue;
                }

                if ($customerEntity->getId()) {
                    switch($childKey) {
                        case 'email':
                            // no-op
                            break;
                        default:
                            $cartCustomer->set($childKey, $value);
                            $customerData[$childKey] = $value;
                            break;
                    }
                } else {
                    $cartCustomer->set($childKey, $formData->get($childKey));
                    $customerData[$childKey] = $value;
                }
            }

            if (!$customerEntity->getId()) {
                $customerEntity->fromArray($customerData);
                //try {
                $this->getEntityService()->persist($customerEntity);
                if ($customerEntity) {

                    $this->getCheckoutSessionService()
                        ->getCartSessionService()
                        ->getCart()
                        ->getCustomer()
                        ->setId($customerEntity->getId());

                }
                //} catch(\Exception $e) { }
            }

            // todo: if tax is enabled and shipping is disabled, then apply tax to billing

            // todo : collect totals regardless

            // todo : set totals to returnData

            $cart = $this->getCheckoutSessionService()
                ->getCartSessionService()
                ->collectShippingMethods()
                ->collectTotals()
                ->getCart();

            $returnData['cart'] = $cart;

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

        $this->getCheckoutSessionService()->setIsValidBillingAddress($isValid);

        $returnData['success'] = $isValid;
        $returnData['messages'] = $messages;
        $returnData['invalid'] = $invalid;

        $response = new JsonResponse($returnData);

        $event->setReturnData($returnData)
            ->setResponse($response);
    }
}
