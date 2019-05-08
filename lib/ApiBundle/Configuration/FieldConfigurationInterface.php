<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 11. 3. 2019
 * Time: 10:36
 */

namespace Asyf\ApiBundle\Configuration;

interface FieldConfigurationInterface
{
    /**
     * @return bool
     */
    public function isExposed(): bool;

    /**
     * @return string
     */
    public function getNormalizerName(): string;

    /**
     * @return array
     */
    public function getRawNestedFieldsConfiguration(): array;

    /**
     * @return array
     */
    public function getOptions(): array;

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getOption(string $key);

    /**
     * @return int
     */
    public function getMaxDepth(): ?int;
}