<?php

namespace MobileCart\CoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use MobileCart\CoreBundle\Entity\Product;
//use MobileCart\CoreBundle\Entity\ItemVarSet;
//use MobileCart\CoreBundle\Entity\ItemVar;
//use MobileCart\CoreBundle\Entity\ItemVarSetVar;
//use MobileCart\CoreBundle\Entity\ItemVarOption;

class ProductConfigCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('cart:product:reconfigure')
            ->setDescription('Re-Configure the Configurable Products')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command runs configuration on configurable products

<info>php %command.full_name%</info>
EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $products = $em->getRepository('MobileCartCoreBundle:Product')->findBy(array(
            'type' => Product::TYPE_CONFIGURABLE,
        ));

        if (count($products)) {
            foreach($products as $product) {
                $product->reconfigure();
                $em->persist($product);
                $output->writeln("Reconfigure Product: {$product->getName()}");
            }
        }

        $em->flush();
    }
}
