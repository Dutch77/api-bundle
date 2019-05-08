<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 13. 3. 2019
 * Time: 15:35
 */

namespace Asyf\ApiBundle\Service\Normalizer;

use Asyf\ApiBundle\Configuration\FieldConfigurationInterface;

interface NormalizerInterface
{
    const DEFAULT_MAX_DEPTH = 5;

    public function normalize($value, ?FieldConfigurationInterface $fieldConfiguration = null, ?int $maxDepth = NormalizerInterface::DEFAULT_MAX_DEPTH, ?int $currentDepth = 0);
    public function normalizeResource($value, FieldConfigurationInterface $fieldConfiguration, int $maxDepth, int $currentDepth);
    public function addEventListener($eventName, $listener, $priority = 0);
    public function removeEventListener($eventName, $listener);
}