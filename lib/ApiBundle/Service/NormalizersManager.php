<?php
/**
 * Created by PhpStorm.
 * User: Michal Kolář
 * Date: 12. 3. 2019
 * Time: 16:07
 */

namespace Asyf\ApiBundle\Service;

use Asyf\ApiBundle\Exception\Normalizer\NormalizerNotFoundException;
use Asyf\ApiBundle\Service\Normalizer\NormalizerInterface;
use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

class NormalizersManager
{
    /**
     * @var NormalizerInterface[]|RewindableGenerator
     */
    protected $normalizers;

    /**
     * @var string
     */
    protected $defaultNormalizerName;

    /**
     * NormalizersManager constructor.
     *
     * @param iterable $normalizers
     * @param string $defaultNormalizerName
     */
    public function __construct(iterable $normalizers, string $defaultNormalizerName)
    {
        $this->normalizers = $normalizers;
        $this->defaultNormalizerName = $defaultNormalizerName;
    }

    /**
     * @param string $key
     *
     * @return NormalizerInterface
     * @throws NormalizerNotFoundException
     */
    public function get(string $key): NormalizerInterface
    {
        if ($key === NormalizerInterface::class) {
            $key = $this->defaultNormalizerName;
        }

        foreach ($this->normalizers as $normalizer) {
            if (get_class($normalizer) === $key) {
                return $normalizer;
            }
        }
        throw new NormalizerNotFoundException($key);
    }
}