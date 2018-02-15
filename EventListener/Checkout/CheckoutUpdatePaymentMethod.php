<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\CheckoutConstants;

/**
 * Class CheckoutUpdatePaymentMethod
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutUpdatePaymentMethod
{
    /**
     * @var \MobileCart\CoreBundle\Service\OrderService
     */
    protected $orderService;

    /**
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
    }

    /**
     * @param \MobileCart\CoreBundle\Service\OrderService $orderService
     * @return $this
     */
    public function setOrderService(\MobileCart\CoreBundle\Service\OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\OrderService
     */
    public function getOrderService()
    {
        return $this->orderService;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->getOrderService()->getCartService();
    }

    public function onCheckoutUpdatePaymentMethod(CoreEvent $event)
    {
        $isValid = false;

        $paymentMethod = '';
        $formData = [];

        // parse/convert API requests
        switch($event->getContentType()) {
            case CoreEvent::JSON:

                $apiRequest = $event->getApiRequest()
                    ? $event->getApiRequest()
                    : @ (array) json_decode($event->getRequest()->getContent());

                if (isset($apiRequest['payment_method'])) {

                    $paymentMethod = $apiRequest['payment_method'];

                    if (is_string($paymentMethod)
                        && isset($apiRequest[$paymentMethod])
                    ) {

                        $formData = $apiRequest[$paymentMethod];
                        if ($formData instanceof \stdClass) {
                            $formData = get_object_vars($formData);
                        }
                    }
                }

                break;
            default:

                $paymentMethod = $event->getRequest()->get('payment_method', '');
                $requestData = $event->getRequest()->request->all();

                $formData = isset($requestData[$paymentMethod])
                    ? $requestData[$paymentMethod]
                    : $requestData;

                break;
        }

        $paymentMethodService = $this->getOrderService()
            ->getPaymentService()
            ->findPaymentMethodServiceByCode($paymentMethod);

        if ($paymentMethodService) {

            /**
             * Set flag because the submission data might be different
             *  than the initial form which was displayed
             */
            $paymentMethodService->setIsSubmission(true);

            // todo : set action, look at cart items

            $form = $paymentMethodService->buildForm()
                ->getForm();

            if (isset($formData['payment_method'])) {
                unset($formData['payment_method']);
            }

            if (isset($formData['cart_id'])) {
                unset($formData['cart_id']);
            }

            $form->submit($formData);
            $isValid = (bool) $form->isValid();

            if ($isValid) {

                // todo: set payment action here ?

                $this->getCartService()
                    ->setPaymentMethodCode($paymentMethod)
                    ->setPaymentData($formData);

            } else {

                $invalid = [];
                foreach($form->all() as $childKey => $child) {
                    $errors = $child->getErrors();
                    if ($errors->count()) {
                        $invalid[$childKey] = [];
                        foreach($errors as $error) {
                            $invalid[$childKey][] = $error->getMessage();
                        }
                    }
                }

                $event->setReturnData('invalid', $invalid);
                $event->setReturnData('prefix', $paymentMethod);
            }
        } else {
            $event->addErrorMessage("Invalid Form Submission. Invalid Payment Service");
        }

        $this->getCartService()->setSectionIsValid(CheckoutConstants::STEP_PAYMENT_METHOD, $isValid);

        $event->setSuccess($isValid);
        $event->setReturnData('cart', $this->getCartService()->getCart());
        $event->setReturnData('messages', $event->getMessages());

        $this->getCartService()->saveCart();

        $event->setResponse(new JsonResponse($event->getReturnData()));
    }
}
