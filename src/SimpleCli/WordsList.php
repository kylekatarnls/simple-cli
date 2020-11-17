<?php

declare(strict_types=1);

namespace SimpleCli;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, string>
 */
class WordsList implements IteratorAggregate
{
    /**
     * List of possible words.
     *
     * @var string[]
     */
    protected $words;

    /**
     * WordsList constructor.
     *
     * @param string[] $words
     */
    public function __construct(array $words)
    {
        $this->words = $words;
    }

    /**
     * Get the list of possible words.
     *
     * @return string[]
     */
    public function getWords(): array
    {
        return $this->words;
    }

    /**
     * Get the words list as an ArrayIterator instance.
     *
     * @return ArrayIterator<int, string>
     */
    public function getArrayIterator(): ArrayIterator
    {
        return new ArrayIterator($this->getWords());
    }

    /**
     * Retrieve an external iterator.
     *
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return Traversable<int, string> An instance of an object implementing <b>Iterator</b> or <b>Traversable</b>.
     */
    public function getIterator(): Traversable
    {
        return $this->getArrayIterator();
    }

    /**
     * Return the closest word as string from the possible list of words or null
     * if none is close enough.
     *
     * @param string $sourceWord   The word that may look like some of the list.
     * @param int    $minimalScore The minimum score to consider two words close.
     *
     * @return string|null
     */
    public function findClosestWord(string $sourceWord, int $minimalScore = 2): ?string
    {
        $words = $this->findClosestWords($sourceWord, $minimalScore);

        return $words === [] ? null : $words[0];
    }

    /**
     * Return the closest words as array from the possible list of words.
     *
     * @param string $sourceWord   The word that may look like some of the list.
     * @param int    $minimalScore The minimum score to consider two words close.
     *
     * @return string[]
     */
    public function findClosestWords(string $sourceWord, int $minimalScore = 2): array
    {
        $list = [];

        foreach ($this->words as $word) {
            $score = $this->getWordScore($sourceWord, $word);

            if ($score >= $minimalScore) {
                $list[$word] = $score;
            }
        }

        arsort($list);

        return array_keys($list);
    }

    /**
     * Get the matching score between two words.
     *
     * @param string $sourceWord Base word.
     * @param string $otherWord  Possible matching word.
     *
     * @return int
     */
    protected function getWordScore(string $sourceWord, string $otherWord): int
    {
        $score = 0;
        $length = strlen($sourceWord) - 1;

        for ($i = 0; $i < $length; $i++) {
            $couple = substr($sourceWord, $i, 2);

            if (stripos($otherWord, $couple) !== false) {
                $score += $i === 0 ? 2 : 1;
            }
        }

        return $score;
    }
}
