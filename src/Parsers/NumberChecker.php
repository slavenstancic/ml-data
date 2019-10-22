<?php

namespace App\Parsers;

use App\CellParserInterface;

class NumberChecker implements CellParserInterface
{
    /** @var PregMatcher */
    private $digits, $words;

    /** @var CharacterCounter */
    private $commas, $spaces, $dots;

    public function __construct()
    {
        $this->digits = new PregMatcher('digits', "/[0-9]/");
        $this->words = new PregMatcher('words', "/[A-Za-z]/i");
        $this->dots = new CharacterCounter('.');
        $this->commas = new CharacterCounter(',');
        $this->spaces = new CharacterCounter(' ');
    }

    public function getName(): string
    {
        return 'Is number';
    }

    public function getValue($cellValue): string
    {
        if ($this->digits->getValue($cellValue) >= 4
            && $this->words->getValue($cellValue) === 0
            && ($this->dots->getValue($cellValue) < 2
                || $this->commas->getValue($cellValue) < 2
                || $this->spaces->getValue($cellValue) < 2)
        ) {
            return 'Yes';
        }

        return 'No';
    }
}