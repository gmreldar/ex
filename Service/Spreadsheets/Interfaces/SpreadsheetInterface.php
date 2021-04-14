<?php

declare(strict_types=1);

namespace App\Factories\Spreadsheets\Interfaces;

use Google_Service_Sheets_AppendValuesResponse;
use Google_Service_Sheets_Spreadsheet;

/**
 * Interface SpreadsheetInterface
 * @package App\Spreadsheet
 */
interface SpreadsheetInterface
{
    /**
     * Create new spreadsheet
     * @return Google_Service_Sheets_Spreadsheet
     */
    public function createSpreadsheet();

    /**
     * Create new Sheet
     * @return Google_Service_Sheets_Spreadsheet
     */
    public function createSheet();

    /**
     * Rename Spreadsheet
     * @param $title
     * @return bool
     */
    public function renameSpreadSheet($title);

    /**
     * Get all data about spreadsheet
     * @return Google_Service_Sheets_Spreadsheet
     */
    public function getSpreadsheet();

    /**
     * Write new data to sheet
     * @param $values
     * @return Google_Service_Sheets_AppendValuesResponse
     */
    public function write($values);
}
