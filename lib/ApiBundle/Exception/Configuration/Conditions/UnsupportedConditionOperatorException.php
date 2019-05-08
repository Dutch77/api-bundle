<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 11. 3. 2019
 * Time: 11:49
 */

namespace Asyf\ApiBundle\Exception\Configuration\Conditions;

class UnsupportedConditionOperatorException extends \Exception
{
    public function __construct(string $operator)
    {
        $message = sprintf('Condition operator "%s" is not supported.', $operator);
        parent::__construct($message);
    }
}