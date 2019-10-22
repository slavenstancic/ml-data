<?php

namespace App\Parsers;

use App\CellParserInterface;

class CharacterCounter implements CellParserInterface
{
    /** @var string */
    private $character;

    public function __construct(string $character = null)
    {
        $this->character = $character;
    }

    public function getName(): string
    {
        $title = $this->character === ' ' ? 'space' : $this->character;
        $title = $title ?? 'all';
        return 'No. of ' . $title . ' chars';
    }

    public function getValue($cellValue): int
    {
        if ($this->character !== null) {
            return substr_count($cellValue, $this->character);
        }

        return strlen($cellValue);
    }
}