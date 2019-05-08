<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 24. 4. 2019
 * Time: 10:13
 */

namespace Asyf\ApiBundle\Event;

class PostNormalizeEvent extends ApiEvent
{
    /**
     * @var int|string|object|iterable
     */
    protected $originalValue;

    /**
     * @var int|string|iterable
     */
    protected $normalizedValue;

    /**
     * PostNormalizeEvent constructor.
     *
     * @param $normalizedValue
     * @param $originalValue
     */
    public function __construct($normalizedValue, $originalValue)
    {
        $this->originalValue = $originalValue;
        $this->normalizedValue = $normalizedValue;
    }

    /**
     * @return int|iterable|object|string
     */
    public function getOriginalValue()
    {
        return $this->originalValue;
    }

    /**
     * @param int|iterable|object|string $originalValue
     *
     * @return PostNormalizeEvent
     */
    public function setOriginalValue($originalValue)
    {
        $this->originalValue = $originalValue;
        return $this;
    }

    /**
     * @return int|iterable|string
     */
    public function getNormalizedValue()
    {
        return $this->normalizedValue;
    }

    /**
     * @param int|iterable|string $normalizedValue
     *
     * @return PostNormalizeEvent
     */
    public function setNormalizedValue($normalizedValue)
    {
        $this->normalizedValue = $normalizedValue;
        return $this;
    }

}