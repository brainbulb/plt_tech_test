<?php

namespace App;

use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class ProductParser
{

    private $rowErrors = [];
    private $rowNumber = 0;
    private $rowSku = null;
    private $result = [];


    /**
     * Loads a CSV file to the database from the provided file path
     *
     * @param string $filepath
     * @param string $delimiter
     * @return array
     * @throws \League\Csv\Exception
     */
    public function loadcsv($filepath, $delimiter)
    {
        $csv = $this->readCsvFile($filepath, $delimiter);
        $rowNumber = 1;

        $this->result = ['rows' => count($csv), 'errors' => 0, 'created' => 0, 'updated' => 0];

        // iterate the rows in the CSV file, save each row to the database if validation  was successful
        foreach ($csv as $record) {

            if ( ! $this->validateRow($rowNumber, $record) ) {
                continue;
            }

            $this->saveRow($record);
        }

        $this->result['errors'] = count($this->rowErrors);

        return $this->result;
    }


    /**
     * @param array $row
     * @return bool
     */
    private function saveRow(array $row)
    {
        $values = [
            'sku' => $row['sku'],
            'description' => $row['description'],
            'normal_price' => $row['normal_price'],
        ];

        if (isset($row['special_price'])) {
            $values['special_price'] = $row['special_price'];
        }

        // attempt to update record first
        $affected = DB::table('products')
            ->where('sku', $row['sku'])
            ->update($values);

        // If update failed attempt to insert
        if ($affected) {
            $this->result['updated'] += 1;
            return true;
        }

        $status = DB::table('products')
            ->insert($values);

        if ($status) {
            $this->result['created'] += 1;
        }

        return true;
    }


    /**
     * Row Validation Rules:
     * All values except for special_price are required
     * When special_price is provided, it should be less than normal_price
     * Negative values are not allowed for either normal_price or special_price
     *
     * @param int $rowNumber
     * @param array $row
     * @return bool
     */
    protected function validateRow(int $rowNumber, array $row)
    {
        $this->setRowNumber($rowNumber);

        // All values except for special_price are required
        if ( ! isset($row['sku'], $row['description'], $row['normal_price'])) {
            return $this->addRowError('sku, description or normal_price fields are missing');
        }

        $this->setRowSku($row['sku']);

        // values should not be blank
        if(trim($row['sku']) == ''  || trim($row['description']) == ''  || trim($row['normal_price']) == '' ) {
            return $this->addRowError('sku, description or normal_price fields are blank');
        }

        $normal_price = number_format($row['normal_price'], 2, '.', '');

        // normal_price should not be negative
        if ($normal_price < 0.00) {
            return $this->addRowError('normal_price should be greater than zero');
        } else if ( ! is_numeric($normal_price) ) {
            return $this->addRowError('normal_price should be a number');
        }

        // When special_price is provided, it should be less than normal_price
        if(isset($row['special_price'])) {

            $special_price = number_format($row['special_price'], 2, '.', '');

            // special_price should not be negative
            if ($special_price < 0.00) {
                return $this->addRowError('special_price should be greater than zero');
            } else if ( ! is_numeric($special_price) ) {
                return $this->addRowError('special_price should be a number');
            }

            // special_price should be less than normal_price
            if ($special_price >= $normal_price) {
                return $this->addRowError('special_price should be less than normal price');
            }
        }

        return true;
    }


    /**
     * Add an error for a particular CSV row of data
     *
     * @param $error
     * @return bool
     */
    private function addRowError($error)
    {
        $rowNumber = $this->getRowNumber();
        $sku = $this->getRowSku();

        $this->rowErrors[] = "row: $rowNumber, sku: $sku, error: $error";

        return false;
    }


    /**
     * Display row errors
     */
    public function getRowErrors()
    {
        return implode(PHP_EOL, $this->rowErrors);
    }


    /**
     * @param $filepath
     * @param string $delimiter
     * @return static
     * @throws \League\Csv\Exception
     */
    private function readCsvFile($filepath, $delimiter = "|")
    {
        $csv = Reader::createFromPath($filepath, 'r');

        $csv->setDelimiter($delimiter);
        $csv->setHeaderOffset(0);

        return $csv;
    }

    /**
     * @return int
     */
    public function getRowNumber(): int
    {
        return $this->rowNumber;
    }

    /**
     * @param int $rowNumber
     */
    public function setRowNumber(int $rowNumber): void
    {
        $this->rowNumber = $rowNumber;
    }

    /**
     * @return null
     */
    public function getRowSku()
    {
        return $this->rowSku;
    }

    /**
     * @param null $rowSku
     */
    public function setRowSku($rowSku): void
    {
        $this->rowSku = $rowSku;
    }
}
