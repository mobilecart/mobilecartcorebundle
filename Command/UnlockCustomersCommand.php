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

class UnlockCustomersCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('cart:customers:unlock')
            ->setDescription('Unlock Customers')
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
        $sql = "update customer set is_locked=0 where 1"; // locked_at >= '{}'
        $connection = $this->getContainer()->get('doctrine')->getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->execute();

        $message = "Unlocked Customers";
        $output->writeln($message);
    }

}
