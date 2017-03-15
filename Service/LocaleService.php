<?php

namespace MobileCart\CoreBundle\Service;

class LocaleService
{
    /**
     * @var array
     */
    protected $locales = [];

    /**
     * @var string
     */
    protected $defaultLocale = '';

    /**
     * @param $code
     * @param string $label
     * @return $this
     */
    public function addLocale($code, $label = '')
    {
        $this->locales[$code] = $label;
        return $this;
    }

    /**
     * @return array
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * @param $code
     * @return bool
     */
    public function hasLocale($code)
    {
        return isset($this->locales[$code]);
    }

    /**
     * @param $locale
     * @return $this
     */
    public function setDefaultLocale($locale)
    {
        $this->defaultLocale = $locale;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }
}
