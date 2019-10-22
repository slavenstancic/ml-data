<?php


namespace App\Parsers;


use App\CellParserInterface;

class PregMatcher implements CellParserInterface
{
    /** @var string */
    private $title;

    /** @var string */
    private $pattern;

    public function __construct(string $title, string $pattern)
    {
        $this->title = $title;
        $this->pattern = $pattern;
    }

    public function getName(): string
    {
        return '# of ' . $this->title;
    }

    public function getValue($cellValue): int
    {
        return preg_match_all($this->pattern, $cellValue);
    }
}