<?php

namespace App\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

class ReferenceExtractor
{
    /** @var PropertyAccessorInterface */
    private $propertyAccessor;

    /** @var PropertyInfoExtractorInterface */
    private $propertyInfoExtractor;

    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        PropertyInfoExtractorInterface $propertyInfoExtractor
    ) {
        $this->propertyAccessor = $propertyAccessor;
        $this->propertyInfoExtractor = $propertyInfoExtractor;
    }

    public function extract(iterable $entities, array $targets, &$refs = [], &$processedEntities = [])
    {
        $targetClasses = array_keys($targets);

        $refs += array_fill_keys($targetClasses, []);

        foreach ($entities as $entity) {
            $entityClass = get_class($entity);

            $entityHash = spl_object_hash($entity);
            if (in_array($entityHash, $processedEntities)) {
                continue;
            }

            $processedEntities[] = $entityHash;

            $targetProperties = null;
            foreach (array_keys($targets) as $targetClass) {
                if (is_a($entityClass, $targetClass,  true)) {
                    $targetProperties = $targets[$targetClass];
                    break;
                }
            }

            if (!$targetProperties) {
                continue;
            }

            foreach ($targetProperties as $targetProperty) {
                foreach ($this->propertyInfoExtractor->getTypes($entityClass, $targetProperty) as $propertyType) {
                    $isCollection = $propertyType->isCollection();

                    $valueClass = $isCollection
                        ? $propertyType->getCollectionValueType()->getClassName()
                        : $propertyType->getClassName();

                    if (!$valueClass) {
                        continue;
                    }

                    $propertyValue = $this->propertyAccessor->getValue($entity, $targetProperty);
                    if ($propertyValue === null) {
                        continue;
                    }

                    if ($isCollection) {
                        $nextCycleEntities = ($propertyValue instanceof ArrayCollection || $propertyValue instanceof PersistentCollection)
                            ? $propertyValue->toArray()
                            : $propertyValue;

                        $refs[$valueClass] += array_combine(
                            array_map(function ($object) {
                                return spl_object_hash($object);
                            }, $nextCycleEntities),
                            $nextCycleEntities
                        );


                    } else {
                        $nextCycleEntities = [ $propertyValue ];
                        $refs[$valueClass][spl_object_hash($propertyValue)] = $propertyValue;
                    }

                    $this->extract($nextCycleEntities, $targets, $refs, $processedEntities);
                }
            }
        }

        return $refs;
    }
}
