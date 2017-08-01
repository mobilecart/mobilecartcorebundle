<?php

namespace MobileCart\CoreBundle\EventListener\Export;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ExportRun
 * @package MobileCart\CoreBundle\EventListener\Export
 */
class ExportRun
{
    /**
     * @var \MobileCart\CoreBundle\Service\ExportService
     */
    protected $exportService;

    protected $formTypeClass = '';

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    protected $router;

    /**
     * @param $exportService
     * @return $this
     */
    public function setExportService($exportService)
    {
        $this->exportService = $exportService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ExportService
     */
    public function getExportService()
    {
        return $this->exportService;
    }

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

    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function onExportRun(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $request = $event->getRequest();

        $route = 'cart_admin_export_run';
        $params = [];
        $url = $this->getRouter()->generate($route, $params);

        $form = $this->getFormFactory()->create($this->getFormTypeClass(), new \stdClass(), [
            'action' => $url,
            'method' => 'POST',
        ]);

        if ($form->handleRequest($request)->isValid()) {
            $formData = $request->request->get('export_options');
            $exportOptionKey = $formData['export_option'];
            $startDateData = $formData['start_date'];
            $endDateData = $formData['end_date'];

            $startDate = $startDateData['year'] . '-' . $startDateData['month'] . '-' . $startDateData['day'];
            $endDate = $endDateData['year'] . '-' . $endDateData['month'] . '-' . $endDateData['day'];

            $export = $this->getExportService()
                ->setExportOptionKey($exportOptionKey)
                ->setStartDate($startDate)
                ->setEndDate($endDate)
                ->runExport();

            if ($export->getResponse()) {
                $response = $export->getResponse();
                $event->setResponse($response);
            }
        }

        $event->setReturnData($returnData);
    }
}
