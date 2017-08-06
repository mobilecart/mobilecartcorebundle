<?php

namespace MobileCart\CoreBundle\Service;

/**
 * Interface SearchServiceInterface
 * @package MobileCart\CoreBundle\Service
 */
interface SearchServiceInterface
{
    /**
     * @param string $objectType
     * @return mixed
     */
    public function setObjectType($objectType);

    /**
     * Parse Request , set filters, sort, and paginator parameters
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return $this
     */
    public function parseRequest(\Symfony\Component\HttpFoundation\Request $request);

    /**
     * @return array|mixed
     */
    public function search();
}
