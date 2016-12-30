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

class CreateCustomerCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('cart:create:customer')
            ->setDescription('Create Customer')
            ->addArgument('email', InputArgument::REQUIRED, 'Email Address')
            ->addArgument('password', InputArgument::REQUIRED, 'Password')
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
        $password = $input->getArgument('password');

        $entityService = $this->getContainer()->get('cart.entity');
        $customer = $entityService->findOneBy(EntityConstants::CUSTOMER, [
            'email' => $email,
        ]);

        if (!$customer) {
            $customer = $entityService->getInstance(EntityConstants::CUSTOMER);
        }

        $encoder = $this->getContainer()->get('security.password_encoder');
        $encoded = $encoder->encodePassword($customer, $password);

        $customer->setEmail($email)
            ->setHash($encoded)
            ->setIsEnabled(1);

        $variantSet = $entityService->findOneBy(EntityConstants::ITEM_VAR_SET, [
            'object_type' => EntityConstants::PRODUCT,
        ]);

        if ($variantSet) {
            $customer->setItemVarSet($variantSet);
        }

        $entityService->persist($customer);

        $message = "Created Customer ({$customer->getId()}): {$email} / {$password} : {$encoded}";
        $output->writeln($message);
    }

}
