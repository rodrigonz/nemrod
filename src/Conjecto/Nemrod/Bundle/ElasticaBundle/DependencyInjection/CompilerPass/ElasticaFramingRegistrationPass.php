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

        foreach ($config['indexes'] as $name => $index) {
            $indexName = isset($index['index_name']) ? $index['index_name']: $name;
            foreach ($index['types'] as $typeName => $settings) {
                $jsonLdFrameLoader->setEsIndex($name);
                $frame = $jsonLdFrameLoader->load($settings['frame'], null, true, true, true);

                $type = !empty($frame['@type']) ? $frame['@type'] : $settings['type'];
                if(empty($type)) {
                    throw \Exception("You must provide a RDF Type.");
                }

                //type
                $typeId = 'nemrod.elastica.type.'.$name.'.'.$typeName;
                $indexId = 'nemrod.elastica.index.'.$name;
                $typeDef = new DefinitionDecorator('nemrod.elastica.type.abstract');
                $typeDef->replaceArgument(0, $type);
                $typeDef->setFactory(array(new Reference($indexId), 'getType'));
                $typeDef->addTag('nemrod.elastica.type', array('index' => $name, 'name' => $typeName, 'type' => $type));

                $container->setDefinition($typeId, $typeDef);

                //registering config to configManager
                $confManager->addMethodCall('setTypeConfigurationArray', array($name, $typeName, $type, $frame));
            }
        }
    }
}
