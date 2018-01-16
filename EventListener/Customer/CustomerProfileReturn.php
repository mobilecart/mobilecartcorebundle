<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CustomerProfileReturn
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerProfileReturn
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
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

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
     * @param CoreEvent $event
     */
    public function onCustomerProfileReturn(CoreEvent $event)
    {
        $customer = $event->getEntity();

        if ($event->getMessages() && $event->getRequest()->getSession()) {
            $event->flashMessages();
        }

        switch($event->getRequestAccept()) {
            case CoreEvent::JSON:

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

                $event->setResponse(new JsonResponse([
                    'success' => $event->getIsValid(),
                    'entity' => $customer->getData(),
                    'redirect_url' => $this->getRouter()->generate('customer_profile', []),
                    'invalid' => $invalid,
                    'messages' => $event->getMessages(),
                ]));

                break;
            default:

                $event->setReturnData('template_sections', []);

                $template = $event->getTemplate()
                    ? $event->getTemplate()
                    : 'Customer:profile.html.twig';

                $form = $event->getReturnData('form');
                $form = $form->createView();
                $event->setReturnData('form', $form);

                $event->setResponse($this->getThemeService()->render(
                    'frontend',
                    $template,
                    $event->getReturnData()
                ));

                break;
        }
    }
}
