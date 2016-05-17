<?php

namespace MobileCart\CoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MobileCart\CoreBundle\Constants\EntityConstants;

class LoadSearchTermsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('cart:init:searchterms')
            ->setDescription('Populate search terms with content/category/product titles')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command Populates search terms with content/category/product titles:

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

        $output->writeln("Starting");

        $page = 1;
        $limit = 10;
        $offset = 0;
        while($products = $entityService->findBy(EntityConstants::PRODUCT,
            [
                'is_enabled' => 1,
                'is_public' => 1,
            ],
            [
                'id' => 'asc'
            ],
            $limit,
            $offset
        )) {

            foreach($products as $product) {

                $str  = $product->getName();

                $term = $entityService->findOneBy(EntityConstants::SEARCH_TERM, [
                    'term' => $str,
                ]);

                if (!$term) {
                    $term = $entityService->getInstance(EntityConstants::SEARCH_TERM);
                    $term->setTerm($str)
                        ->setUsageCount(0)
                        ->setResultCount(0)
                        ;
                }

                $output->writeln("Term: {$str}");
                $entityService->persist($term);
            }

            $page++;
            $offset = ($page - 1) * $limit;
        }

        $output->writeln("Finished");
    }
}
