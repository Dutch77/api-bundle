<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 11. 3. 2019
 * Time: 11:48
 */

namespace Asyf\ApiBundle\Exception\Configuration;

class NoSuchPropertyException extends \Exception
{
    public function __construct(string $propertyName, string $className)
    {
        $message = sprintf('Property "%s" does not exist in class "%s"', $propertyName, $className);
        parent::__construct($message);
    }
}