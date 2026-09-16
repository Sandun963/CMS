<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Division;
use App\Models\Floor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | MANAGE LOCATIONS PAGE
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $floors = Floor::with([
            'divisions' => function ($query) {
                $query->orderBy('name')
                    ->with([
                        'areas' => function ($query) {
                            $query->orderBy('name');
                        }
                    ]);
            }
        ])
        ->orderBy('name')
        ->get();

        return view(
            'locations.index',
            compact('floors')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FLOOR
    |--------------------------------------------------------------------------
    */

    public function storeFloor(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:floors,name',
            ],
        ]);

        Floor::create([
            'name' => trim($data['name']),
            'is_active' => true,
        ]);

        return back()->with(
            'success',
            'Floor added successfully.'
        );
    }


    public function updateFloor(
        Request $request,
        Floor $floor
    ) {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(
                    'floors',
                    'name'
                )->ignore($floor->id),
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        $floor->update([
            'name' => trim($data['name']),
            'is_active' => $data['is_active'],
        ]);

        return back()->with(
            'success',
            'Floor updated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DIVISION
    |--------------------------------------------------------------------------
    */

    public function storeDivision(Request $request)
    {
        $data = $request->validate([
            'floor_id' => [
                'required',
                'integer',
                'exists:floors,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $exists = Division::where(
                'floor_id',
                $data['floor_id']
            )
            ->where(
                'name',
                trim($data['name'])
            )
            ->exists();

        if ($exists) {

            return back()
                ->withErrors([
                    'division_name' =>
                        'This division already exists on the selected floor.'
                ])
                ->withInput();
        }

        Division::create([
            'floor_id' => $data['floor_id'],
            'name' => trim($data['name']),
            'is_active' => true,
        ]);

        return back()->with(
            'success',
            'Division added successfully.'
        );
    }


    public function updateDivision(
        Request $request,
        Division $division
    ) {
        $data = $request->validate([
            'floor_id' => [
                'required',
                'integer',
                'exists:floors,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        $exists = Division::where(
                'floor_id',
                $data['floor_id']
            )
            ->where(
                'name',
                trim($data['name'])
            )
            ->where(
                'id',
                '!=',
                $division->id
            )
            ->exists();

        if ($exists) {

            return back()
                ->withErrors([
                    'division_name' =>
                        'This division already exists on the selected floor.'
                ])
                ->withInput();
        }

        $division->update([
            'floor_id' => $data['floor_id'],
            'name' => trim($data['name']),
            'is_active' => $data['is_active'],
        ]);

        return back()->with(
            'success',
            'Division updated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AREA
    |--------------------------------------------------------------------------
    */

    public function storeArea(Request $request)
    {
        $data = $request->validate([
            'division_id' => [
                'required',
                'integer',
                'exists:divisions,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $exists = Area::where(
                'division_id',
                $data['division_id']
            )
            ->where(
                'name',
                trim($data['name'])
            )
            ->exists();

        if ($exists) {

            return back()
                ->withErrors([
                    'area_name' =>
                        'This area already exists in the selected division.'
                ])
                ->withInput();
        }

        Area::create([
            'division_id' => $data['division_id'],
            'name' => trim($data['name']),
            'is_active' => true,
        ]);

        return back()->with(
            'success',
            'Area added successfully.'
        );
    }


    public function updateArea(
        Request $request,
        Area $area
    ) {
        $data = $request->validate([
            'division_id' => [
                'required',
                'integer',
                'exists:divisions,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        $exists = Area::where(
                'division_id',
                $data['division_id']
            )
            ->where(
                'name',
                trim($data['name'])
            )
            ->where(
                'id',
                '!=',
                $area->id
            )
            ->exists();

        if ($exists) {

            return back()
                ->withErrors([
                    'area_name' =>
                        'This area already exists in the selected division.'
                ])
                ->withInput();
        }

        $area->update([
            'division_id' => $data['division_id'],
            'name' => trim($data['name']),
            'is_active' => $data['is_active'],
        ]);

        return back()->with(
            'success',
            'Area updated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AJAX - DIVISIONS FOR SELECTED FLOOR
    |--------------------------------------------------------------------------
    |
    | Used by dependent dropdowns:
    |
    | Floor
    |   ↓
    | Division
    |
    | Available to authenticated users through the existing route.
    |
    */

    public function divisions(Floor $floor)
    {
        $divisions = Division::where(
                'floor_id',
                $floor->id
            )
            ->where(
                'is_active',
                true
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        return response()->json(
            $divisions
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AJAX - AREAS FOR SELECTED DIVISION
    |--------------------------------------------------------------------------
    |
    | Used by dependent dropdowns:
    |
    | Division
    |   ↓
    | Area
    |
    | Available to authenticated users through the existing route.
    |
    */

    public function areas(Division $division)
    {
        $areas = Area::where(
                'division_id',
                $division->id
            )
            ->where(
                'is_active',
                true
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        return response()->json(
            $areas
        );
    }
}