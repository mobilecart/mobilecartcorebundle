<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CustomerProfilePostReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @var Event
     */
    protected $event;

    protected $router;

    /**
     * @param $event
     * @return $this
     */
    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     */
    protected function getEvent()
    {
        return $this->event;
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

    /**
     * @param $themeService
     * @return $this
     */
    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ThemeService
     */
    public function getThemeService()
    {
        return $this->themeService;
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
     * @param Event $event
     */
    public function onCustomerProfilePostReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();
        $customer = $event->getEntity();

        $request = $event->getRequest();
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
