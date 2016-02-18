<?php

namespace MobileCart\CoreBundle\Search;

class FacetTerm
{
    public $term;
    public $urlToken;
    public $url;
    public $count;
    public $remove_url; // todo: match camel-case or snake-case
    //public $missing; // todo: what does this mean?

    public function __construct($term = '', $urlToken = '', $url = '', $count = 0, $removeUrl = '')
    {
        $this->term = $term;
        $this->urlToken = $urlToken;
        $this->url = $url;
        $this->count = $count;
        $this->remove_url = $removeUrl;
    }
}
