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

    protected $passwordEncoder;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @param \MobileCart\CoreBundle\Service\CartService $cartService
     * @return $this
     */
    public function setCartService(\MobileCart\CoreBundle\Service\CartService $cartService)
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
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
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
        $isValid = false;

        $sectionData = $event->get('section_data', []);

        // todo : handle submission of customer_address ID

        // todo : this should either be shipping_address or the step after if is_shipping_same = true
        $nextSection = CheckoutConstants::STEP_SHIPPING_ADDRESS;

        // parse/convert API requests
        switch($event->getContentType()) {
            case CoreEvent::JSON:

                $apiRequest = $event->getApiRequest()
                    ? $event->getApiRequest()
                    : @ (array) json_decode($event->getRequest()->getContent());

                // allow this to work with a browser session. the request probably wouldn't contain cart_id
                if (array_key_exists('billing_city', $apiRequest)) {

                    // cart_id is not part of the form
                    if (isset($apiRequest['cart_id'])) {
                        unset($apiRequest['cart_id']);
                    }

                    $event->submitForm($apiRequest);
                    $isValid = $event->isFormValid();
                }

                break;
            default:

                $requestData = $event->getRequest()->request->all();
                $event->submitForm($requestData);
                $isValid = $event->isFormValid();

                break;
        }

        // if they are not registered (or logged in), and they try to checkout with a registered email
        if (!$this->getCartService()->getCustomerId()
            && isset($requestData['email'])
        ) {

            $existing = $this->getEntityService()->findOneBy(EntityConstants::CUSTOMER, [
                'email' => $requestData['email'],
            ]);

            if ($existing) {
                $isValid = false;
                $invalid['email'] = ['Email exists. Please login before checkout'];
            }
        }

        if ($isValid) {

            // currently, there is only a customer entity when you are logged in
            //  but, the logic here is ready for customer registration during checkout also

            if ($this->getCartService()->getCustomerId()) {
                if (!$this->getCartService()->getCustomerEntity()) {
                    $this->getCartService()->loadCustomerEntity();
                }
            } elseif (isset($requestData['register'])) {
                $this->getCartService()->setCustomerEntity($this->getEntityService()->getInstance(EntityConstants::CUSTOMER));
            }

            //$formData = $event->getFormData(); // todo : use this?
            $formData = $event->getForm()->getData();
            foreach($event->getForm()->all() as $childKey => $child) {

                // security concerns
                //  blacklist some fields as an extra security measure

                if (in_array($childKey, [
                    'id',
                    'item_var_set_id',
                    'hash',
                    'confirm_hash',
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
                    'register',
                ])) {
                    continue;
                }

                $value = $formData[$childKey];
                switch($childKey) {
                    case 'password':

                        // note: this isn't currently enabled. You would need to add this field to the form type
                        if ($this->getCartService()->getCustomerEntity()
                            && $value
                        ) {

                            $isPasswordValid = true;

                            if (strlen($value) < 8) {
                                $isPasswordValid = false;
                                $event->addErrorMessage('Password must be at least 8 characters');
                            }

                            // todo : more requirements, create a simple service

                            if ($isPasswordValid) {
                                $encoder = $this->getSecurityPasswordEncoder();
                                $encoded = $encoder->encodePassword($this->getCartService()->getCustomerEntity(), $value);
                                $this->getCartService()->getCustomerEntity()->setHash($encoded);
                                // hash should never be stored in session
                            } else {
                                $isValid = false;
                                $invalid['password'] = 'Password is too weak';
                            }
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

                        $this->getCartService()->getCustomer()->set('first_name', $firstName);
                        $this->getCartService()->getCustomer()->set('last_name', $lastName);
                        $this->getCartService()->getCustomer()->set('billing_name', $value);

                        if ($this->getCartService()->getCustomerEntity()
                            && !$this->getCartService()->getCustomerEntity()->getFirstName()
                            && $firstName
                        ) {
                            $this->getCartService()->getCustomerEntity()->set('first_name', $firstName);
                        }

                        if ($this->getCartService()->getCustomerEntity()
                            && !$this->getCartService()->getCustomerEntity()->getLastName()
                            && $lastName
                        ) {
                            $this->getCartService()->getCustomerEntity()->set('last_name', $lastName);
                        }

                        if ($this->getCartService()->getCustomerEntity()
                            && ($value != $this->getCartService()->getCustomerEntity()->getBillingName())
                        ) {
                            $this->getCartService()->getCustomerEntity()->set('billing_name', $value);
                        }

                        break;
                    case 'email':

                        // note: this logic isn't currently enabled,
                        //  you would need to create a new customer instance before this
                        // todo : add a better validator
                        if (strlen($value) > 5
                            && is_int(strpos($value, '@'))
                        ) {
                            $this->getCartService()->getCustomer()->set('email', $value);
                            if ($this->getCartService()->getCustomerEntity()) {
                                $this->getCartService()->getCustomerEntity()->set('email', $value);
                            }
                        }

                        break;
                    default:

                        if (is_null($value)) {
                            continue;
                        }

                        $this->getCartService()->getCustomer()->set($childKey, $value);

                        if ($this->getCartService()->getCustomerEntity()) {
                            $this->getCartService()->getCustomerEntity()->set($childKey, $value);
                        }

                        break;
                }
            }

            if ($this->getCartService()->getCustomerEntity()) {
                try {
                    $this->getCartService()->saveCustomerEntity();
                } catch(\Exception $e) {
                    $isValid = false;
                    $event->addErrorMessage('An error occurred while saving the customer account.');
                }
            }

            $this->getCartService()->collectTotals(); // re-apply tax and discounts

        } else {
            $event->setReturnData('invalid', $event->getFormInvalid());
        }

        $this->getCartService()->setSectionIsValid(CheckoutConstants::STEP_BILLING_ADDRESS, $isValid);

        // update the checkout_state whether the submission is valid or invalid
        $this->getCartService()->saveCart();

        $event->setSuccess($isValid);
        $event->setReturnData('cart', $this->getCartService()->getCart());
        $event->setReturnData('messages', $event->getMessages());
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
