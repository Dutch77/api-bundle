<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 11. 3. 2019
 * Time: 10:36
 */

namespace Asyf\ApiBundle\Configuration;

use Asyf\ApiBundle\Configuration\Conditions\ConditionsConfiguration;
use Asyf\ApiBundle\Configuration\OrderBy\OrderByConfiguration;
use Asyf\ApiBundle\Exception\Configuration\MissingConfigurationOptionException;
use Asyf\ApiBundle\Exception\Configuration\OrderBy\UnsupportedOrderDirectionException;
use Asyf\ApiBundle\Exception\Configuration\OrderBy\NotMappedFieldException;
use Asyf\ApiBundle\Exception\Configuration\Conditions\AmbiguousConditionException;
use Asyf\ApiBundle\Exception\Configuration\Conditions\MissingConditionKeyException;
use Asyf\ApiBundle\Exception\Configuration\Conditions\UnsupportedConditionTypeException;
use Asyf\ApiBundle\Exception\Configuration\Conditions\UnsupportedConditionOperatorException;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;
use Doctrine\ORM\PersistentCollection;

class PersistentCollectionFieldConfiguration extends IterableFieldConfiguration implements FieldConfigurationInterface
{
    const ORDER_BY = 'orderBy';
    const LIMIT = 'limit';
    const OFFSET = 'offset';
    const CONDITIONS = 'conditions';

    /**
     * @var PersistentCollection
     */
    protected $value;
    /**
     * @var OrderByConfiguration
     */
    protected $orderByConfiguration;

    /**
     * @var ConditionsConfiguration
     */
    protected $conditionsConfiguration;
    /**
     * @var int|null
     */
    protected $limit;
    /**
     * @var int|null
     */
    protected $offset;

    /**
     * @var Criteria
     */
    protected $criteria;

    /**
     * PersistentCollectionFieldConfiguration constructor.
     *
     * @param PersistentCollection $value
     * @param array $configurations
     *
     * @throws MissingConfigurationOptionException
     * @throws NotMappedFieldException
     * @throws UnsupportedOrderDirectionException
     */
    public function __construct(PersistentCollection $value, array $configurations)
    {
        parent::__construct($value, $configurations);
        $this->processOrderBy();
        $this->processLimitAndOffset();
        $this->processConditions();
    }

    /**
     * @return FieldConfiguration
     */
    protected function processConditions(): FieldConfiguration
    {
        try {
            $conditions = $this->getConfigurationOption(self::CONDITIONS);
        } catch (MissingConfigurationOptionException $exception) {
            $conditions = [];
        }
        $this->conditionsConfiguration = new ConditionsConfiguration($this->value, $conditions);
        return $this;
    }

    /**
     * @return FieldConfiguration
     * @throws MissingConfigurationOptionException
     * @throws UnsupportedOrderDirectionException
     * @throws NotMappedFieldException
     */
    protected function processOrderBy(): FieldConfiguration
    {
        $orderBy = $this->getConfigurationOption(self::ORDER_BY);
        $this->orderByConfiguration = new OrderByConfiguration($this->value, $orderBy);
        return $this;
    }

    /**
     * @return FieldConfiguration
     */
    protected function processLimitAndOffset(): FieldConfiguration
    {
        try {
            $this->limit = $this->getConfigurationOption(self::LIMIT);
        } catch (MissingConfigurationOptionException $exception) {
            $this->limit = null;
        }

        try {
            $this->offset = $this->getConfigurationOption(self::OFFSET);
        } catch (MissingConfigurationOptionException $exception) {
            if ($this->limit) {
                $this->offset = 0;
            } else {
                $this->offset = null;
            }
        }
        return $this;
    }

    /**
     * @param string $fieldName
     * @param string $direction
     * @param int|null $priority
     *
     * @return $this
     * @throws NotMappedFieldException
     * @throws UnsupportedOrderDirectionException
     */
    public function addOrderBy(string $fieldName, string $direction, ?int $priority = null): FieldConfiguration
    {
        $this->orderByConfiguration->add($fieldName, $direction, $priority);
        return $this;
    }

    /**
     * @param string $fieldName
     *
     * @return $this
     */
    public function removeOrderBy(string $fieldName): FieldConfiguration
    {
        $this->orderByConfiguration->remove($fieldName);
        return $this;
    }

    /**
     * @return $this
     */
    public function resetOrderBy(): FieldConfiguration
    {
        $this->orderByConfiguration->reset();
        return $this;
    }

    /**
     * @return array
     */
    public function getOrderBy(): array
    {
        return $this->orderByConfiguration->getOrderByArray();
    }

    /**
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @return int|null
     */
    public function getOffset(): ?int
    {
        return $this->offset;
    }

    /**
     * @return bool
     */
    public function getSameType(): bool
    {
        return true;
    }

    /**
     * @return Expression|null
     * @throws AmbiguousConditionException
     * @throws MissingConditionKeyException
     * @throws UnsupportedConditionOperatorException
     * @throws UnsupportedConditionTypeException
     */
    protected function getConditionsExpression(): ?Expression
    {
        return $this->conditionsConfiguration->getExpression();
    }

    /**
     * @return Criteria
     * @throws AmbiguousConditionException
     * @throws MissingConditionKeyException
     * @throws UnsupportedConditionOperatorException
     * @throws UnsupportedConditionTypeException
     */
    public function getCriteria(): Criteria
    {
        $criteria = Criteria::create();
        if ($limit = $this->getLimit()) {
            // if there is a limit, there is an offset -> implicitly set to 0
            $offset = $this->getOffset();
            $criteria
                ->setMaxResults($limit)
                ->setFirstResult($offset);
        }
        $orderBy = $this->getOrderBy();
        if (count($orderBy)) {
            $criteria->orderBy($orderBy);
        }
        if ($conditionExpression = $this->getConditionsExpression()) {
            $criteria->where($conditionExpression);
        }
        return $criteria;
    }
}