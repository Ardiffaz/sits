<?php

namespace App\Operation;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

abstract class BaseOperation
{
    /** @var EntityManagerInterface */
    protected $em;

    /** @var SerializerInterface */
    protected $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->em = $entityManager;
        $this->serializer = $serializer;
    }

    abstract public function execute();

}