<?php

declare(strict_types=1);

namespace App\Factories\Spreadsheets\Interfaces;

/**
 * Interface AbstractSpreadsheetFactoryInterface
 * @package App\Spreadsheet\Interfaces
 */
interface AbstractSpreadsheetFactoryInterface
{
    /**
     * @param string $className
     * @return SpreadsheetInterface
     */
    public function make(string $className): SpreadsheetInterface;
}
