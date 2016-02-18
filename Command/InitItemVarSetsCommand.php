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

class InitItemVarSetsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('cart:init:itemvarsets')
            ->setDescription('Create Default Variant Sets for each Item Type')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command manages creating default Variant Sets for each Item Type:

<info>php %command.full_name%</info>

EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityService = $this->getContainer()->get('cart.entity');
        foreach (EntityConstants::getEavObjects() as $code => $label) {

            $varSet = $entityService->findOneBy(EntityConstants::ITEM_VAR_SET, [
                'object_type' => $code,
            ]);

            if (!$varSet) {

                $name = "Default {$label}";

                $varSet = $entityService->getInstance(EntityConstants::ITEM_VAR_SET);
                $varSet->setName($name)
                    ->setObjectType($code);

                $entityService->persist($varSet);

                $message = "Created ItemVarSet: {$name} for Object Type: {$code}";
                $output->writeln($message);
            }
        }
    }
}
