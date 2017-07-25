<?php

namespace MobileCart\CoreBundle\Service;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;

class ExportService
{
    /**
     * @var mixed
     */
    protected $eventDispatcher;

    /**
     * @var array
     */
    protected $exportOptions = [];

    /**
     * @var bool
     */
    protected $isCollected = false;

    /**
     * @var string
     */
    protected $format = 'csv';

    /**
     * @var string
     */
    protected $exportOptionKey = '';

    /**
     * @var string
     */
    protected $delimiter = '';

    /**
     * @var string
     */
    protected $fieldsEnclosedWith = '';

    /**
     * @var string
     */
    protected $startDate = '';

    /**
     * @var string
     */
    protected $endDate = '';

    /**
     * @param $eventDispatcher
     * @return $this
     */
    public function setEventDispatcher($eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param $format
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param $exportOptionKey
     * @return $this
     */
    public function setExportOptionKey($exportOptionKey)
    {
        $this->exportOptionKey = $exportOptionKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getExportOptionKey()
    {
        return $this->exportOptionKey;
    }

    /**
     * @param $startDate
     * @return $this
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param $endDate
     * @return $this
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param $delimiter
     * @return $this
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * @param $char
     * @return $this
     */
    public function setFieldsEnclosedWith($char)
    {
        $this->fieldsEnclosedWith = $char;
        return $this;
    }

    /**
     * @return string
     */
    public function getFieldsEnclosedWith()
    {
        return $this->fieldsEnclosedWith;
    }

    /**
     * @return $this
     */
    public function collectExportOptions()
    {
        $event = new CoreEvent();
        $this->getEventDispatcher()
            ->dispatch(CoreEvents::EXPORT_OPTIONS_COLLECT, $event);

        $this->exportOptions = $event->getExportOptions();
        $this->isCollected = true;
        return $this;
    }

    /**
     * @return array
     */
    public function getExportOptions()
    {
        if (!$this->isCollected) {
            $this->collectExportOptions();
        }

        return $this->exportOptions;
    }

    /**
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function runExport()
    {
        $exportOptions = $this->getExportOptions();
        $found = false;

        if ($exportOptions) {
            foreach($exportOptions as $exportOption) {
                if ($exportOption->getKey() == $this->exportOptionKey) {
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            throw new \InvalidArgumentException('Invalid Export Option');
        }

        $event = new CoreEvent();
        $event->setRunExport(true)
            ->setExportOptionKey($this->exportOptionKey)
            ->setFormat($this->format)
            ->setStartDate($this->startDate)
            ->setEndDate($this->endDate)
            ->setDelimiter($this->delimiter)
            ->setFieldsEnclosedWith($this->fieldsEnclosedWith);

        $this->getEventDispatcher()
            ->dispatch(CoreEvents::EXPORT_OPTIONS_COLLECT, $event);

        return $event->getExport();
    }
}
