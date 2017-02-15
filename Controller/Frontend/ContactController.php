<?php

namespace MobileCart\CoreBundle\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ContactController extends Controller
{
    public function indexAction(Request $request)
    {
        // build form
        $formType = new \MobileCart\CoreBundle\Form\ContactFormType();

        $form = $this->createForm($formType, null, [
            'action' => $this->generateUrl('cart_contact_post', []),
            'method' => 'POST'
        ]);

        $returnData = [
            'form' => $form->createView(),
            'user' => $this->getUser(),
            'recaptcha_key' => trim($this->getParameter('recaptcha.key.site'))
        ];

        // render template
        return $this->get('cart.theme')
            ->render('frontend', 'Contact:index.html.twig', $returnData);
    }

    public function postAction(Request $request)
    {
        // build form
        $formType = new \MobileCart\CoreBundle\Form\ContactFormType();
        $form = $this->createForm($formType);
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

            // send email
            $formData = $form->getData();

            $email = $formData['email'];
            $message = $formData['message'];
            $viewData = [
                'email' => $email,
                'message' => $message,
            ];

            // render template

            $body = $this->get('cart.theme')
                ->render('email', 'Email:contact_message.html.twig', $viewData);

            $subject = 'Contact Form Submission';
            $recipient = trim($this->getParameter('cart.email.to.main'));
            $fromEmail = trim($this->getParameter('cart.email.from.main'));

            try {

                $msg = \Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom($fromEmail)
                    ->setTo($recipient)
                    ->setBody($body, 'text/html');

                $this->get('mailer')->send($msg);

            } catch(\Exception $e) {
                // todo : handle error
            }

            // redirect
            return $this->redirectToRoute('cart_contact_thankyou', []);
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
