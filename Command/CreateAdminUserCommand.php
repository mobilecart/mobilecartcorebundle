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
use MobileCart\CoreBundle\Constants\EntityConstants;

class CreateAdminUserCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('cart:create:adminuser')
            ->setDescription('Create Admin User')
            ->addArgument('email', InputArgument::REQUIRED, 'Email Address')
            ->addArgument('password', InputArgument::REQUIRED, 'Password')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command creates an Admin User:

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
        $password = $input->getArgument('password');

        $entityService = $this->getContainer()->get('cart.entity');
        $adminUser = $entityService->findOneBy(EntityConstants::ADMIN_USER, [
            'email' => $email,
        ]);

        if (!$adminUser) {
            $adminUser = $entityService->getInstance(EntityConstants::ADMIN_USER);
        }

        $encoder = $this->getContainer()->get('security.password_encoder');
        $encoded = $encoder->encodePassword($adminUser, $password);

        $adminUser->setEmail($email)
            ->setHash($encoded)
            ->setIsEnabled(1);

        $entityService->persist($adminUser);

        $message = "Created AdminUser ({$adminUser->getId()}): {$email} / {$password} : {$encoded}";
        $output->writeln($message);
    }

}
