<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\Division;
use App\Models\Floor;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportLocations extends Command
{
    protected $signature = 'locations:import';

    protected $description = 'Import floors, divisions and areas from Excel';

    public function handle(): int
    {
        $filePath = storage_path('app/locations.xlsx');

        /*
        |--------------------------------------------------------------------------
        | Check File
        |--------------------------------------------------------------------------
        */

        if (! file_exists($filePath)) {

            $this->error('Excel file not found:');
            $this->error($filePath);

            return Command::FAILURE;
        }


        $this->info('Reading Excel file...');


        /*
        |--------------------------------------------------------------------------
        | Read Excel
        |--------------------------------------------------------------------------
        */

        try {

            $spreadsheet = IOFactory::load($filePath);

            $worksheet = $spreadsheet->getActiveSheet();

            $rows = $worksheet->toArray(
                null,
                true,
                true,
                false
            );

        } catch (\Throwable $e) {

            $this->error('Unable to read Excel file.');

            $this->error($e->getMessage());

            return Command::FAILURE;
        }


        if (count($rows) <= 1) {

            $this->error(
                'Excel file contains no data rows.'
            );

            return Command::FAILURE;
        }


        /*
        |--------------------------------------------------------------------------
        | Remove Header
        |--------------------------------------------------------------------------
        |
        | Excel structure:
        |
        | 0 = floor_id
        | 1 = floor
        | 2 = division_id
        | 3 = division
        | 4 = area_id
        | 5 = area
        |
        | We DO NOT use the old IDs.
        | MySQL will create new IDs automatically.
        |
        */

        array_shift($rows);


        $this->info('Import started...');


        $processedRows = 0;
        $skippedRows = 0;


        /*
        |--------------------------------------------------------------------------
        | Process Excel Rows
        |--------------------------------------------------------------------------
        */

        foreach ($rows as $index => $row) {

            $excelRowNumber = $index + 2;


            /*
            |--------------------------------------------------------------------------
            | Read Names
            |--------------------------------------------------------------------------
            */

            $floorName = trim(
                (string) ($row[1] ?? '')
            );

            $divisionName = trim(
                (string) ($row[3] ?? '')
            );

            $areaName = trim(
                (string) ($row[5] ?? '')
            );


            /*
            |--------------------------------------------------------------------------
            | Skip Incomplete Rows
            |--------------------------------------------------------------------------
            */

            if (
                $floorName === '' ||
                $divisionName === '' ||
                $areaName === ''
            ) {

                $skippedRows++;

                $this->warn(
                    "Skipped Excel row {$excelRowNumber}"
                );

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Floor
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | 01st Floor
            |
            | If it already exists, use it.
            | Otherwise create it.
            |
            */

            $floor = Floor::firstOrCreate(
                [
                    'name' => $floorName,
                ],
                [
                    'is_active' => true,
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Division
            |--------------------------------------------------------------------------
            |
            | A division is unique inside a floor.
            |
            | Example:
            |
            | floor_id = 1
            | name     = BNR
            |
            */

            $division = Division::firstOrCreate(
                [
                    'floor_id' => $floor->id,
                    'name' => $divisionName,
                ],
                [
                    'is_active' => true,
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Area
            |--------------------------------------------------------------------------
            |
            | An area is unique inside a division.
            |
            */

            Area::firstOrCreate(
                [
                    'division_id' => $division->id,
                    'name' => $areaName,
                ],
                [
                    'is_active' => true,
                ]
            );


            $processedRows++;
        }


        /*
        |--------------------------------------------------------------------------
        | Final Results
        |--------------------------------------------------------------------------
        */

        $this->newLine();

        $this->info(
            'Location import completed successfully.'
        );


        $this->table(
            [
                'Type',
                'Records'
            ],
            [
                [
                    'Floors',
                    Floor::count()
                ],
                [
                    'Divisions',
                    Division::count()
                ],
                [
                    'Areas',
                    Area::count()
                ],
                [
                    'Processed Excel Rows',
                    $processedRows
                ],
                [
                    'Skipped Excel Rows',
                    $skippedRows
                ],
            ]
        );


        return Command::SUCCESS;
    }
}