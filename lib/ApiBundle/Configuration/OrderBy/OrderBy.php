<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 11. 3. 2019
 * Time: 10:58
 */

namespace Asyf\ApiBundle\Configuration\OrderBy;

class OrderBy
{
    const DIRECTION = 'direction';
    const PRIORITY = 'priority';

    /**
     * @var string
     */
    protected $field;
    /**
     * @var string
     */
    protected $direction;
    /**
     * @var int|null
     */
    protected $priority;

    public function __construct(string $field, string $direction, ?int $priority = 1)
    {
        $this->field = $field;
        $this->direction = $direction;
        $this->priority = $priority;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @param string $field
     *
     * @return OrderBy
     */
    public function setField(string $field): OrderBy
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return string
     */
    public function getDirection(): string
    {
        return $this->direction;
    }

    /**
     * @param string $direction
     *
     * @return OrderBy
     */
    public function setDirection(string $direction): OrderBy
    {
        $this->direction = $direction;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPriority(): ?int
    {
        return $this->priority;
    }

    /**
     * @param int|null $priority
     *
     * @return OrderBy
     */
    public function setPriority(?int $priority): OrderBy
    {
        $this->priority = $priority;
        return $this;
    }

}