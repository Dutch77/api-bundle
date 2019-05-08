<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 13. 3. 2019
 * Time: 13:46
 */

namespace Asyf\ApiBundle\Configuration\Conditions;

use Asyf\ApiBundle\Exception\Configuration\Conditions\AmbiguousConditionException;
use Asyf\ApiBundle\Exception\Configuration\Conditions\MissingConditionKeyException;
use Asyf\ApiBundle\Exception\Configuration\Conditions\UnsupportedConditionOperatorException;
use Asyf\ApiBundle\Exception\Configuration\Conditions\UnsupportedConditionTypeException;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\Common\Collections\Expr\Expression;
use Doctrine\ORM\PersistentCollection;

class ConditionsConfiguration
{
    const FIELD = 'field';
    const TYPE = 'type';
    const OPERATOR = 'operator';
    const VALUE = 'value';
    const CHILDREN = 'children';

    const AVAILABLE_CONDITION_KEYS = [
        self::FIELD,
        self::TYPE,
        self::OPERATOR,
        self::VALUE,
        self::CHILDREN
    ];

    const CONDITION_OPERATOR_EQUAL = '=';
    const CONDITION_OPERATOR_NOT_EQUAL = '!=';
    const CONDITION_OPERATOR_LESS = '<';
    const CONDITION_OPERATOR_LESS_OR_EQUAL = '<=';
    const CONDITION_OPERATOR_GREATER = '>';
    const CONDITION_OPERATOR_GREATER_OR_EQUAL = '>=';
    const CONDITION_OPERATOR_LIKE = 'like';
    const CONDITION_OPERATOR_IS_NULL = 'is null';
    const CONDITION_OPERATOR_IS_NOT_NULL = 'is not null';
    const CONDITION_OPERATOR_IN = 'in';
    const CONDITION_OPERATOR_NOT_IN = 'not in';

    const DEFAULT_CONDITION_OPERATOR = self::CONDITION_OPERATOR_EQUAL;

    const CONDITION_TYPE_AND = 'and';
    const CONDITION_TYPE_OR = 'or';

    const DEFAULT_CONDITION_TYPE = self::CONDITION_TYPE_AND;

    /**
     * @var PersistentCollection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $rawConditions;

    /**
     * @var Expression
     */
    protected $expression;

    /**
     * ConditionsConfiguration constructor.
     *
     * @param PersistentCollection $collection
     * @param array $rawConditions
     */
    public function __construct(PersistentCollection $collection, array $rawConditions)
    {
        $this->expr = Criteria::expr();
        $this->collection = $collection;
        $this->rawConditions = $rawConditions;
    }

    /**
     * @return Expression|null
     * @throws AmbiguousConditionException
     * @throws MissingConditionKeyException
     * @throws UnsupportedConditionOperatorException
     * @throws UnsupportedConditionTypeException
     */
    public function getExpression(): ?Expression
    {
        $conditions = $this->createConditions($this->rawConditions);
        return $this->buildExpression($conditions);
    }

    /**
     * @param Condition[] $conditions
     *
     * @return Expression|null
     * @throws UnsupportedConditionOperatorException
     * @throws UnsupportedConditionTypeException
     */
    protected function buildExpression(array $conditions): ?Expression
    {
        $finalExpression = null;
        $conditionsToGroup = [];
        foreach ($conditions as $condition) {
            if ($finalExpression) {
                $conditionsToGroup = [$finalExpression];
            }

            if ($condition->getValue()) {
                $conditionExpression = $this->getConditionExpression($condition);
                $conditionsToGroup[] = $conditionExpression;
            } else if ($condition->getChildren()) {
                $conditionExpression = $this->buildExpression($condition->getChildren());
                $conditionsToGroup[] = $conditionExpression;
            }

            if (count($conditionsToGroup)) {
                $type = $condition->getType();
                switch ($type) {
                    case self::CONDITION_TYPE_AND:
                        $finalExpression = new CompositeExpression(CompositeExpression::TYPE_AND, $conditionsToGroup);
                        break;
                    case self::CONDITION_TYPE_OR:
                        $finalExpression = new CompositeExpression(CompositeExpression::TYPE_OR, $conditionsToGroup);
                        break;
                    default:
                        throw new UnsupportedConditionTypeException($type);
                }
            }
        }
        return $finalExpression;
    }

    /**
     * @param Condition $condition
     *
     * @return Comparison
     * @throws UnsupportedConditionOperatorException
     */
    protected function getConditionExpression(Condition $condition): Comparison
    {
        $expr = Criteria::expr();
        $operator = $condition->getOperator();
        $field = $condition->getField();
        $value = $condition->getValue();

        switch ($operator) {
            case self::CONDITION_OPERATOR_EQUAL:
                return $expr->eq($field, $value);
            case self::CONDITION_OPERATOR_NOT_EQUAL:
                return $expr->neq($field, $value);
            case self::CONDITION_OPERATOR_LESS:
                return $expr->lt($field, $value);
            case self::CONDITION_OPERATOR_LESS_OR_EQUAL:
                return $expr->lte($field, $value);
            case self::CONDITION_OPERATOR_GREATER:
                return $expr->gt($field, $value);
            case self::CONDITION_OPERATOR_GREATER_OR_EQUAL:
                return $expr->gte($field, $value);
            case self::CONDITION_OPERATOR_LIKE:
                return new Comparison($field, Comparison::CONTAINS, $value);
            case self::CONDITION_OPERATOR_IS_NULL:
                return new Comparison($field, Comparison::EQ, null);
            case self::CONDITION_OPERATOR_IS_NOT_NULL:
                return new Comparison($field, Comparison::NEQ, null);
            case self::CONDITION_OPERATOR_IN:
                return $expr->in($field, $value);
            case self::CONDITION_OPERATOR_NOT_IN:
                return $expr->notIn($field, $value);
            default:
                throw new UnsupportedConditionOperatorException($operator);
        }
    }

    /**
     * @param array $rawConditions
     *
     * @return array
     * @throws AmbiguousConditionException
     * @throws MissingConditionKeyException
     */
    protected function createConditions(array $rawConditions): array
    {
        $conditions = [];
        foreach ($rawConditions as $rawCondition) {
            $field = null;
            $value = null;
            $children = null;
            $operator = self::DEFAULT_CONDITION_OPERATOR;
            $type = self::DEFAULT_CONDITION_TYPE;

            if (isset($rawCondition[self::FIELD])) {
                $field = $rawCondition[self::FIELD];
            } else {
                throw new MissingConditionKeyException(self::FIELD);
            }
            if (isset($rawCondition[self::VALUE]) && isset($rawCondition[self::CHILDREN])) {
                throw new AmbiguousConditionException();
            }
            if (isset($rawCondition[self::VALUE])) {
                $value = $rawCondition[self::VALUE];
            }
            if (isset($rawCondition[self::CHILDREN])) {
                $children = $this->createConditions($rawCondition[self::CHILDREN]);
            }
            if (isset($rawCondition[self::OPERATOR])) {
                $operator = $rawCondition[self::OPERATOR];
            }
            if (isset($rawCondition[self::TYPE])) {
                $type = $rawCondition[self::TYPE];
            }
            $conditions[] = new Condition($field, $value, $children, $operator, $type);
        }
        return $conditions;
    }
}