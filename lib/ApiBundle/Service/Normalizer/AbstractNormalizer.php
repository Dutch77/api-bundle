<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 11. 3. 2019
 * Time: 14:59
 */

namespace Asyf\ApiBundle\Service\Normalizer;

use Asyf\ApiBundle\Configuration\FieldConfigurationInterface;
use Asyf\ApiBundle\Event\ApiEvent;
use Asyf\ApiBundle\Event\PostNormalizeEvent;
use Asyf\ApiBundle\Event\PreNormalizeEvent;
use Asyf\ApiBundle\Service\ConfigurationBuilder;
use Asyf\ApiBundle\Exception\Normalizer\NoGetterMethodAvailableException;
use Asyf\ApiBundle\Service\NormalizersManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Acl\Util\ClassUtils;
use Asyf\ApiBundle\Exception\Normalizer\NormalizerNotFoundException;
use Asyf\ApiBundle\Exception\Configuration\MissingConfigurationOptionException;
use Asyf\ApiBundle\Exception\Configuration\OrderBy\NotMappedFieldException;
use Asyf\ApiBundle\Exception\Configuration\OrderBy\UnsupportedOrderDirectionException;
use Asyf\ApiBundle\Exception\Configuration\MissingEntityConfigurationException;
use Asyf\ApiBundle\Exception\Normalizer\UnexpectedValueTypeException;

abstract class AbstractNormalizer implements NormalizerInterface
{
    /**
     * @var NormalizersManager
     */
    protected $normalizersManager;
    /**
     * @var ConfigurationBuilder
     */
    protected $configurationBuilder;
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * AbstractNormalizer constructor.
     *
     * @param NormalizersManager $normalizersManager
     * @param ConfigurationBuilder $configurationBuilder
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(NormalizersManager $normalizersManager, ConfigurationBuilder $configurationBuilder, EventDispatcherInterface $eventDispatcher)
    {
        $this->normalizersManager = $normalizersManager;
        $this->configurationBuilder = $configurationBuilder;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param $value
     * @param FieldConfigurationInterface $fieldConfiguration
     * @param int|null $maxDepth
     * @param int|null $currentDepth
     *
     * @return array|mixed
     * @throws MissingConfigurationOptionException
     * @throws MissingEntityConfigurationException
     * @throws NotMappedFieldException
     * @throws UnexpectedValueTypeException
     * @throws UnsupportedOrderDirectionException
     */
    public function normalize($value, ?FieldConfigurationInterface $fieldConfiguration = null, ?int $maxDepth = NormalizerInterface::DEFAULT_MAX_DEPTH, ?int $currentDepth = 0)
    {
        if ($this->eventDispatcher->hasListeners(ApiEvent::API_EVENT__PRE_NORMALIZE)) {
            $event = new PreNormalizeEvent($value);
            $this->eventDispatcher->dispatch(ApiEvent::API_EVENT__PRE_NORMALIZE, $event);
            $value = $event->getValue();
        }

        $normalizedValue = null;

        if ($value !== null) {
            if (!$fieldConfiguration) {
                $fieldConfiguration = $this->configurationBuilder->buildConfiguration($value);
            }

            $currentDepth = $currentDepth === null ? 0 : $currentDepth;
            $maxDepth = $maxDepth === null ? NormalizerInterface::DEFAULT_MAX_DEPTH : $maxDepth;

            $currentDepth++;
            if ($fieldMaxDepth = $fieldConfiguration->getMaxDepth()) {
                $fieldMaxDepth += $currentDepth;
                $maxDepth = min($fieldMaxDepth, $maxDepth);
            }

            if ($currentDepth > $maxDepth) {
                switch ($value) {
                    case is_scalar($value);
                        $normalizedValue = $value;
                        break;
                    case is_iterable($value):
                        $normalizedValue = sprintf('-- max depth limit (%s) --', ($currentDepth - 1));
                        break;
                    default:
                        $normalizedValue = (string)$value;
                        break;
                }
            } else {
                $normalizedValue = $this->normalizeResource($value, $fieldConfiguration, $maxDepth, $currentDepth);
            }
        }

        if ($this->eventDispatcher->hasListeners(ApiEvent::API_EVENT__POST_NORMALIZE)) {
            $event = new PostNormalizeEvent($normalizedValue, $value);
            $this->eventDispatcher->dispatch(ApiEvent::API_EVENT__POST_NORMALIZE, $event);
            $normalizedValue = $event->getNormalizedValue();
        }

        return $normalizedValue;
    }

    /**
     * @param $value
     * @param FieldConfigurationInterface $fieldConfiguration
     * @param int $maxDepth
     * @param int $currentDepth
     *
     * @return mixed
     */
    abstract public function normalizeResource($value, FieldConfigurationInterface $fieldConfiguration, int $maxDepth, int $currentDepth);

    /**
     * @param string $propertyName
     * @param $object
     *
     * @return mixed
     * @throws NoGetterMethodAvailableException
     */
    protected function getPropertyValue($object, string $propertyName)
    {
        $prefixes = ['get', 'is'];
        $guessMethods = [];
        foreach ($prefixes as $prefix) {
            $guessMethods[] = sprintf('%s%s', $prefix, ucfirst($propertyName));
        }

        foreach ($guessMethods as $methodName) {
            if (method_exists($object, $methodName)) {
                return $object->$methodName();
            }
        }

        throw new NoGetterMethodAvailableException(sprintf(
            'Could not get value for field name "%s" in entity "%s". None of following methods %s exists',
            $propertyName,
            ClassUtils::getRealClass($object),
            implode(', ', $guessMethods)
        ));
    }

    /**
     * @param string $name
     *
     * @return NormalizerInterface
     * @throws NormalizerNotFoundException
     */
    protected function getNormalizer(string $name): NormalizerInterface
    {
        /**
         * @var $normalizer NormalizerInterface
         */
        $normalizer = $this->normalizersManager->get($name);
        return $normalizer;
    }

    /**
     * @param $eventName
     * @param $listener
     * @param int $priority
     *
     * @return $this
     */
    public function addEventListener($eventName, $listener, $priority = 0)
    {
        $this->eventDispatcher->addListener($eventName, $listener, $priority);
        return $this;
    }

    /**
     * @param $eventName
     * @param $listener
     *
     * @return $this
     */
    public function removeEventListener($eventName, $listener)
    {
        $this->eventDispatcher->removeListener($eventName, $listener);
        return $this;
    }
}