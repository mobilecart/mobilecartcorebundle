<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

class CheckoutUpdatePaymentMethod
{
    protected $event;

    protected $checkoutSessionService;

    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function setCheckoutSessionService($checkoutSessionService)
    {
        $this->checkoutSessionService = $checkoutSessionService;
        return $this;
    }

    public function getCheckoutSessionService()
    {
        return $this->checkoutSessionService;
    }

    public function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function onCheckoutUpdatePaymentMethod(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $isValid = 0;

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

            $form->submit($formData);
            $isValid = (int) $form->isValid();

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

        $this->getCheckoutSessionService()->setIsValidPaymentMethod($isValid);

        $returnData['success'] = $isValid;

        $response = new JsonResponse($returnData);

        $event->setReturnData($returnData)
            ->setResponse($response);
    }
}
