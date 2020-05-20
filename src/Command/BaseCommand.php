<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class BaseCommand extends Command
{

    /** @var EntityManagerInterface */
    protected $em;

    /** @var SymfonyStyle */
    protected $ss;

    abstract protected function proceed(InputInterface $input, OutputInterface $output);


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ss = new SymfonyStyle($input, $output);

        $returnCode = $this->prepare($input, $output);

        if (false !== $returnCode) {
            $returnCode = $this->proceed($input, $output);
        }

        return $returnCode;
    }

    protected function prepare(InputInterface $input, OutputInterface $output) : ?bool
    {
        return null;
    }

    public function setEntityManager(EntityManagerInterface $em)
    {
        $this->em = $em;
        return $this;
    }
}
