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

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestSearchCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('cart:test:search')
            ->setDescription('Test Search')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command tests Search:

<info>php %command.full_name%</info>

EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $productIds = [1,2,3,4,5];
        $result = $this->getContainer()->get('cart.search')
            ->init('product')
            ->addAdvFilter(['field' => 'id', 'op' => 'in', 'value' => $productIds])
            ->search()->getResult()
        ;

        if ($result['entities']) {
            foreach($result['entities'] as $row) {
                $message = "Sku: " . $row['sku'];
                $output->writeln($message);
            }
        }
    }

}
