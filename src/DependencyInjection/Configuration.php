<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('crell_planedo');

        $treeBuilder->getRootNode()
            ->children()
                ->integerNode('items_per_page')
                    ->defaultValue(10)
                    ->info('How many feed items to show per page, both in HTML and in Atom/RSS feeds')
                ->end()
                ->scalarNode('purge_before')
                    ->defaultValue('-30 days')
                    ->info('How old an entry should be before it gets purged. Uses the standard PHP relative time formats (https://www.php.net/manual/en/datetime.formats.relative.php)')
                ->end()
                ->booleanNode('use_plain_text')
                    ->defaultValue(false)
                    ->info('Set to true to serve Atom/RSS feeds as text/plain instead of an XML mimetype.  Only for debugging. Do not use in production.')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
