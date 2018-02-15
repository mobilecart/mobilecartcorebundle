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
     * @var bool
     */
    protected $isSinglePage = false;

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
     * @param bool $yesNo
     * @return $this
     */
    public function setAllowGuestCheckout($yesNo)
    {
        $this->allowGuestCheckout = $yesNo;
        return $this;
    }

    /**
     * @return bool
     */
    public function getAllowGuestCheckout()
    {
        return $this->allowGuestCheckout;
    }

    /**
     * @param $isEnabled
     * @return $this
     */
    public function setIsSinglePage($isEnabled)
    {
        $this->isSinglePage = (bool) $isEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsSinglePage()
    {
        return (bool) $this->isSinglePage;
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
     * @param array $sections
     * @return array
     */
    public function sortFormSections(array $sections)
    {
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
            return $newSections;
        }

        return $sections;
    }

    /**
     * @return $this
     */
    public function collectFormSections()
    {
        $event = new CoreEvent();
        $event->setUser($this->getUser());

        if ($this->getRequest()) {
            $event->setRequest($this->getRequest());
        }

        $this->getEventDispatcher()
            ->dispatch(CoreEvents::CHECKOUT_FORM, $event);

        $this->event = $event;

        $sections = $event->getReturnData('sections');
        $this->formSections = $this->sortFormSections($sections);

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
     * This is used when the checkout is configured for multiple pages eg isSinglePage = false
     *
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
     * This is used when the checkout is configured for a single page eg isSinglePage = true
     *
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
     * This is used for tracking which steps are valid, and is useful in other areas of checkout also
     *  such as the javascript
     *
     * @return array
     */
    public function getFormSectionKeys()
    {
        if (!$this->formSections) {
            $this->collectFormSections();
        }
        return array_keys($this->formSections);
    }

    /**
     * This is generally used for rendering the first step of the form when isSinglePage = false
     *
     * @return string
     */
    public function getFirstFormSectionKey()
    {
        $keys = $this->getFormSectionKeys();
        return isset($keys[0])
            ? $keys[0]
            : '';
    }
}
