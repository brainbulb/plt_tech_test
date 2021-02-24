<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\ProductParser;

if (!ini_get("auto_detect_line_endings")) {
    ini_set("auto_detect_line_endings", '1');
}

class Product extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:load {filepath} {--errors}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parses a product CSV file and inserts / updates the database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $filepath = $this->argument('filepath');
        $showRowErrors = $this->option('errors');
        $delimiter = "|";

        $product = new ProductParser();

        try {

            $result = $product->loadcsv($filepath, $delimiter);

            $this->info("File Path: $filepath");
            $this->info('Total Rows: ' . $result['rows']);
            $this->info('Rows With Errors: ' . $result['errors']);
            $this->info('Products Created: ' . $result['created']);
            $this->info('Products Updated: ' . $result['updated']);

            if ($showRowErrors) {
                $this->error($product->getRowErrors());
            }

        } catch (\Exception $e) {
            $this->error('Caught exception: ' . $e->getMessage());
        }
    }
}
