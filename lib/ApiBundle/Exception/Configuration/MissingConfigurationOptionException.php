<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 11. 3. 2019
 * Time: 14:03
 */

namespace Asyf\ApiBundle\Exception\Configuration;


class MissingConfigurationOptionException extends \Exception
{
    public function __construct(string $key)
    {
        $message = sprintf('Configuration option "%s" is not defined.', $key);
        parent::__construct($message);
    }
}