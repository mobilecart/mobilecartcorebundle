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
     * @param $orderService
     * @return $this
     */
    public function setOrderService($orderService)
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
        $returnData = $event->getReturnData();

        $isValid = false;

        // todo : for api calls, where do we store payment data ? add a column to cart ?

        $returnData['messages'] = [];
        $returnData['invalid'] = [];

        $request = $event->getRequest();
        $paymentMethod = $request->get('payment_method', '');
        $paymentMethodService = $this->getCheckoutSessionService()
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

            $requestData = $request->request->all();
            $formData = isset($requestData[$paymentMethod])
                ? $requestData[$paymentMethod]
                : $requestData;

            if (isset($formData['payment_method'])) {
                unset($formData['payment_method']);
            }

            $form->submit($formData);
            $isValid = (bool) $form->isValid();

            if ($isValid) {

                // set action here ?

                $this->getCheckoutSessionService()
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

                $returnData['invalid'] = $invalid;
                $returnData['prefix'] = $paymentMethod;
            }

        } else {
            $returnData['messages'][] = "Invalid Form Submission. Invalid Payment Service";
        }

        $this->getCheckoutSessionService()->setSectionIsValid(CheckoutConstants::STEP_PAYMENT_METHOD, $isValid);

        $returnData['success'] = $isValid;

        $response = new JsonResponse($returnData);

        $event->setReturnData($returnData)
            ->setResponse($response);
    }
}
