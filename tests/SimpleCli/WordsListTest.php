<?php

namespace Tests\SimpleCli;

use ArrayIterator;
use SimpleCli\WordsList;
use Traversable;

/**
 * @coversDefaultClass \SimpleCli\WordsList
 */
class WordsListTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getWords
     */
    public function testConstructor()
    {
        $words = ['ab', 'bc', 'cd'];
        $wordsList = new WordsList($words);

        static::assertSame($words, $wordsList->getWords());
    }

    /**
     * @covers ::getArrayIterator
     */
    public function testGetArrayIterator()
    {
        $words = ['ab', 'bc', 'cd'];
        $list = (new WordsList($words))->getArrayIterator();

        static::assertInstanceOf(ArrayIterator::class, $list);
        static::assertSame($words, iterator_to_array($list));
    }

    /**
     * @covers ::getIterator
     */
    public function testGetIterator()
    {
        $words = ['ab', 'bc', 'cd'];
        $list = new WordsList($words);

        static::assertInstanceOf(Traversable::class, $list);
        static::assertSame($words, iterator_to_array($list));
    }

    /**
     * @covers ::findClosestWords
     * @covers ::getWordScore
     */
    public function testFindClosestWords()
    {
        $list = new WordsList(['ab', 'bc', 'cd']);

        static::assertSame([], $list->findClosestWords('ef'));

        $list = new WordsList(['update', 'delete', 'create']);

        static::assertSame(['update'], $list->findClosestWords('upgrade'));

        static::assertSame(['update', 'create'], $list->findClosestWords('date'));

        static::assertSame(['update', 'create', 'delete'], $list->findClosestWords('date', 1));
    }

    /**
     * @covers ::findClosestWord
     * @covers ::getWordScore
     */
    public function testFindClosestWord()
    {
        $list = new WordsList(['ab', 'bc', 'cd']);

        static::assertNull($list->findClosestWord('ef'));

        $list = new WordsList(['update', 'delete', 'create']);

        static::assertSame('update', $list->findClosestWord('upgrade'));

        static::assertSame('update', $list->findClosestWord('date'));

        static::assertSame('update', $list->findClosestWord('date', 1));

        static::assertNull($list->findClosestWord('date', 5));

        static::assertSame('update', $list->findClosestWord('dateup', 5));

        static::assertSame('delete', $list->findClosestWord('dellete'));
    }
}
