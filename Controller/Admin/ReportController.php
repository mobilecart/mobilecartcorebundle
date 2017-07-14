<?php

namespace MobileCart\CoreBundle\Controller\Admin;

use MobileCart\CoreBundle\Constants\EntityConstants;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\CoreEvent;

class ReportController extends Controller
{
    public function indexAction(Request $request)
    {
        // get report options via Service via Event

        /*

reports:
 customer totals, by day, month and year
 general totals, by day, month and year
 item totals, by day, month and year
 shipment totals, by day, month and year



        //*/

        // get date range via Request

        // get response type: html, csv, json via Request

        // run report via Event
        // get service via DI

        // return response
    }
}
