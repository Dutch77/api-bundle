<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 11. 3. 2019
 * Time: 11:49
 */

namespace Asyf\ApiBundle\Exception\Configuration\Conditions;

class UnsupportedConditionTypeException extends \Exception
{
    public function __construct(string $type)
    {
        $message = sprintf('Condition type "%s" is not supported.', $type);
        parent::__construct($message);
    }
}