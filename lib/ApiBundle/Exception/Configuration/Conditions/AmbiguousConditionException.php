<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 26. 3. 2019
 * Time: 12:15
 */

namespace Asyf\ApiBundle\Exception\Configuration\Conditions;

class AmbiguousConditionException extends \Exception
{
    public function __construct()
    {
        $message = 'There can be either key "value" or key "children"';
        parent::__construct($message);
    }
}