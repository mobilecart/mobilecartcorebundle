<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CustomerProfilePostReturn
{
    protected $request;

    protected $entityService;

    protected $themeService;

    protected $event;

    protected $router;

    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    protected function getEvent()
    {
        return $this->event;
    }

    protected function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
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

    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    public function getThemeService()
    {
        return $this->themeService;
    }

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function onCustomerProfilePostReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $customer = $event->getEntity();
        $objectType = $event->getObjectType();

        $request = $event->get('request');
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');

        $response = '';
        switch($format) {
            case 'json':

                $isValid = (int) $event->getIsValid();
                $invalid = [];
                if (!$isValid) {
                    $form = $event->getForm();
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

                $returnData = [
                    'success' => $event->getIsValid(),
                    'entity' => $customer->getData(),
                    'redirect_url' => $this->getRouter()->generate('customer_profile', []),
                    'invalid' => $invalid,
                ];

                $response = new JsonResponse($returnData);
                break;
            default:

                $typeSections = [];
                $returnData['template_sections'] = $typeSections;

                if ($messages = $event->getMessages()) {
                    foreach($messages as $code => $message) {
                        $event->getRequest()->getSession()->getFlashBag()->add($code, $message);
                    }
                }

                $response = new RedirectResponse($this->getRouter()->generate('customer_profile', []));

                break;
        }

        $event->setResponse($response)
            ->setReturnData($returnData);
    }
}
