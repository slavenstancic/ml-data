<?php

require_once __DIR__ . '/Bootstrap.php';

use App\FileCounter;
use App\Parsers\CharacterCounter;
use App\Parsers\DateChecker;
use App\Parsers\NumberChecker;
use App\Parsers\PregMatcher;
use App\Parsers\StringMatcher;
use App\Parsers\TimeChecker;

$parsers = [
    new CharacterCounter(' '),
    new CharacterCounter('/'),
    new CharacterCounter(':'),
    new CharacterCounter('.'),
    new CharacterCounter(','),
    new CharacterCounter(),
    new StringMatcher('test'),
    new PregMatcher('digits', "/[0-9]/"),
    new PregMatcher('words', "/[A-Za-z]/i"),
    new DateChecker(),
    new TimeChecker(),
    new NumberChecker(),
];

new FileCounter($parsers, true);
