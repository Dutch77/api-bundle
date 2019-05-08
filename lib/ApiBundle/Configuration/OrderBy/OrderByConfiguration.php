<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 11. 3. 2019
 * Time: 11:01
 */

namespace Asyf\ApiBundle\Configuration\OrderBy;

use Asyf\ApiBundle\Exception\Configuration\MissingConfigurationOptionException;
use Asyf\ApiBundle\Exception\Configuration\OrderBy\NotMappedFieldException;
use Asyf\ApiBundle\Exception\Configuration\OrderBy\UnsupportedOrderDirectionException;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\PersistentCollection;

class OrderByConfiguration
{
    /**
     * @var OrderBy[]
     */
    protected $orderBys = [];
    /**
     * @var PersistentCollection
     */
    protected $value;

    /**
     * OrderByConfiguration constructor.
     *
     * @param PersistentCollection $value
     * @param array $orderBy
     *
     * @throws MissingConfigurationOptionException
     * @throws NotMappedFieldException
     * @throws UnsupportedOrderDirectionException
     */
    public function __construct(PersistentCollection $value, array $orderBy)
    {
        $this->value = $value;
        $this->process($orderBy);
    }

    /**
     * @return OrderBy[]
     */
    public function getOrderBys(): array
    {
        usort($this->orderBys, function (OrderBy $a, OrderBy $b) {
            return ($a->getPriority() < $b->getPriority()) ? -1 : 1;
        });
        return $this->orderBys;
    }

    /**
     * @return array
     */
    public function getOrderByArray(): array
    {
        $orderByArray = [];
        foreach ($this->getOrderBys() as $orderBy) {
            $orderByArray[$orderBy->getField()] = $orderBy->getDirection();
        }
        return $orderByArray;
    }

    /**
     * @param string $fieldName
     * @param string $direction
     * @param int|null $priority
     *
     * @return $this
     * @throws UnsupportedOrderDirectionException
     * @throws NotMappedFieldException
     *
     * Some people may wonder why prioritize. Order of orderBys is lost when reading from request or json.
     */
    public function add(string $fieldName, string $direction, ?int $priority = null): OrderByConfiguration
    {
        $this->checkField($fieldName);
        $this->checkDirection($direction);

        if (!$priority) {
            $highestPriority = 0;
            foreach ($this->orderBys as $orderBy) {
                if ($orderBy->getPriority() > $highestPriority) {
                    $highestPriority = $orderBy->getPriority();
                }
            }
            $highestPriority++;
            $newOrderBy = new OrderBy($fieldName, $direction, $highestPriority);
        } else {
            $newOrderBy = new OrderBy($fieldName, $direction, $priority);
        }
        $this->orderBys[$fieldName] = $newOrderBy;
        return $this;
    }

    /**
     * @param string $fieldName
     *
     * @return OrderByConfiguration
     */
    public function remove(string $fieldName): OrderByConfiguration
    {
        unset($this->orderBys[$fieldName]);
        return $this;
    }

    /**
     * @return OrderByConfiguration
     */
    public function reset(): OrderByConfiguration
    {
        $this->orderBys = [];
        return $this;
    }

    /**
     * @param array $orderBys
     *
     * @throws MissingConfigurationOptionException
     * @throws NotMappedFieldException
     * @throws UnsupportedOrderDirectionException
     */
    protected function process(array $orderBys)
    {
        foreach ($orderBys as $key => $value) {
            if (is_array($value)) {
                if (isset($value[OrderBy::DIRECTION])) {
                    $direction = $value[OrderBy::DIRECTION];
                    $priority = isset($value[OrderBy::PRIORITY]) ? $value[OrderBy::PRIORITY] : null;
                    if ($priority) {
                        $this->add($key, $direction, $priority);
                    } else {
                        $this->add($key, $direction);
                    }
                } else {
                    throw new MissingConfigurationOptionException(OrderBy::DIRECTION);
                }
            } else {
                $this->add($key, $value);
            }
        }
    }

    /**
     * @param string $fieldName
     *
     * @throws NotMappedFieldException
     */
    protected function checkField(string $fieldName): void
    {
        try {
            $this->value->getTypeClass()->getFieldMapping($fieldName);
        } catch (MappingException $exception) {
            throw new NotMappedFieldException($fieldName, $this->value->getTypeClass()->getName());
        }
    }

    /**
     * @param string $direction
     *
     * @throws UnsupportedOrderDirectionException
     */
    protected function checkDirection(string $direction): void
    {
        switch ($direction) {
            case 'ASC':
            case 'asc':
            case 'DESC':
            case 'desc':
                break;
            default:
                throw new UnsupportedOrderDirectionException($direction);
        }
    }
}