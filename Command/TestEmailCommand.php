<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestEmailCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('cart:test:email')
            ->setDescription('Send Test Email')
            ->addArgument('email', InputArgument::REQUIRED, 'Email Address')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command creates a Customer:

<info>php %command.full_name%</info>

EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $mailer = $this->getContainer()->get('mailer');
        $subject = 'Test Email';
        $body = "Hello, this is a test email";

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            //->setFrom('~')
            ->setTo($email)
            ->setBody($body, 'text/html');

        $message = $mailer->send($message)
            ? "Message sent to: {$email}"
            : "Error sending email";

        $output->writeln($message);
    }

}
