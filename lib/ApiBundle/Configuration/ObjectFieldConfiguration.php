<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 11. 3. 2019
 * Time: 10:36
 */

namespace Asyf\ApiBundle\Configuration;


class ObjectFieldConfiguration extends FieldConfiguration implements FieldConfigurationInterface
{
    /**
     * @var object
     */
    protected $value;
}