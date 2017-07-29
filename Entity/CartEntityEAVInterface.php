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
interface CartEntityEAVInterface extends CartEntityInterface
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

    /**
     * Get var_values_datetime
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVarValuesDatetime();

    /**
     * Get var_values_decimal
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVarValuesDecimal();

    /**
     * Get var_values_int
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVarValuesInt();

    /**
     * Get var_values_text
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVarValuesText();

    /**
     * Get var_values_varchar
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVarValuesVarchar();

}
