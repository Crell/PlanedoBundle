<?php

declare(strict_types=1);

/*
 * This file is part of the package crell/planedo-bundle.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Crell\Bundle\Planedo\Tests\Unit\Entity;

use Crell\Bundle\Planedo\Entity\FeedEntry;
use PHPUnit\Framework\TestCase;

class FeedEntryTest extends TestCase
{
    /**
     * @test
     * @dataProvider descriptionCleanerDataProvider()
     */
    public function descriptionCleanerTest(string $unclean, string $clean, string $link, string $title): void
    {
        $entry = (new FeedEntry())
            ->setDescription($unclean)
            ->setLink($link)
            ->setTitle($title);
        $result = $entry->getDescription();

        self::assertEquals($clean, $result);
    }

    public function descriptionCleanerDataProvider(): iterable
    {
        yield 'Empty string' => [
            'unclean' => '',
            'clean' => '',
            'link' => '',
            'title' => '',
        ];

        yield 'No link to remove' => [
            'unclean' => '<article>Stuff here</article>',
            'clean' => '<article>Stuff here</article>',
            'link' => '',
            'title' => '',
        ];

        yield 'Simple link' => [
            'unclean' => '<article>Stuff: <a href="https://www.google.com/">The title</a></article>',
            'clean' => '<article>Stuff: </article>',
            'link' => 'https://www.google.com/',
            'title' => 'The title',
        ];

        yield 'Link with anchor' => [
            'unclean' => '<article>Stuff: <a href="https://www.google.com/#beep">The title</a></article>',
            'clean' => '<article>Stuff: </article>',
            'link' => 'https://www.google.com/#beep',
            'title' => 'The title',
        ];

        yield 'Link with query params' => [
            'unclean' => '<article>Stuff: <a href="https://www.google.com/?beep=boop&foo=bar">The title</a></article>',
            'clean' => '<article>Stuff: </article>',
            'link' => 'https://www.google.com/?beep=boop&foo=bar',
            'title' => 'The title',
        ];

        yield 'Link with query params and anchor' => [
            'unclean' => '<article>Stuff: <a href="https://www.google.com/?beep=boop&foo=bar#result">The title</a></article>',
            'clean' => '<article>Stuff: </article>',
            'link' => 'https://www.google.com/?beep=boop&foo=bar#result',
            'title' => 'The title',
        ];

        yield 'Link with extra elements' => [
            'unclean' => '<article>Stuff: <a href="https://www.google.com/"><span class="foo">The title</span></a></article>',
            'clean' => '<article>Stuff: </article>',
            'link' => 'https://www.google.com/',
            'title' => 'The title',
        ];

        yield 'Link with extra attributes' => [
            'unclean' => '<article>Stuff: <a rel="foo" href="https://www.google.com/" class="bar">The title</a></article>',
            'clean' => '<article>Stuff: </article>',
            'link' => 'https://www.google.com/',
            'title' => 'The title',
        ];

        $unclean = <<<END
<article data-history-node-id="303" role="article" about="https://www.garfieldtech.com/blog/aoc2021-day5" class="node node--type-story node--promoted node--view-mode-teaser clearfix"><header><h2 class="node__title">
        <a href="https://www.garfieldtech.com/blog/aoc2021-day5" rel="bookmark"><span class="field field--name-title field--type-string field--label-hidden">Advent of Functional PHP: Day 5</span>
</a>
      </h2>
      </header>
</article>
END;
        $clean = <<<END
<article data-history-node-id="303" role="article" about="https://www.garfieldtech.com/blog/aoc2021-day5" class="node node--type-story node--promoted node--view-mode-teaser clearfix"><header><h2 class="node__title">

</h2>
</header>
</article>
END;

        yield 'Link with newlines in the way' => [
                'unclean' => $unclean,
                'clean' => $clean,
                'link' => 'https://www.garfieldtech.com/blog/aoc2021-day5',
                'title' => 'Advent of Functional PHP: Day 5',
        ];

        $unclean = <<<END
<article data-history-node-id="303" role="article" about="https://www.garfieldtech.com/blog/aoc2021-day5" class="node node--type-story node--promoted node--view-mode-teaser clearfix"><header><h2 class="node__title">
        <a href="https://www.garfieldtech.com/blog/aoc2021-day5" rel="bookmark"><span class="field field--name-title field--type-string field--label-hidden">Advent of Functional PHP: Day 5</span>
</a>
      </h2>
      </header>
      <a href="https://duckduckgo.com/">Search</a>
</article>
END;
        $clean = <<<END
<article data-history-node-id="303" role="article" about="https://www.garfieldtech.com/blog/aoc2021-day5" class="node node--type-story node--promoted node--view-mode-teaser clearfix"><header><h2 class="node__title">

</h2>
</header>
<a href="https://duckduckgo.com/">Search</a>
</article>
END;

        yield 'Multiple links but only filter the first' => [
                'unclean' => $unclean,
                'clean' => $clean,
                'link' => 'https://www.garfieldtech.com/blog/aoc2021-day5',
                'title' => 'Advent of Functional PHP: Day 5',
        ];
    }
}
