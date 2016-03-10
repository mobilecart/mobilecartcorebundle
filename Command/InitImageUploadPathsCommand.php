<?php

namespace MobileCart\CoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MobileCart\CoreBundle\Constants\EntityConstants;

class InitImageUploadPathsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('cart:init:imageupload')
            ->setDescription('Create Image Upload Paths')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command creates Image Upload Paths:

<info>php %command.full_name%</info>

EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $imageService = $this->getContainer()->get('cart.image');
        $uploadPaths = $imageService->getImageUploadPaths();
        if (!$uploadPaths) {
            $message = "No Upload paths ?";
            $output->writeln($message);
        }

        foreach($uploadPaths as $relPath) {
            $path = $this->getContainer()->get('kernel')->getRootDir() . '/../web/' . $relPath;
            if (!is_dir($path)) {
                if (mkdir($path, 0777, true)) {
                    $message = "Created path: " . realpath($path);
                    $output->writeln($message);
                } else {
                    $message = "Could not create directory: " . $path;
                    $output->writeln($message);
                }
            } else {
                $message = "Path exists: " . realpath($path);
                $output->writeln($message);
            }
        }
    }
}
