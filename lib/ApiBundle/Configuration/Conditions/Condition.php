<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 25. 3. 2019
 * Time: 12:19
 */

namespace Asyf\ApiBundle\Configuration\Conditions;

class Condition
{
    /**
     * @var string
     */
    protected $field;
    /**
     * @var bool|int|string|array
     */
    protected $value;

    /**
     * @var Condition[]|null
     */
    protected $children;

    /**
     * @var string
     */
    protected $operator;
    /**
     * @var string
     */
    protected $type;

    /**
     * Condition constructor.
     *
     * @param string $field
     * @param string $value
     * @param array|null $children
     * @param string|null $operator
     * @param string|null $type
     */
    public function __construct(string $field, ?string $value = null, ?array $children = null, ?string $operator = ConditionsConfiguration::DEFAULT_CONDITION_OPERATOR, ?string $type = ConditionsConfiguration::DEFAULT_CONDITION_TYPE)
    {
        $this
            ->setField($field)
            ->setValue($value)
            ->setChildren($children)
            ->setOperator($operator)
            ->setType($type);
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
     * @return Condition
     */
    public function setField(string $field): Condition
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return Condition
     */
    public function setValue(?string $value): Condition
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return Condition[]|null
     */
    public function getChildren(): ?array
    {
        return $this->children;
    }

    /**
     * @param Condition[]|null $children
     *
     * @return Condition
     */
    public function setChildren(?array $children): Condition
    {
        $this->children = $children;
        return $this;
    }

    /**
     * @return string
     */
    public function getOperator(): ?string
    {
        return $this->operator;
    }

    /**
     * @param string $operator
     *
     * @return Condition
     */
    public function setOperator(?string $operator): Condition
    {
        $this->operator = strtolower($operator);
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Condition
     */
    public function setType(?string $type): Condition
    {
        $this->type = strtolower($type);
        return $this;
    }

}