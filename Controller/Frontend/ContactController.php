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

        $event->setReturnData('form', $event->getReturnData('form')->createView());
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
        $form = $event->getReturnData('form');
        if ($form->handleRequest($request)->isValid()) {

            // validate recaptcha
            $recaptchaKey = trim($this->getParameter('recaptcha.key.site'));
            if ($recaptchaKey && $request->get('g-recaptcha-response', '')) {
                if (!$this->get('cart.recaptcha')->isValid($request->get('g-recaptcha-response'))) {
                    // redirect
                    return $this->redirectToRoute('cart_contact', []);
                }
            }

            $formData = $request->request->get($form->getName());

            $event->setFormData($formData);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CONTACT_FORM_POST, $event);

            return $event->getResponse();
        }

        if ($request->getSession() && $event->getMessages()) {
            foreach($event->getMessages() as $code => $messages) {
                if (!$messages) {
                    continue;
                }
                foreach($messages as $message) {
                    $request->getSession()->getFlashBag()->add($code, $message);
                }
            }
        }

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
