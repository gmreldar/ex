<?php

declare(strict_types=1);

namespace App\Factories\Spreadsheets;

use Google\Exception;
use App\Traits\GoogleFactoryTrait;
use Google_Service_Sheets_Request;
use Google_Service_Sheets_ValueRange;
use Google_Service_Sheets_Spreadsheet;
use Google_Service_Sheets_AddSheetRequest;
use Google_Service_Sheets_SheetProperties;
use Google_Service_Sheets_AppendValuesResponse;
use Google_Service_Sheets_SpreadsheetProperties;
use App\Factories\Spreadsheets\Interfaces\SpreadsheetInterface;
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;
use Google_Service_Sheets_UpdateSpreadsheetPropertiesRequest;

/**
 * Class GoogleFactory
 * @package App\Spreadsheet
 */
class GoogleFactory implements SpreadsheetInterface
{
    use GoogleFactoryTrait;

    public const CONFIG_SHEET_ID = 'google/sheet/id';
    public const CONFIG_SHEET_URL = 'google/sheet/url';

    /**
     * Create new spreadsheet
     * @return Google_Service_Sheets_Spreadsheet
     * @throws Exception
     */
    public function createSpreadsheet()
    {
        $service = $this->getService();
        $spreadsheetProperties = new Google_Service_Sheets_SpreadsheetProperties();
        $spreadsheetProperties->setTitle(setting('admin.sheet_title'));
        $spreadsheet = new Google_Service_Sheets_Spreadsheet();
        $spreadsheet->setProperties($spreadsheetProperties);
        $spreadsheet = $service->spreadsheets->create($spreadsheet);
        setValue(self::CONFIG_SHEET_ID, $spreadsheet->getSpreadsheetId());
        setValue(self::CONFIG_SHEET_URL, $spreadsheet->getSpreadsheetUrl());
        $this->renameDefaultSheet($spreadsheet);
        $this->write($this->getDefaultData());
        return $spreadsheet;
    }

    /**
     * Create new Sheet
     * @return Google_Service_Sheets_Spreadsheet
     * @throws Exception
     */
    public function createSheet()
    {
        $sheetProperties = new Google_Service_Sheets_SheetProperties();
        $sheetProperties->setTitle($this->getListName());
        $addSheetRequests = new Google_Service_Sheets_AddSheetRequest();
        $addSheetRequests->setProperties($sheetProperties);
        $sheetRequests = new Google_Service_Sheets_Request();
        $sheetRequests->setAddSheet($addSheetRequests);
        $requests = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest();
        $requests->setRequests($sheetRequests);
        $service = $this->getService();
        $service->spreadsheets->batchUpdate(getValue(self::CONFIG_SHEET_ID), $requests);
        $this->write($this->getDefaultData());
        $this->renameSpreadSheet($this->getListName());
        return $this->getSpreadsheet();
    }

    /**
     * Rename Spreadsheet
     * @param $title
     * @return bool
     * @throws Exception
     */
    public function renameSpreadSheet($title)
    {
        $spreadsheetProperties = $this->getSpreadsheet()->getProperties();
        if ($this->checkListTitle($title, $spreadsheetProperties->getTitle())) {
            return false;
        }
        $spreadsheetProperties->setTitle($title);

        $updateSheetRequests = new Google_Service_Sheets_UpdateSpreadsheetPropertiesRequest();
        $updateSheetRequests->setProperties($spreadsheetProperties);
        $updateSheetRequests->setFields('title');
        $themeColors = $updateSheetRequests->getProperties()->getSpreadsheetTheme()->getThemeColors();
        foreach ($themeColors as $key => $themeColor) {
            if (!is_null($colorStyle = $this->setDefaultColor($themeColor))) {
                $updateSheetRequests->getProperties()->getSpreadsheetTheme()->getThemeColors()[$key]
                    ->setColor($colorStyle);
            }
        }
        $sheetRequests = new Google_Service_Sheets_Request();
        $sheetRequests->setUpdateSpreadsheetProperties($updateSheetRequests);

        $requests = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest();
        $requests->setRequests($sheetRequests);
        $service = $this->getService();
        $service->spreadsheets->batchUpdate(getValue(self::CONFIG_SHEET_ID), $requests);
        return true;
    }

    /**
     * @return Google_Service_Sheets_Spreadsheet
     * @throws Exception
     */
    public function getSpreadsheet()
    {
        $service = $this->getService();
        return $service->spreadsheets->get(getValue(self::CONFIG_SHEET_ID));
    }

    /**
     * Write new data to sheet
     * @param $values
     * @return Google_Service_Sheets_AppendValuesResponse
     * @throws Exception
     */
    public function write($values)
    {
        $spreadsheet = $this->getSpreadsheet();
        $sheetProperties = $spreadsheet->getSheets()[array_key_last($spreadsheet->getSheets())]->getProperties();
        if (!$this->checkListTitle($sheetProperties->getTitle())) {
            $this->createSheet();
        }
        $spreadSheet = $this->getService();
        $valueRange = new Google_Service_Sheets_ValueRange();
        $valueRange->setValues($values);
        $options = ['valueInputOption' => 'USER_ENTERED'];
        return $spreadSheet->spreadsheets_values
            ->append(getValue(self::CONFIG_SHEET_ID), $this->getListName(), $valueRange, $options);
    }
}
