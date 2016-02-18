<?php

namespace MobileCart\CoreBundle\Search;

class Facet
{
    public $label;
    public $urlToken;
    public $isActive;
    public $terms = array();
    public $total;

    public function __construct($label = '', $urlToken = '', $isActive = 0, $terms = array(), $total = 0)
    {
        $this->label = $label;
        $this->urlToken = $urlToken;
        $this->isActive = $isActive;
        $this->terms = $terms;
        $this->total = $total;
    }

    public function addTerm(FacetTerm $term)
    {
        $this->terms[] = $term;
        return $this;
    }

    public function getTerms()
    {
        return $this->terms;
    }
}
