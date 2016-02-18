<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Entity;

/**
 * Interface CartEntityEAVInterface
 * @package MobileCart\CoreBundle\Entity
 *
 * Method definitions for EAV-based Entities
 *
 */
interface CartEntityEAVInterface
{
    /**
     * Get Var Values as Entities
     *
     * @return array
     */
    public function getVarValues();

    /**
     * Get Var Values as associative array
     *
     * @return array
     */
    public function getVarValuesData();

    /**
     * Get Var Values as associative array, formatted for Lucene
     *
     * @return array
     */
    public function getLuceneVarValuesData();
}
