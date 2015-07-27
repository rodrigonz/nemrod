<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 02/04/2015
 * Time: 11:41.
 */
namespace Conjecto\Nemrod\Bundle\ElasticaBundle\DependencyInjection\CompilerPass;

use Conjecto\Nemrod\ElasticSearch\JsonLdFrameLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class ElasticaFramingRegistrationPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     *
     * @throws \InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        $config = $container->getExtensionConfig('elastica')[0];
        $jsonLdFrameLoader = $container->get('nemrod.elastica.jsonld.frame.loader.filesystem');
        $confManager = $container->getDefinition('nemrod.elastica.config_manager');
        $filiationBuilder = $container->get('nemrod.filiation.builder');
        $jsonLdFrameLoader->setFiliationBuilder($filiationBuilder);

        foreach ($config['indexes'] as $name => $types) {
            foreach ($types['types'] as $typeName => $settings) {
                $jsonLdFrameLoader->setEsIndex($name);
                $frame = $jsonLdFrameLoader->load($settings['frame'], null, false);
                $settings['frame'] = $frame;
                if (isset($frame['@type']) || isset($settings['type'])) {
                    $type = '';
                    if (isset($settings['type'])) {
                        $type = $settings['type'];
                    } elseif (isset($frame['@type'])) {
                        $type = $frame['@type'];
                    }

                    //type
                    $container
                        ->setDefinition('nemrod.elastica.type.'.$name.'.'.$typeName, new DefinitionDecorator('nemrod.elastica.type'))
                        ->setArguments(array(new Reference('nemrod.elastica.index.'.$name), $type))
                        ->addTag('nemrod.elastica.type', array('type' => $type));

                    //search service
                    $container
                        ->setDefinition('nemrod.elastica.search.'.$name.'.'.$typeName, new DefinitionDecorator('nemrod.elastica.search'))
                        ->setArguments(array(new Reference('nemrod.elastica.type.'.$name.'.'.$typeName), $typeName));

                    //registering config to configManager
                    $settings['type_service_id'] = 'nemrod.elastica.type.'.$name.'.'.$typeName;

                    $confManager->addMethodCall('setConfig', array($type, $settings));
                }
            }
        }
    }
}
