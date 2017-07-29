<?php

namespace MobileCart\CoreBundle\Entity;

use \Doctrine\Common\Collections\Collection;

class RecursiveCategoryIterator implements \RecursiveIterator
{
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return (!$this->data->current()->getChildCategories()->isEmpty());
    }

    /**
     * @return RecursiveCategoryIterator|\RecursiveIterator
     */
    public function getChildren()
    {
        return new RecursiveCategoryIterator($this->data->current()->getChildCategories());
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return $this->data->current();
    }

    public function next()
    {
        $this->data->next();
    }

    public function key()
    {
        return $this->data->key();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->data->current() instanceof Category;
    }

    public function rewind()
    {
        $this->data->first();
    }
}
