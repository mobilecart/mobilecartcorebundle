<?php

namespace MobileCart\CoreBundle\Service;

class LocaleService
{
    protected $locales = [];

    protected $defaultLocale = '';

    public function addLocale($code, $label = '')
    {
        $this->locales[$code] = $label;
        return $this;
    }

    public function getLocales()
    {
        return $this->locales;
    }

    public function hasLocale($code)
    {
        return isset($this->locales[$code]);
    }

    public function setDefaultLocale($locale)
    {
        $this->defaultLocale = $locale;
        return $this;
    }

    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }
}
