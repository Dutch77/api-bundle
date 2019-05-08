<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 12. 3. 2019
 * Time: 14:11
 */

namespace Asyf\ApiBundle\Service\Normalizer;

use Asyf\ApiBundle\Configuration\FieldConfiguration;
use Asyf\ApiBundle\Configuration\FieldConfigurationInterface;
use Asyf\ApiBundle\Exception\Configuration\MissingConfigurationOptionException;

class DateTimeNormalizer extends AbstractNormalizer
{
    const FORMAT = 'format';
    /**
     * @param \DateTime $value
     * @param FieldConfigurationInterface $fieldConfiguration
     * @param int|null $maxDepth
     * @param int|null $currentDepth
     *
     * @return mixed
     */
    public function normalizeResource($value, FieldConfigurationInterface $fieldConfiguration, int $maxDepth, int $currentDepth)
    {
        $format = $this->getFormat($fieldConfiguration);
        return $value->format($format);
    }

    /**
     * @param FieldConfigurationInterface|FieldConfiguration $fieldConfiguration
     *
     * @return string
     */
    protected function getFormat(FieldConfigurationInterface $fieldConfiguration): string {
        try {
            return $fieldConfiguration->getOption(self::FORMAT);
        } catch (MissingConfigurationOptionException $exception) {
            return 'Y-m-d G:i:s';
        }
    }
}