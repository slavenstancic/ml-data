<?php

namespace App\Parsers;

use App\CellParserInterface;

class DateChecker implements CellParserInterface
{
    /** @var PregMatcher */
    private $digits, $words;

    /** @var CharacterCounter */
    private $dots, $slashes;

    public function __construct()
    {
        $this->digits = new PregMatcher('digits', "/[0-9]/");
        $this->words = new PregMatcher('words', "/[A-Za-z]/i");
        $this->dots = new CharacterCounter('.');
        $this->slashes = new CharacterCounter('/');
    }

    public function getName(): string
    {
        return 'Is date';
    }

    public function getValue($cellValue): string
    {
        if ($this->digits->getValue($cellValue) >= 4
            && $this->words->getValue($cellValue) === 0
            && ($this->dots->getValue($cellValue) > 1 || $this->slashes->getValue($cellValue) > 1)
        ) {
            return 'Yes';
        }

        return 'No';
    }
}