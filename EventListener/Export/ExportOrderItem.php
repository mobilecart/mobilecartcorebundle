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

                // create Export object, set data
                $event->setExport(new ArrayWrapper()); // we're not returning anyways

                // build query

                $itemTable = $this->getEntityService()->getTableName(EntityConstants::ORDER_ITEM);
                $shipTable = $this->getEntityService()->getTableName(EntityConstants::ORDER_SHIPMENT);
                $orderTable = $this->getEntityService()->getTableName(EntityConstants::ORDER);

                $sql = "select o.created_at, o.id, o.reference_nbr, oi.id, oi.sku, oi.name, oi.base_price, oi.qty, oi.tax, os.company, os.method" .
                    " from {$itemTable} oi " .
                    " inner join {$orderTable} o on oi.order_id=o.id " .
                    " left join {$shipTable} os on oi.order_shipment_id=os.id" .
                    " where o.created_at >= '{$event->getStartDate()}'" .
                    " and o.created_at < '{$event->getEndDate()}'";

                // execute query
                $conn = $this->getEntityService()
                    ->getDoctrine()
                    ->getManager()
                    ->getConnection();

                $stmt = $conn->prepare($sql);
                $stmt->execute();

                // output header

                header("Content-Type: application/csv");
                header("Content-Disposition: attachment; filename=order_items-{$event->getStartDate()}-{$event->getEndDate()}.csv");
                header("Pragma: no-cache");

                // loop rows, echo string, saving memory
                $sentHeaderRow = false;
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    if (!$sentHeaderRow) {
                        echo implode(';', array_keys($row)) . "\n";
                        $sentHeaderRow = true;
                    }
                    echo implode(';', $row) . "\n";
                }

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
