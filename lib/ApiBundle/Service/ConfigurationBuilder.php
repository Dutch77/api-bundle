<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 11. 3. 2019
 * Time: 11:57
 */

namespace Asyf\ApiBundle\Service;

use Asyf\ApiBundle\Configuration\FieldConfiguration;
use Asyf\ApiBundle\Configuration\IterableFieldConfiguration;
use Asyf\ApiBundle\Configuration\ObjectFieldConfiguration;
use Asyf\ApiBundle\Configuration\PersistentCollectionFieldConfiguration;
use Asyf\ApiBundle\Configuration\ScalarFieldConfiguration;
use Asyf\ApiBundle\Exception\Configuration\MissingEntityConfigurationException;
use Asyf\ApiBundle\Exception\Normalizer\UnexpectedValueTypeException;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Asyf\ApiBundle\Exception\Configuration\MissingConfigurationOptionException;
use Asyf\ApiBundle\Exception\Configuration\OrderBy\UnsupportedOrderDirectionException;
use Asyf\ApiBundle\Exception\Configuration\OrderBy\NotMappedFieldException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Acl\Util\ClassUtils;

class ConfigurationBuilder
{
    /**
     * @var RegistryInterface
     */
    protected $doctrine;
    /**
     * @var array
     */
    protected $apiConfiguration;

    /**
     * ConfigurationBuilder constructor.
     *
     * @param RegistryInterface $doctrine
     * @param array $defaultConfiguration
     */
    public function __construct(RegistryInterface $doctrine, array $defaultConfiguration)
    {
        $this->doctrine = $doctrine;
        $this->apiConfiguration = $defaultConfiguration;
    }

    /**
     * @param mixed|iterable|object|PersistentCollection $value
     * @param array $extraConfigurations
     *
     * @return FieldConfiguration
     * @throws MissingConfigurationOptionException
     * @throws MissingEntityConfigurationException
     * @throws UnexpectedValueTypeException
     * @throws UnsupportedOrderDirectionException
     * @throws NotMappedFieldException
     */
    public function buildConfiguration($value, array $extraConfigurations = []): FieldConfiguration
    {
        $configurations = [];

        switch (true) {
            case is_scalar($value):
                return new ScalarFieldConfiguration($value, $extraConfigurations);
            case is_iterable($value):
                switch (true) {
                    case $value instanceof PersistentCollection:
                        $configurations[] = $this->getEntityConfiguration($value->getTypeClass()->getName());
                        foreach ($extraConfigurations as $extraConfiguration) {
                            $configurations[] = $extraConfiguration;
                        }
                        return new PersistentCollectionFieldConfiguration($value, $configurations);
                    default:
                        if ($type = $this->guessIterableType($value)) {
                            $configurations[] = $this->getEntityConfiguration($type);
                        }
                        foreach ($extraConfigurations as $extraConfiguration) {
                            $configurations[] = $extraConfiguration;
                        }
                        return new IterableFieldConfiguration($value, $configurations);
                }
            case is_object($value):
                $className = $this->getObjectClass($value);
                // add default entity configuration
                $configurations[] = $this->getEntityConfiguration($className);
                // add extra configuration from builder
                // add extra configuration from query
                // add any other source
                foreach ($extraConfigurations as $extraConfiguration) {
                    $configurations[] = $extraConfiguration;
                }
                return new ObjectFieldConfiguration($value, $configurations);
            default:
                throw new UnexpectedValueTypeException(sprintf('Value type is not supported. Check stack trace.'));
        }
    }

    /**
     * @param string $className
     *
     * @return ClassMetadata
     */
    protected function getClassMetaData(string $className): ClassMetadata
    {
        $em = $this->doctrine->getEntityManagerForClass($className);
        return $em->getClassMetadata($className);
    }

    /**
     * @param string $className
     *
     * @return array
     * @throws MissingEntityConfigurationException
     */
    protected function getEntityConfiguration(string $className): array
    {
        if (isset($this->apiConfiguration['entities'][$className])) {
            return $this->apiConfiguration['entities'][$className];
        }
        throw new MissingEntityConfigurationException($className);
    }

    /**
     * @param iterable $value
     *
     * @return string|null
     */
    protected function guessIterableType(iterable $value): ?string
    {
        $type = null;
        foreach ($value as $item) {
            $type = $this->getObjectClass($item);
            break;
        }
        return $type;
    }

    /**
     * @param $object
     *
     * @return string|null
     */
    protected function getObjectClass($object): ?string
    {
        return is_object($object) ? ClassUtils::getRealClass($object) : null;
    }
}