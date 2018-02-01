<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Constants\CheckoutConstants;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\EventListener\Cart\BaseCartListener;

/**
 * Class CheckoutUpdateBillingAddress
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutUpdateBillingAddress extends BaseCartListener
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
        $sectionData = $event->get('section_data', []);

        $form = isset($sectionData['form'])
            ? $sectionData['form']
            : [];

        $fields = isset($sectionData['fields'])
            ? $sectionData['fields']
            : [];

        // parse/convert API requests
        switch($event->getContentType()) {
            case CoreEvent::JSON:

                $apiRequest = $event->getApiRequest()
                    ? $event->getApiRequest()
                    : @ (array) json_decode($event->getRequest()->getContent());

                if (isset($apiRequest['cart_id'])) {

                    $event->getRequest()->request->set('cart_id', $apiRequest['cart_id']);

                    foreach($apiRequest as $key => $value) {

                        if (!in_array($key, $fields)) {
                            continue;
                        }

                        $event->getRequest()->request->set($key, $value);
                    }
                }

                break;
            default:

                break;
        }

        $request = $event->getRequest();
        $this->initCart($request);

        $nextSection = isset($sectionData['next_section'])
            ? $sectionData['next_section']
            : '';

        $request = $event->getRequest();
        $requestData = $request->request->all();
        if (isset($requestData['cart_id'])) {
            unset($requestData['cart_id']);
        }

        $form->submit($requestData);
        $isValid = $form->isValid();

        $invalid = [];

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

            $formData = $form->getData();
            foreach($form->all() as $childKey => $child) {

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

        $this->getCartService()->setSectionIsValid(CheckoutConstants::STEP_BILLING_ADDRESS, $isValid);

        // save cart entity
        $this->getCartService()->saveCart();

        $event->setSuccess($isValid);
        $event->setReturnData('cart', $this->getCartService()->getCart());
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
