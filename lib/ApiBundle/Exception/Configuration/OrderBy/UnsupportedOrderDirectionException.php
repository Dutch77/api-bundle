<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 11. 3. 2019
 * Time: 11:49
 */

namespace Asyf\ApiBundle\Exception\Configuration\OrderBy;

class UnsupportedOrderDirectionException extends \Exception
{
    public function __construct(string $direction)
    {
        $message = sprintf('Direction "%s" is not supported.', $direction);
        parent::__construct($message);
    }
}