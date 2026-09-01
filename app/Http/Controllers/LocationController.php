<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Division;

class LocationController extends Controller
{
    /**
     * Get divisions belonging to a floor.
     */
    public function divisions($floorId)
    {
        $divisions = Division::where(
                'floor_id',
                $floorId
            )
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name'
            ]);

        return response()->json($divisions);
    }

    /**
     * Get areas belonging to a division.
     */
    public function areas($divisionId)
    {
        $areas = Area::where(
                'division_id',
                $divisionId
            )
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name'
            ]);

        return response()->json($areas);
    }
}