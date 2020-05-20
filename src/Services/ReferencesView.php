<?php

namespace App\Services;

use ReflectionClass;

class ReferencesView
{
    /** @var array */
    private $refs;

    /** @var array */
    private $renameSchema;

    public function __construct(array $refs, array $renameSchema = [])
    {
        $this->refs = $refs;
        $this->renameSchema = $renameSchema;
    }

    public static function from(array $refs, array $renameSchema = [])
    {
        return new self($refs, $renameSchema);
    }

    public function build()
    {
        return array_combine(
            array_map(
                function (string $entityClass) {
                    return empty($this->renameSchema[$entityClass])
                        ? lcfirst((new ReflectionClass($entityClass))->getShortName()).'s'
                        : $this->renameSchema[$entityClass];
                },
                array_keys($this->refs)
            ),
            array_map(function (array $classRefs) {
                return array_values($classRefs);
            }, $this->refs)
        );
    }
}