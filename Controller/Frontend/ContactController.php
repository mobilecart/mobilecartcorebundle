<?php

namespace MobileCart\CoreBundle\Controller\Frontend;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ContactController extends Controller
{
    public function indexAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request);
        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTACT_FORM, $event);

        $returnData = $event->getReturnData();
        $form = $returnData['form'];

        $returnData['form'] = $form->createView();
        $returnData['user'] = $this->getUser();
        $returnData['recaptcha_key'] = trim($this->getParameter('recaptcha.key.site'));

        // render template
        return $this->get('cart.theme')
            ->render('frontend', 'Contact:index.html.twig', $returnData);
    }

    public function postAction(Request $request)
    {
        // build form
        $event = new CoreEvent();
        $event->setRequest($request);
        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTACT_FORM, $event);

        $returnData = $event->getReturnData();
        $form = $returnData['form'];

        // validate
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

            $emailEvent = new CoreEvent();
            $emailEvent->setFormData($formData)
                ->setRequest($request);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CONTACT_FORM_POST, $emailEvent);

            return $emailEvent->getResponse();
        }

        // redirect
        return $this->redirectToRoute('cart_contact', []);
    }

    public function thankyouAction(Request $request)
    {
        // render template
        return $this->get('cart.theme')
            ->render('frontend', 'Contact:thankyou.html.twig', []);
    }
}
