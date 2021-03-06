<?php

namespace MobileCart\CoreBundle\EventListener\Export;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ExportOptionsViewReturn
 * @package MobileCart\CoreBundle\EventListener\Export
 */
class ExportOptionsViewReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @var string
     */
    protected $formTypeClass = '';

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @param $class
     * @return $this
     */
    public function setFormTypeClass($class)
    {
        $this->formTypeClass = $class;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormTypeClass()
    {
        return $this->formTypeClass;
    }

    /**
     * @param $formFactory
     * @return $this
     */
    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    /**
     * @return \Symfony\Component\Form\FormFactory
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
     * @param \MobileCart\CoreBundle\Service\ThemeService $themeService
     * @return $this
     */
    public function setThemeService(\MobileCart\CoreBundle\Service\ThemeService $themeService)
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

    public function onExportOptionsViewReturn(CoreEvent $event)
    {
        $returnData = $event->getReturnData();

        $form = $this->getFormFactory()->create($this->getFormTypeClass(), new \stdClass(), [
            'action' => $this->getRouter()->generate('cart_admin_export_run', []),
            'method' => 'POST',
        ]);

        $returnData['form'] = $form->createView();

        $response = $this->getThemeService()->renderAdmin(
            'Export:options.html.twig',
            $returnData
        );

        $event->setReturnData($returnData)
            ->setResponse($response);
    }
}
