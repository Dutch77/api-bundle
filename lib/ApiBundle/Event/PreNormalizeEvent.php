<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 24. 4. 2019
 * Time: 10:13
 */

namespace Asyf\ApiBundle\Event;

class PreNormalizeEvent extends ApiEvent
{
    /**
     * @var int|string|object|iterable
     */
    protected $value;

    /**
     * PreNormalizeEvent constructor.
     *
     * @param $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return int|iterable|object|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int|iterable|object|string $value
     *
     * @return ApiEvent
     */
    public function setValue($value): ApiEvent
    {
        $this->value = $value;
        return $this;
    }
}