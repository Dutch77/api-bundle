<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 25. 3. 2019
 * Time: 10:57
 */

namespace Asyf\ApiBundle\Exception\Configuration\OrderBy;

class NotMappedFieldException extends \Exception
{
    public function __construct(string $fieldName, string $entityClass)
    {
        $message = sprintf('Entity class "%s" does not have mapped field "%s".', $entityClass, $fieldName);
        parent::__construct($message);
    }
}