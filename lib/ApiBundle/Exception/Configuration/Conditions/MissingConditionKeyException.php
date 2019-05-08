<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 26. 3. 2019
 * Time: 12:15
 */

namespace Asyf\ApiBundle\Exception\Configuration\Conditions;

class MissingConditionKeyException extends \Exception
{
    public function __construct(string $key)
    {
        $message = sprintf('Condition key "%s" is missing.', $key);
        parent::__construct($message);
    }
}