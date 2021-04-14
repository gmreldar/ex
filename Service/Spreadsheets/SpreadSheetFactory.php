<?php

declare(strict_types=1);

namespace App\Factories\Spreadsheets;

use App\Factories\Spreadsheets\Interfaces\AbstractSpreadsheetFactoryInterface;
use App\Factories\Spreadsheets\Interfaces\SpreadsheetInterface;
use Prophecy\Exception\Doubler\ClassNotFoundException;

/**
 * Class SpreadSheetFactory
 * @package App\Spreadsheet
 */
class SpreadSheetFactory implements AbstractSpreadsheetFactoryInterface
{
    /**
     * @param string $className
     * @return SpreadsheetInterface
     */
    public function make(string $className): SpreadsheetInterface
    {
        $className = __NAMESPACE__ . '\\' . ucfirst($className) . 'Factory';
        if (!class_exists($className)) {
            throw new ClassNotFoundException('Class do not exist', $className);
        }
        return new $className();
    }
}
