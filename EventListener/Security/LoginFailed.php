<?php

namespace MobileCart\CoreBundle\EventListener\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\ParameterBagUtils;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;

class LoginFailed implements AuthenticationFailureHandlerInterface
{

    const MAX_FAILED_LOGINS = 10;

    protected $httpKernel;
    protected $httpUtils;
    protected $logger;
    protected $options;
    protected $defaultOptions = array(
        'failure_path' => null,
        'failure_forward' => false,
        'login_path' => '/login',
        'failure_path_parameter' => '_failure_path',
    );

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    protected $entityService;

    /**
     * Constructor.
     *
     * @param HttpKernelInterface $httpKernel
     * @param HttpUtils           $httpUtils
     * @param array               $options    Options for processing a failed authentication attempt.
     * @param LoggerInterface     $logger     Optional logger
     */
    public function __construct(HttpKernelInterface $httpKernel, HttpUtils $httpUtils, array $options = array(), LoggerInterface $logger = null)
    {
        $this->httpKernel = $httpKernel;
        $this->httpUtils = $httpUtils;
        $this->logger = $logger;
        $this->setOptions($options);
    }

    /**
     * @param \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     * @return $this
     */
    public function setEntityService(\MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface $entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

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
     * Gets the options.
     *
     * @return array An array of options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets the options.
     *
     * @param array $options An array of options
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->defaultOptions, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($failureUrl = ParameterBagUtils::getRequestParameterValue($request, $this->options['failure_path_parameter'])) {
            $this->options['failure_path'] = $failureUrl;
        }

        if (null === $this->options['failure_path']) {
            $this->options['failure_path'] = $this->options['login_path'];
        }

        $event = new CoreEvent();
        $event->addErrorMessage('Login Failed');

        $username = $request->get('_username', '');
        if ($username) {

            $user = $this->getEntityService()->findOneBy(EntityConstants::CUSTOMER, [
                'email' => $username
            ]);

            if (!$user) {

                $user = $this->getEntityService()->findOneBy(EntityConstants::ADMIN_USER, [
                    'email' => $username
                ]);
            }

            if ($user) {
                $failedLogins = (int) $user->getFailedLogins();
                $failedLogins++;
                if ($failedLogins >= self::MAX_FAILED_LOGINS && !$user->getIsLocked()) {

                    $user->setIsLocked(true)
                        ->setApiKey('')
                        ->setLockedAt(new \DateTime('now'));

                    $event->addWarningMessage('The account has been temporarily locked');

                    // observe event, possibly send an email

                    $event->setUser($user);

                    $this->getEventDispatcher()
                        ->dispatch(CoreEvents::LOGIN_LOCKED, $event);

                    // todo : un-lock users via cron script, also expire passwords
                }

                $user->setFailedLogins($failedLogins);

                try {
                    $this->getEntityService()->persist($user);
                } catch(\Exception $e) {
                    $event->addErrorMessage('An error occurred while saving the User');
                }
            }
        }

        if ($request->headers->get('Accept') == 'application/json') {

            return new JsonResponse([
                'success' => false,
                'messages' => $event->getMessages(),
            ]);
        }

        if ($this->options['failure_forward']) {
            if (null !== $this->logger) {
                $this->logger->debug('Authentication failure, forward triggered.', array('failure_path' => $this->options['failure_path']));
            }

            $subRequest = $this->httpUtils->createRequest($request, $this->options['failure_path']);
            $subRequest->attributes->set(Security::AUTHENTICATION_ERROR, $exception);

            return $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

        if (null !== $this->logger) {
            $this->logger->debug('Authentication failure, redirect triggered.', array('failure_path' => $this->options['failure_path']));
        }

        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);

        return $this->httpUtils->createRedirectResponse($request, $this->options['failure_path']);
    }
}
