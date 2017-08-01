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

    protected $formTypeClass = '';

    protected $formFactory;

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

    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    public function getFormFactory()
    {
        return $this->formFactory;
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

    public function onExportOptionsViewReturn(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $route = 'cart_admin_export_run';
        $params = [];
        $url = $this->getRouter()->generate($route, $params);

        $form = $this->getFormFactory()->create($this->getFormTypeClass(), new \stdClass(), [
            'action' => $url,
            'method' => 'POST',
        ]);

        $returnData['form'] = $form->createView();

        $response = $this->getThemeService()
            ->render('admin', 'Export:options.html.twig', $returnData);

        $event->setReturnData($returnData)
            ->setResponse($response);
    }
}
