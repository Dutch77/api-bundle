<?php

namespace Asyf\ApiBundle\DependencyInjection;

use Asyf\ApiBundle\Service\Normalizer\NormalizerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AsyfApiExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('asyf_api', $config);

        $defaultNormalizer = isset($config['default_normalizer']) ? $config['default_normalizer'] : $container->getParameter('asyf.api.normalizer.default.class');
        $container->setAlias(NormalizerInterface::class, $defaultNormalizer);
    }
}
