<?php

namespace MobileCart\CoreBundle\Controller\Frontend;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ContactController
 * @package MobileCart\CoreBundle\Controller\Frontend
 */
class ContactController extends Controller
{
    /**
     * Display form
     */
    public function indexAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request);
        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTACT_FORM, $event);

        $event->setReturnData('form', $event->getForm()->createView());
        $event->setReturnData('user', $this->getUser());
        $event->setReturnData('recaptcha_key', trim($this->getParameter('recaptcha.key.site')));

        return $this->get('cart.theme')->render('frontend', 'Contact:index.html.twig', $event->getReturnData());
    }

    /**
     * Handle form submission
     */
    public function postAction(Request $request)
    {
        // build form
        $event = new CoreEvent();
        $event->setRequest($request);
        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTACT_FORM, $event);

        // validate
        if ($event->isFormValid()) {

            // validate recaptcha
            $recaptchaKey = trim($this->getParameter('recaptcha.key.site'));
            if ($recaptchaKey && $request->get('g-recaptcha-response', '')) {
                if (!$this->get('cart.recaptcha')->isValid($request->get('g-recaptcha-response'))) {
                    // redirect
                    return $this->redirectToRoute('cart_contact', []);
                }
            }

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CONTACT_FORM_POST, $event);

            return $event->getResponse();
        }

        $event->flashMessages();

        // redirect
        return $this->redirectToRoute('cart_contact', []);
    }

    /**
     * Display confirmation message
     */
    public function thankyouAction(Request $request)
    {
        return $this->get('cart.theme')->render('frontend', 'Contact:thankyou.html.twig', []);
    }
}
