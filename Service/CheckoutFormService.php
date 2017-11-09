<?php

namespace MobileCart\CoreBundle\Service;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;

/**
 * Class CheckoutFormService
 * @package MobileCart\CoreBundle\Service
 */
class CheckoutFormService
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var CoreEvent
     */
    protected $event;

    /**
     * @var array
     */
    protected $formSections = [];

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Symfony\Component\Security\Core\User\UserInterface
     */
    protected $user;

    /**
     * @param $eventDispatcher
     * @return $this
     */
    public function setEventDispatcher($eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return $this
     */
    public function setRequest(\Symfony\Component\HttpFoundation\Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @return $this
     */
    public function setUser(\Symfony\Component\Security\Core\User\UserInterface $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return CoreEvent
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return $this
     */
    public function collectFormSections()
    {
        $event = new CoreEvent();
        $event->setRequest($this->getRequest())
            ->setUser($this->getUser());
        $this->getEventDispatcher()
            ->dispatch(CoreEvents::CHECKOUT_FORM, $event);

        $this->event = $event;

        $sections = $event->getReturnData('sections');
        if ($sections) {

            // ensure the steps are properly ordered
            $newSections = [];
            $sectionOrder = [];
            foreach($sections as $section => $sectionData) {
                // set aside the order, for re-ordering
                $sectionOrder[$section] = $sections[$section]['step_number'];
            }

            // crude/quick sort and order the checkout steps
            $sectionOrder = array_flip($sectionOrder);
            ksort($sectionOrder);
            $sectionOrder = array_values($sectionOrder);

            $x = 1;
            $lastSection = '';
            foreach($sectionOrder as $section) {

                $newSections[$section] = $sections[$section];
                if (strlen($lastSection)) {
                    $newSections[$lastSection]['next_section'] = $section;
                }

                $lastSection = $section;
                $x++;
            }
            $newSections[$lastSection]['final_step'] = true;
            $this->formSections = $newSections;
        }

        return $this;
    }

    /**
     * @param $section
     * @return array|mixed
     * @throws \InvalidArgumentException
     */
    public function getFormSection($section)
    {
        if (!$this->formSections) {
            $this->collectFormSections();
        }

        if (!isset($this->formSections[$section])) {
            throw new \InvalidArgumentException("Section not found: {$section}");
        }

        return $this->formSections[$section];
    }

    /**
     * @return array
     */
    public function getFormSections()
    {
        if (!$this->formSections) {
            $this->collectFormSections();
        }

        return $this->formSections;
    }

    /**
     * @param $section
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getSectionResponse($section)
    {
        $event = new CoreEvent();
        $event->setRequest($this->getRequest())
            ->setUser($this->getUser())
            ->set('single_step', $section);

        $this->getEventDispatcher()
            ->dispatch(CoreEvents::CHECKOUT_FORM, $event);

        $this->event = $event;

        return $event->getResponse();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getFullResponse()
    {
        $event = new CoreEvent();
        $event->setRequest($this->getRequest())
            ->setUser($this->getUser());

        $this->getEventDispatcher()
            ->dispatch(CoreEvents::CHECKOUT_FORM, $event);

        $this->event = $event;

        return $event->getResponse();
    }

    /**
     * @param $section
     * @return array
     */
    public function getSectionData($section)
    {
        if (!$this->formSections) {
            $this->collectFormSections();
        }

        return isset($this->formSections[$section])
            ? $this->formSections[$section]
            : [];
    }

    /**
     * @return array
     */
    public function getSectionKeys()
    {
        if (!$this->formSections) {
            $this->collectFormSections();
        }
        return array_keys($this->formSections);
    }

    /**
     * @return string
     */
    public function getFirstSectionKey()
    {
        $keys = $this->getSectionKeys();
        return isset($keys[0])
            ? $keys[0]
            : '';
    }
}
