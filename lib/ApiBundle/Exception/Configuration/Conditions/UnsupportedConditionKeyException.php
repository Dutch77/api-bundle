<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 11. 3. 2019
 * Time: 11:49
 */

namespace Asyf\ApiBundle\Exception\Configuration\Conditions;

class UnsupportedConditionKeyException extends \Exception
{
    public function __construct(string $key)
    {
        $message = sprintf('Condition key "%s" is not supported.', $key);
        parent::__construct($message);
    }
}