<?php

namespace MobileCart\CoreBundle;

final class Version
{
    /**
     * Get Version of MobileCart Core
     *  call this from other bundles to handle API changes
     *
     * @return string
     */
    static function getVersion()
    {
        return '1.0.0';
    }
}
