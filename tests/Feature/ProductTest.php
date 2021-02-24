<?php

namespace Tests\Feature;

use App\ProductParser;
use Tests\TestCase;

class ProductTest extends TestCase
{
    /**
     * Test product CSV parsing errors
     *
     * @return void
     * @throws \League\Csv\Exception
     */
    public function testProductCsvFormat()
    {
        $product = new ProductParser();

        // test for valid product
        $result = $product->loadcsv(__DIR__ . '/products-test.csv', "|");

        $this->assertFalse((bool)$result['errors']);

        // test for invalid product errors
        $result = $product->loadcsv(__DIR__ . '/invalid-products-test.csv', "|");

        $this->assertTrue((bool)$result['errors']);
    }
}
