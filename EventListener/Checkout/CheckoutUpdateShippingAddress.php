<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Constants\CheckoutConstants;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CheckoutUpdateShippingAddress
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutUpdateShippingAddress
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var \MobileCart\CoreBundle\Service\CheckoutSessionService
     */
    protected $checkoutSessionService;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

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
     * @return bool
     */
    public function getIsShippingEnabled()
    {
        return $this->getCheckoutSessionService()->getCartSessionService()->getShippingService()->getIsShippingEnabled();
    }

    /**
     * @param CoreEvent $event
     * @return bool
     */
    public function onCheckoutUpdateShippingAddress(CoreEvent $event)
    {
        if (!$this->getIsShippingEnabled()) {
            return false;
        }

        $sectionData = $event->get('section_data', []);

        $form = isset($sectionData['form'])
            ? $sectionData['form']
            : [];

        $nextSection = isset($sectionData['next_section'])
            ? $sectionData['next_section']
            : '';

        $request = $event->getRequest();

        $cartCustomer = $this->getCheckoutSessionService()
            ->getCartSessionService()
            ->getCustomer();

        // only load a customer if we have a customer ID
        $customerEntity = $cartCustomer->getId()
            ? $this->getEntityService()->find(EntityConstants::CUSTOMER, $cartCustomer->getId())
            : null;

        $requestData = $request->request->all();
        $form->submit($requestData);
        $isValid = $form->isValid();
        $invalid = [];
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

                $value = $formData[$childKey];

                switch($childKey) {
                    case 'is_shipping_same':

                        $isShippingSame = (bool) $value;

                        $cartCustomer->set($childKey, $isShippingSame);
                        if ($customerEntity) {
                            $customerEntity->set($childKey, $isShippingSame);
                        }
                        break;
                    default:

                        if (is_null($value)) {
                            continue;
                        }

                        $cartCustomer->set($childKey, $value);
                        if ($customerEntity) {
                            $customerEntity->set($childKey, $value);
                        }
                        break;
                }
            }

            // check is_shipping_same, copy values
            if ($isShippingSame) {
                $cartCustomer->copyBillingToShipping();
                $isShippingSame = true;
            }

            // finally, if we have a registered customer, update our records with the submitted info
            if ($customerEntity) {

                if ($isShippingSame) {
                    $customerEntity->copyBillingToShipping();
                }

                try {
                    $this->getEntityService()->persist($customerEntity);
                    if ($customerEntity->getId()) {
                        $this->getCheckoutSessionService()->getCartSessionService()->setCustomerEntity($customerEntity);
                    }
                } catch(\Exception $e) {
                    $event->addErrorMessage('An exception occurred while saving the customer.');
                }
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

        $this->getCheckoutSessionService()->setSectionIsValid(CheckoutConstants::STEP_SHIPPING_ADDRESS, $isValid);

        if ($isValid) {
            // only updating the main address
            $this->getCheckoutSessionService()->getCartSessionService()->collectShippingMethods('main');
        }

        $event->setReturnData('success', $isValid);
        $event->setReturnData('messages', $event->getMessages());
        $event->setReturnData('invalid', $invalid);
        $event->setReturnData('next_section', $nextSection);

        if ($isValid && strlen($nextSection)) {

            $event->setReturnData('redirect_url', $this->getRouter()->generate(
                'cart_checkout_section', [
                    'section' => $nextSection
                ]
            ));
        }

        $event->setResponse(new JsonResponse($event->getReturnData()));
    }
}
