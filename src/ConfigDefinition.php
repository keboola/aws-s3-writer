<?php

declare(strict_types=1);

namespace Keboola\S3Writer;

use Keboola\Component\Config\BaseConfigDefinition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class ConfigDefinition extends BaseConfigDefinition
{
    protected function getParametersDefinition(): ArrayNodeDefinition
    {
        $parametersNode = parent::getParametersDefinition();
        // @formatter:off
        /** @noinspection NullPointerExceptionInspection */
        $parametersNode
            ->children()
                ->scalarNode('accessKeyId')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('#secretAccessKey')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('bucket')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('prefix')
                ->end()
            ->end()
        ;
        // @formatter:on
        return $parametersNode;
    }
}
