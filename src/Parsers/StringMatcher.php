<?php


namespace App\Parsers;


use App\CellParserInterface;

class StringMatcher implements CellParserInterface
{
    /** @var string */
    private $string;

    public function __construct(string $string)
    {
        $this->string = $string;
    }

    public function getName(): string
    {
        return 'String "' . $this->string . '" in cell';
    }

    public function getValue($cellValue): int
    {
        return substr_count($cellValue, $this->string);
    }
}