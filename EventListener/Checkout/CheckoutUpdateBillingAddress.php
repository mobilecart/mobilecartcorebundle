<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Constants\CheckoutConstants;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CheckoutUpdateBillingAddress
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutUpdateBillingAddress
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var \MobileCart\CoreBundle\Service\CheckoutSessionService
     */
    protected $checkoutSessionService;

    protected $passwordEncoder;

    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

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
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @return $this
     */
    public function setFormFactory(\Symfony\Component\Form\FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    /**
     * @return \Symfony\Component\Form\FormFactoryInterface
     */
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

    public function setSecurityPasswordEncoder($passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        return $this;
    }

    public function getSecurityPasswordEncoder()
    {
        return $this->passwordEncoder;
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
     */
    public function onCheckoutUpdateBillingAddress(CoreEvent $event)
    {
        $sectionData = $event->get('section_data', []);

        $form = isset($sectionData['form'])
            ? $sectionData['form']
            : [];

        $nextSection = isset($sectionData['next_section'])
            ? $sectionData['next_section']
            : '';

        $request = $event->getRequest();

        $cart = $this->getCheckoutSessionService()
            ->getCartSessionService()
            ->getCart();

        $customerId = $this->getCheckoutSessionService()->getCartSessionService()->getCustomerId();

        $cartCustomer = $cart->getCustomer();

        $requestData = $request->request->all();
        $form->submit($requestData);
        $isValid = $form->isValid();

        // if they are not registered (or logged in), and they try to checkout with a registered email
        if (!$customerId && isset($requestData['email'])) {

            $existing = $this->getEntityService()->findOneBy(EntityConstants::CUSTOMER, [
                'email' => $requestData['email'],
            ]);

            if ($existing) {
                $isValid = false;
                $invalid['email'] = ['Email exists. Please login before checkout'];
            }
        }

        $invalid = [];

        if ($isValid) {

            // currently, there is only a customer entity if you are logged in
            //  but, the logic here is ready for customer registration during checkout also
            $customerEntity = $customerId
                ? $this->getEntityService()->find(EntityConstants::CUSTOMER, $customerId)
                : null;

            $formData = $form->getData();
            foreach($form->all() as $childKey => $child) {

                // security concerns
                //  blacklist some fields as an extra security measure

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

                //$value = $formData->get($childKey);
                $value = $formData[$childKey];
                switch($childKey) {
                    case 'password':
                        // note: this isn't currently enabled. You would need to add this field to the form type
                        if ($customerEntity
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

                        $cartCustomer->set('first_name', $firstName);
                        $cartCustomer->set('last_name', $lastName);
                        $cartCustomer->set('billing_name', $value);

                        if ($customerEntity && !$customerEntity->getFirstName() && $firstName) {
                            $customerEntity->set('first_name', $firstName);
                        }

                        if ($customerEntity && !$customerEntity->getLastName() && $lastName) {
                            $customerEntity->set('last_name', $lastName);
                        }

                        if ($customerEntity && ($value != $customerEntity->getBillingName())) {
                            $customerEntity->set('billing_name', $value);
                        }

                        break;
                    case 'email':

                        $cartCustomer->set('email', $value);

                        // note: this logic isn't currently enabled,
                        //  you would need to create a new customer instance before this
                        if ($customerEntity
                            && strlen($value) > 5
                        ) {
                            $customerEntity->set('email', $value);
                        }

                        break;
                    default:

                        if (is_null($value)) {
                            continue;
                        }

                        $cartCustomer->set($childKey, $value);

                        // note : currently only updates customer if you are logged in
                        if ($customerEntity) {
                            $customerEntity->set($childKey, $value);
                        }

                        break;
                }
            }

            // todo : in the future, handle new registrations during checkout

            if ($customerEntity) {
                try {
                    $this->getEntityService()->persist($customerEntity);
                    if ($customerEntity->getId()) {
                        $this->getCheckoutSessionService()->getCartSessionService()->setCustomerEntity($customerEntity);
                    }
                } catch(\Exception $e) {
                    $event->addErrorMessage('An exception occurred while saving the customer account.');
                }
            }

            $cart = $this->getCheckoutSessionService()
                ->getCartSessionService()
                //->collectShippingMethods('main') // avoid collecting shipping methods unless cart changes or shipping info changes
                ->collectTotals()
                ->getCart();

            $event->setReturnData('cart', $cart);

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

        $this->getCheckoutSessionService()->setSectionIsValid(CheckoutConstants::STEP_BILLING_ADDRESS, $isValid);

        $event->setReturnData('success', $isValid);
        $event->setReturnData('messages', $event->getMessages());
        $event->setReturnData('invalid', $invalid);
        $event->setReturnData('next_section', $nextSection);

        if ($isValid && strlen($nextSection)) {

            $event->setReturnData('redirect_url', $this->getRouter()->generate(
                'cart_checkout_section', [
                'section' => $nextSection
            ]));
        }

        $event->setResponse(new JsonResponse($event->getReturnData()));
    }
}
