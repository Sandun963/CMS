<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Division;
use App\Models\Floor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('data/locations.csv');

        if (!file_exists($file)) {
            throw new RuntimeException(
                "Location CSV file not found: {$file}"
            );
        }

        $handle = fopen($file, 'r');

        if ($handle === false) {
            throw new RuntimeException(
                'Unable to open locations.csv'
            );
        }

        // Read the header row.
        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            throw new RuntimeException(
                'locations.csv is empty.'
            );
        }

        // Remove possible UTF-8 BOM from the first heading.
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);

        $header = array_map(
            fn ($value) => trim($value),
            $header
        );

        $requiredColumns = [
            'floor',
            'division',
            'area',
        ];

        foreach ($requiredColumns as $column) {
            if (!in_array($column, $header, true)) {
                fclose($handle);

                throw new RuntimeException(
                    "Required column '{$column}' was not found."
                );
            }
        }

        DB::transaction(function () use ($handle, $header) {

            while (($row = fgetcsv($handle)) !== false) {

                // Skip completely empty rows.
                if (count(array_filter(
                    $row,
                    fn ($value) => trim((string) $value) !== ''
                )) === 0) {
                    continue;
                }

                // Skip malformed rows.
                if (count($row) !== count($header)) {
                    continue;
                }

                $data = array_combine($header, $row);

                $floorName = trim($data['floor'] ?? '');
                $divisionName = trim($data['division'] ?? '');
                $areaName = trim($data['area'] ?? '');

                if (
                    $floorName === '' ||
                    $divisionName === '' ||
                    $areaName === ''
                ) {
                    continue;
                }

                /*
                 * FLOOR
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
                 * DIVISION
                 *
                 * Same division name can exist on different floors,
                 * therefore floor_id is part of the lookup.
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
                 * AREA
                 *
                 * Same area name such as "OFFICE AREA" can exist
                 * under several divisions.
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
            }
        });

        fclose($handle);

        $this->command?->info(
            'Locations imported successfully.'
        );
    }
}