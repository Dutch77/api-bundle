<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 11. 3. 2019
 * Time: 16:54
 */

namespace Asyf\ApiBundle\Exception\Normalizer;

class NormalizerNotFoundException extends \Exception
{
    public function __construct(string $key)
    {
        $message = sprintf('Normalizer "%s" was not found.', $key);
        parent::__construct($message);
    }
}