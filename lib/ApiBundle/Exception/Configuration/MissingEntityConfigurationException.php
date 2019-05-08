<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 11. 3. 2019
 * Time: 12:55
 */

namespace Asyf\ApiBundle\Exception\Configuration;

class MissingEntityConfigurationException extends \Exception
{
    public function __construct(string $className)
    {
        $message = sprintf('There is no default configuration for class "%s" in "asyf_api.yaml".', $className);
        parent::__construct($message);
    }
}