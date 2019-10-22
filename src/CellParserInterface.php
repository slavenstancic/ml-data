<?php

namespace App;

interface CellParserInterface
{
    public function getName(): string;
    public function getValue($cellValue);
}