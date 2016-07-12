<?php

namespace KittenCovenBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class KittenCovenHellfireCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('kitten-coven:hellfire')
            ->setDescription('...')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $argument = $input->getArgument('argument');

        if ($input->getOption('option')) {
            // ...
        }
        $apiConsumer = $this->getContainer()->get('kitten_coven.black_grimoire');
        $apiConsumer->summonBlackGrimoire();

        $output->writeln('Command result.');
    }

}
