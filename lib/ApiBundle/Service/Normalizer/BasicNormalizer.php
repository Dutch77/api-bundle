<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 11. 3. 2019
 * Time: 14:59
 */

namespace Asyf\ApiBundle\Service\Normalizer;

use Asyf\ApiBundle\Configuration\FieldConfigurationInterface;
use Asyf\ApiBundle\Configuration\IterableFieldConfiguration;
use Asyf\ApiBundle\Configuration\ObjectFieldConfiguration;
use Asyf\ApiBundle\Configuration\PersistentCollectionFieldConfiguration;
use Asyf\ApiBundle\Configuration\ScalarFieldConfiguration;
use Asyf\ApiBundle\Exception\Normalizer\UnexpectedValueTypeException;
use Asyf\ApiBundle\Exception\Normalizer\NoGetterMethodAvailableException;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;
use Asyf\ApiBundle\Exception\Configuration\MissingConfigurationOptionException;
use Asyf\ApiBundle\Exception\Configuration\OrderBy\UnsupportedOrderDirectionException;
use Asyf\ApiBundle\Exception\Configuration\MissingEntityConfigurationException;
use Asyf\ApiBundle\Exception\Normalizer\NormalizerNotFoundException;
use Asyf\ApiBundle\Exception\Configuration\OrderBy\NotMappedFieldException;
use Asyf\ApiBundle\Exception\Configuration\Conditions\AmbiguousConditionException;
use Asyf\ApiBundle\Exception\Configuration\Conditions\MissingConditionKeyException;
use Asyf\ApiBundle\Exception\Configuration\Conditions\UnsupportedConditionOperatorException;
use Asyf\ApiBundle\Exception\Configuration\Conditions\UnsupportedConditionTypeException;

class BasicNormalizer extends AbstractNormalizer
{
    /**
     * @param $value
     * @param FieldConfigurationInterface $fieldConfiguration
     * @param int $maxDepth
     * @param int $currentDepth
     *
     * @return array|mixed
     * @throws AmbiguousConditionException
     * @throws MissingConditionKeyException
     * @throws MissingConfigurationOptionException
     * @throws MissingEntityConfigurationException
     * @throws NoGetterMethodAvailableException
     * @throws NormalizerNotFoundException
     * @throws NotMappedFieldException
     * @throws UnexpectedValueTypeException
     * @throws UnsupportedConditionOperatorException
     * @throws UnsupportedConditionTypeException
     * @throws UnsupportedOrderDirectionException
     */
    public function normalizeResource($value, FieldConfigurationInterface $fieldConfiguration, int $maxDepth, int $currentDepth)
    {
        switch (true) {
            case is_scalar($value):
                return $this->normalizeScalar($value, $fieldConfiguration, $maxDepth, $currentDepth);
            case is_iterable($value):
                return $this->normalizeIterable($value, $fieldConfiguration, $maxDepth, $currentDepth);
            case is_object($value):
                return $this->normalizeObject($value, $fieldConfiguration, $maxDepth, $currentDepth);
            case is_null($value):
                return null;
            default:
                throw new UnexpectedValueTypeException(sprintf('Value type is not supported. Check stack trace.'));
        }
    }

    /**
     * @param $value
     * @param FieldConfigurationInterface|ScalarFieldConfiguration $fieldConfiguration
     * @param int $maxDepth
     * @param int $currentDepth
     *
     * @return bool|int|string
     * @throws NormalizerNotFoundException
     */
    protected function normalizeScalar($value, FieldConfigurationInterface $fieldConfiguration, int $maxDepth, int $currentDepth)
    {
        $normalizer = $this->getNormalizer($fieldConfiguration->getNormalizerName());
        if ($this === $normalizer) {
            return $value;
        } else {
            return $normalizer->normalize($value, $fieldConfiguration, $maxDepth, $currentDepth);
        }
    }

    /**
     * @param iterable|Collection|PersistentCollection $value
     * @param FieldConfigurationInterface|IterableFieldConfiguration|PersistentCollectionFieldConfiguration $fieldConfiguration
     * @param int $maxDepth
     * @param int $currentDepth
     *
     * @return array
     * @throws AmbiguousConditionException
     * @throws MissingConditionKeyException
     * @throws MissingConfigurationOptionException
     * @throws MissingEntityConfigurationException
     * @throws NormalizerNotFoundException
     * @throws NotMappedFieldException
     * @throws UnexpectedValueTypeException
     * @throws UnsupportedConditionOperatorException
     * @throws UnsupportedConditionTypeException
     * @throws UnsupportedOrderDirectionException
     */
    protected function normalizeIterable(iterable $value, IterableFieldConfiguration $fieldConfiguration, int $maxDepth, int $currentDepth): array
    {
        if ($value instanceof PersistentCollection) {
            $value = $value->matching($fieldConfiguration->getCriteria());
        }

        $output = [];
        foreach ($value as $child) {
            $normalizer = $this->getNormalizer($fieldConfiguration->getNormalizerName());
            if ($fieldConfiguration->getSameType()) {
                $output[] = $normalizer->normalize($child, $fieldConfiguration, $maxDepth, $currentDepth);
            } else {
                $childFieldConfiguration = $this->configurationBuilder->buildConfiguration($child);
                $output[] = $normalizer->normalize($child, $childFieldConfiguration, $maxDepth, $currentDepth);
            }
        }
        return $output;
    }

    /**
     * @param $value
     * @param FieldConfigurationInterface|ObjectFieldConfiguration|IterableFieldConfiguration|PersistentCollectionFieldConfiguration $fieldConfiguration
     * @param int $maxDepth
     * @param int $currentDepth
     *
     * @return mixed
     * @throws MissingConfigurationOptionException
     * @throws MissingEntityConfigurationException
     * @throws NoGetterMethodAvailableException
     * @throws NormalizerNotFoundException
     * @throws UnexpectedValueTypeException
     * @throws UnsupportedOrderDirectionException
     * @throws NotMappedFieldException
     */
    protected function normalizeObject($value, FieldConfigurationInterface $fieldConfiguration, int $maxDepth, int $currentDepth)
    {
        $normalizedObject = [];
        try {
            $nestedRawFieldsConfiguration = $fieldConfiguration->getRawNestedFieldsConfiguration();
        } catch (MissingConfigurationOptionException $exception) {
            $nestedRawFieldsConfiguration = null;
        }
        if ($nestedRawFieldsConfiguration) {
            foreach ($nestedRawFieldsConfiguration as $fieldName => $fieldConfiguration) {
                $propertyValue = $this->getPropertyValue($value, $fieldName);
                if ($propertyValue !== null) {
                    $nestedFieldConfiguration = $this->configurationBuilder->buildConfiguration($propertyValue, [$fieldConfiguration]);
                    if ($nestedFieldConfiguration->isExposed()) {
                        $normalizer = $this->getNormalizer($nestedFieldConfiguration->getNormalizerName());
                        $normalizedObject[$fieldName] = $normalizer->normalize($propertyValue, $nestedFieldConfiguration, $maxDepth, $currentDepth);
                    }
                } else {
                    $normalizedObject[$fieldName] = null;
                }
            }
        } else {
            $normalizer = $this->getNormalizer($fieldConfiguration->getNormalizerName());
            if ($this === $normalizer) {
                $normalizedObject = (string)$value;
            } else {
                $normalizedObject = $normalizer->normalize($value, $fieldConfiguration, $maxDepth, $currentDepth);
            }
        }
        return $normalizedObject;
    }
}