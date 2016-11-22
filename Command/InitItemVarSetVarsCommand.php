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

class InitItemVarSetVarsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('cart:init:variants')
            ->setDescription('Create Default Variants for each Item Type')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command manages creating default Variants for each Item Type:

<info>php %command.full_name%</info>

EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // NOTE: This command is meant to be an example
        // It is Not Required to run this command during installation
        // You should copy/paste this to your own bundle
        //  for automating your own custom installations

        $entityService = $this->getContainer()->get('cart.entity');

        $itemVars = [
            EntityConstants::CONTENT => [
                [
                    'code' => 'subcategory',
                    'label' => 'Sub Category',
                    'datatype' => EntityConstants::VARCHAR,
                    'url_key' => 'subcategory',
                    'form_input' => 'text',
                ],
                [
                    'code' => 'content_type',
                    'label' => 'Content Type',
                    'datatype' => EntityConstants::VARCHAR,
                    'url_key' => 'content_type',
                    'form_input' => 'select',
                    'options' => [
                        [
                            'url_value' => 'blog',
                            'value' => 'blog',
                        ],
                        [
                            'url_value' => 'social',
                            'value' => 'social'
                        ],
                        [
                            'url_value' => 'gallery',
                            'value' => 'gallery',
                        ]
                    ]
                ],
            ],
        ];

        foreach($itemVars as $objectType => $objectVars) {

            $varSet = $entityService->findOneBy(EntityConstants::ITEM_VAR_SET, [
                'object_type' => $objectType,
            ]);

            foreach($objectVars as $varData) {

                $code = $varData['code'];
                $label = $varData['label'];
                $dataType = $varData['datatype'];
                $urlKey = $varData['url_key'];
                $formInput = $varData['form_input'];

                // check if it's already there
                $itemVar = $entityService->findOneBy(EntityConstants::ITEM_VAR, [
                    'code' => $code,
                ]);

                if (!$itemVar) {
                    $itemVar = $entityService->getInstance(EntityConstants::ITEM_VAR);

                    $itemVar->setName($label)
                        ->setCode($code)
                        ->setDatatype($dataType)
                        ->setUrlToken($urlKey)
                        ->setFormInput($formInput);

                    $entityService->persist($itemVar);
                    $output->writeln("Created ItemVar for '{$code}' ");
                } else {
                    $output->writeln("ItemVar '{$code}' : row found");
                }

                $varSetVar = $entityService->findOneBy(EntityConstants::ITEM_VAR_SET_VAR, [
                    'item_var_set' => $varSet->getId(),
                    'item_var' => $itemVar->getId()
                ]);

                if (!$varSetVar) {
                    $varSetVar = $entityService->getInstance(EntityConstants::ITEM_VAR_SET_VAR);
                    $varSetVar->setItemVar($itemVar)
                        ->setItemVarSet($varSet);

                    $entityService->persist($varSetVar);

                    $message = "Created ItemVar: {$label} for Object Type: {$objectType}";
                    $output->writeln($message);
                } else {
                    $output->writeln("ItemVar: {$label} for Object Type: {$objectType} : row found");
                }

                if (isset($varData['options'])) {
                    $options = $varData['options'];
                    foreach($options as $optionData) {
                        $option = $entityService->findOneBy(EntityConstants::ITEM_VAR_OPTION_VARCHAR, [
                            'item_var' => $itemVar->getId(),
                            'value' => $optionData['url_value']
                        ]);

                        if(!$option) {
                            $option = $entityService->getInstance(EntityConstants::ITEM_VAR_OPTION_VARCHAR);
                            $option->setItemVar($itemVar)
                                ->setUrlValue($optionData['url_value'])
                                ->setValue($optionData['value']);

                            $entityService->persist($option);
                            $message = "Added Option: " . $optionData['value'];
                            $output->writeln($message);
                        } else {
                            $output->writeln("Option: {$optionData['value']} : row found");
                        }
                    }
                }
            }
        }
    }
}
