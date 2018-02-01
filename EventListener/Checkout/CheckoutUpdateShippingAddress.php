<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Constants\CheckoutConstants;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\EventListener\Cart\BaseCartListener;

/**
 * Class CheckoutUpdateShippingAddress
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutUpdateShippingAddress extends BaseCartListener
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

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
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
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
        return $this->getCartService()->getShippingService()->getIsShippingEnabled();
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

        $fields = isset($sectionData['fields'])
            ? $sectionData['fields']
            : [];

        $nextSection = isset($sectionData['next_section'])
            ? $sectionData['next_section']
            : '';

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

        //$cartCustomer = $this->getCartService()->getCustomer();

        // only load a customer if we have a customer ID
        $customerEntity = $this->getCartService()->getCustomerId()
            ? $this->getEntityService()->find(EntityConstants::CUSTOMER, $this->getCartService()->getCustomerId())
            : null;

        // this step does not handle registrations

        // todo : handle submission of customer_address ID

        $requestData = $request->request->all();
        if (isset($requestData['cart_id'])) {
            unset($requestData['cart_id']);
        }

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

                        $this->getCartService()->getCustomer()->set($childKey, $isShippingSame);
                        if ($customerEntity) {
                            $customerEntity->set($childKey, $isShippingSame);
                        }
                        break;
                    default:

                        if (is_null($value)) {
                            continue;
                        }

                        $this->getCartService()->getCustomer()->set($childKey, $value);
                        if ($customerEntity) {
                            $customerEntity->set($childKey, $value);
                        }
                        break;
                }
            }

            // check is_shipping_same, copy values
            if ($isShippingSame) {
                $this->getCartService()->getCustomer()->copyBillingToShipping();
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
                        $this->getCartService()->setCustomerEntity($customerEntity);
                    }
                } catch(\Exception $e) {
                    $isValid = false;
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

        $this->getCartService()->setSectionIsValid(CheckoutConstants::STEP_SHIPPING_ADDRESS, $isValid);

        if ($isValid) {
            // only updating the main address
            $rateRequest = $this->getCartService()->createRateRequest('main');
            $this->getCartService()->collectShippingMethods($rateRequest);
        }

        $this->getCartService()->saveCart();

        $event->setSuccess($isValid);
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
