<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 11. 3. 2019
 * Time: 10:36
 */

namespace Asyf\ApiBundle\Configuration;

use Asyf\ApiBundle\Exception\Configuration\MissingConfigurationOptionException;
use Asyf\ApiBundle\Service\Normalizer\NormalizerInterface;
use Asyf\ApiBundle\Utils\Utils;

class FieldConfiguration implements FieldConfigurationInterface
{
    const FIELDS = 'fields';
    const NORMALIZER = 'normalizer';
    const DEFAULT_NORMALIZER_NAME = NormalizerInterface::class;
    const OPTIONS = 'options';
    const EXPOSE = 'expose';
    const MAX_DEPTH = 'maxDepth';

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var array
     */
    protected $mergedRawConfiguration;

    /**
     * FieldConfiguration constructor.
     *
     * @param mixed $value
     * @param array $configurations
     */
    public function __construct($value, array $configurations)
    {
        $this->value = $value;
        $this->mergedRawConfiguration = Utils::mergeArrays($configurations, true, [self::FIELDS]);
    }

    /**
     * @return bool
     */
    public function isExposed(): bool
    {
        try {
            return $this->getConfigurationOption(self::EXPOSE);
        } catch (MissingConfigurationOptionException $exception) {
            return true;
        }
    }

    /**
     * @return string
     */
    public function getNormalizerName(): string
    {
        try {
            return $this->getConfigurationOption(self::NORMALIZER);
        } catch (MissingConfigurationOptionException $exception) {
            return self::DEFAULT_NORMALIZER_NAME;
        }
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        try {
            return $this->getConfigurationOption(self::OPTIONS);
        } catch (MissingConfigurationOptionException $exception) {
            return [];
        }
    }

    /**
     * @return int|null
     */
    public function getMaxDepth(): ?int
    {
        try {
            return $this->getConfigurationOption(self::MAX_DEPTH);
        } catch (MissingConfigurationOptionException $exception) {
            return null;
        }
    }

    /**
     * @param string $key
     *
     * @return mixed
     * @throws MissingConfigurationOptionException
     */
    public function getOption(string $key)
    {
        try {
            return $this->getConfigurationOption(self::OPTIONS)[$key];
        } catch (\Exception $exception) {
            throw new MissingConfigurationOptionException(sprintf('options.%s', $key));
        }
    }

    /**
     * @return array
     * @throws MissingConfigurationOptionException
     */
    public function getRawNestedFieldsConfiguration(): array
    {
        return $this->getConfigurationOption(self::FIELDS);
    }

    /**
     * @param string $key
     *
     * @return mixed
     * @throws MissingConfigurationOptionException
     */
    protected function getConfigurationOption(string $key)
    {
        if (isset($this->mergedRawConfiguration[$key])) {
            return $this->mergedRawConfiguration[$key];
        } else {
            throw new MissingConfigurationOptionException($key);
        }
    }
}