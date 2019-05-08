<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 11. 3. 2019
 * Time: 10:36
 */

namespace Asyf\ApiBundle\Configuration;

use Symfony\Component\Security\Acl\Util\ClassUtils;
use Asyf\ApiBundle\Exception\Configuration\MissingConfigurationOptionException;

class IterableFieldConfiguration extends FieldConfiguration implements FieldConfigurationInterface
{
    const SAME_TYPE = 'sameType';

    /**
     * @var iterable
     */
    protected $value;

    /**
     * @return bool
     */
    public function getSameType(): bool
    {
        try {
            return $this->getConfigurationOption(self::SAME_TYPE);
        } catch (MissingConfigurationOptionException $exception) {
            return true;
        }
    }
}