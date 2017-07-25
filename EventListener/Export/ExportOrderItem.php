<?php

namespace MobileCart\CoreBundle\EventListener\Export;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Component\EventDispatcher\Event;

class ExportOrderItem
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var Event
     */
    protected $event;

    /**
     * @var string
     */
    protected $exportOptionKey = 'order_item';

    /**
     * @var string
     */
    protected $exportOptionLabel = 'Export Order Items';

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
    public function onExportOptionsCollect(Event $event)
    {
        if ($event->getRunExport()) {
            if ($event->getExportOptionKey() == $this->exportOptionKey) {
                // build query

                $itemTable = $this->getEntityService()->getTableName(EntityConstants::ORDER_ITEM);
                $shipTable = $this->getEntityService()->getTableName(EntityConstants::ORDER_SHIPMENT);
                $orderTable = $this->getEntityService()->getTableName(EntityConstants::ORDER);

                $sql = "select o.id, o.reference_nbr, oi.id, oi.sku, oi.name, oi.base_price, oi.qty, oi.tax" .
                    " from {$itemTable} oi "
                ;

                // output header

                // loop rows, echo string

                // create Export object, set data

                die();
            }
        } else {
            $event->addExportOption(new ArrayWrapper([
                'key' => $this->exportOptionKey,
                'label' => $this->exportOptionLabel,
            ]));
        }
    }
}
