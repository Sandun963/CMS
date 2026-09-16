@extends('layouts.app')

@section('title', 'Manage Locations')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Manage Locations</h4>
        <div class="text-muted">
            Manage floors, divisions and areas.
        </div>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


{{-- =========================================================
     ADD NEW LOCATION
========================================================= --}}

<div class="row g-3 mb-4">

    {{-- ADD FLOOR --}}
    <div class="col-lg-4">
        <div class="card stat-card h-100">
            <div class="card-header bg-white">
                <strong>
                    <i class="bi bi-building me-1"></i>
                    Add Floor
                </strong>
            </div>

            <div class="card-body">

                <form
                    method="POST"
                    action="{{ route('locations.floors.store') }}"
                >
                    @csrf

                    <label class="form-label">
                        Floor Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        class="form-control mb-3"
                        placeholder="Example: 16th Floor"
                        required
                    >

                    <button class="btn btn-primary w-100">
                        <i class="bi bi-plus-circle me-1"></i>
                        Add Floor
                    </button>
                </form>

            </div>
        </div>
    </div>


    {{-- ADD DIVISION --}}
    <div class="col-lg-4">
        <div class="card stat-card h-100">
            <div class="card-header bg-white">
                <strong>
                    <i class="bi bi-diagram-3 me-1"></i>
                    Add Division
                </strong>
            </div>

            <div class="card-body">

                <form
                    method="POST"
                    action="{{ route('locations.divisions.store') }}"
                >
                    @csrf

                    <label class="form-label">
                        Floor
                    </label>

                    <select
                        name="floor_id"
                        class="form-select mb-3"
                        required
                    >
                        <option value="">
                            Select Floor
                        </option>

                        @foreach($floors as $floor)
                            @if($floor->is_active)
                                <option value="{{ $floor->id }}">
                                    {{ $floor->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>

                    <label class="form-label">
                        Division Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        class="form-control mb-3"
                        placeholder="Enter division name"
                        required
                    >

                    <button class="btn btn-primary w-100">
                        <i class="bi bi-plus-circle me-1"></i>
                        Add Division
                    </button>
                </form>

            </div>
        </div>
    </div>


    {{-- ADD AREA --}}
    <div class="col-lg-4">
        <div class="card stat-card h-100">
            <div class="card-header bg-white">
                <strong>
                    <i class="bi bi-geo-alt me-1"></i>
                    Add Area
                </strong>
            </div>

            <div class="card-body">

                <form
                    method="POST"
                    action="{{ route('locations.areas.store') }}"
                >
                    @csrf

                    <label class="form-label">
                        Floor
                    </label>

                    <select
                        id="areaFloor"
                        class="form-select mb-3"
                        required
                    >
                        <option value="">
                            Select Floor
                        </option>

                        @foreach($floors as $floor)
                            @if($floor->is_active)
                                <option value="{{ $floor->id }}">
                                    {{ $floor->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>

                    <label class="form-label">
                        Division
                    </label>

                    <select
                        name="division_id"
                        id="areaDivision"
                        class="form-select mb-3"
                        required
                        disabled
                    >
                        <option value="">
                            Select Floor First
                        </option>
                    </select>

                    <label class="form-label">
                        Area Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        class="form-control mb-3"
                        placeholder="Example: Meeting Room"
                        required
                    >

                    <button class="btn btn-primary w-100">
                        <i class="bi bi-plus-circle me-1"></i>
                        Add Area
                    </button>

                </form>

            </div>
        </div>
    </div>

</div>


{{-- =========================================================
     EXISTING LOCATIONS
========================================================= --}}

<div class="card stat-card">

    <div class="card-header bg-white">
        <strong>Existing Locations</strong>
    </div>

    <div class="card-body">

        @forelse($floors as $floor)

            <div class="border rounded mb-3">

                {{-- FLOOR --}}
                <div class="p-3 bg-light d-flex
                            justify-content-between
                            align-items-center">

                    <div>
                        <strong>
                            <i class="bi bi-building me-1"></i>
                            {{ $floor->name }}
                        </strong>

                        @if($floor->is_active)
                            <span class="badge bg-success ms-2">
                                Active
                            </span>
                        @else
                            <span class="badge bg-secondary ms-2">
                                Inactive
                            </span>
                        @endif
                    </div>

                    <button
                        class="btn btn-sm btn-outline-primary"
                        data-bs-toggle="collapse"
                        data-bs-target="#floor{{ $floor->id }}"
                    >
                        View
                    </button>

                </div>


                <div
                    class="collapse"
                    id="floor{{ $floor->id }}"
                >

                    <div class="p-3">

                        {{-- EDIT FLOOR --}}
                        <form
                            method="POST"
                            action="{{ route(
                                'locations.floors.update',
                                $floor
                            ) }}"
                            class="row g-2 mb-4"
                        >
                            @csrf
                            @method('PUT')

                            <div class="col-md-7">
                                <input
                                    type="text"
                                    name="name"
                                    value="{{ $floor->name }}"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <div class="col-md-3">
                                <select
                                    name="is_active"
                                    class="form-select"
                                >
                                    <option
                                        value="1"
                                        @selected($floor->is_active)
                                    >
                                        Active
                                    </option>

                                    <option
                                        value="0"
                                        @selected(!$floor->is_active)
                                    >
                                        Inactive
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <button
                                    class="btn btn-outline-primary w-100"
                                >
                                    Save
                                </button>
                            </div>
                        </form>


                        {{-- DIVISIONS --}}
                        @forelse($floor->divisions as $division)

                            <div class="border rounded mb-3 p-3">

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'locations.divisions.update',
                                        $division
                                    ) }}"
                                    class="row g-2 align-items-center mb-3"
                                >
                                    @csrf
                                    @method('PUT')

                                    <input
                                        type="hidden"
                                        name="floor_id"
                                        value="{{ $floor->id }}"
                                    >

                                    <div class="col-md-7">

                                        <label class="small text-muted">
                                            Division
                                        </label>

                                        <input
                                            type="text"
                                            name="name"
                                            value="{{ $division->name }}"
                                            class="form-control"
                                            required
                                        >

                                    </div>

                                    <div class="col-md-3">

                                        <label class="small text-muted">
                                            Status
                                        </label>

                                        <select
                                            name="is_active"
                                            class="form-select"
                                        >
                                            <option
                                                value="1"
                                                @selected($division->is_active)
                                            >
                                                Active
                                            </option>

                                            <option
                                                value="0"
                                                @selected(!$division->is_active)
                                            >
                                                Inactive
                                            </option>
                                        </select>

                                    </div>

                                    <div class="col-md-2">

                                        <label class="small text-muted">
                                            &nbsp;
                                        </label>

                                        <button
                                            class="btn btn-outline-primary w-100"
                                        >
                                            Save
                                        </button>

                                    </div>

                                </form>


                                {{-- AREAS --}}

                                <div class="ms-md-4">

                                    @forelse($division->areas as $area)

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'locations.areas.update',
                                                $area
                                            ) }}"
                                            class="row g-2
                                                   align-items-center
                                                   mb-2"
                                        >
                                            @csrf
                                            @method('PUT')

                                            <input
                                                type="hidden"
                                                name="division_id"
                                                value="{{ $division->id }}"
                                            >

                                            <div class="col-md-7">

                                                <div class="input-group">

                                                    <span class="input-group-text">
                                                        <i class="bi bi-geo-alt"></i>
                                                    </span>

                                                    <input
                                                        type="text"
                                                        name="name"
                                                        value="{{ $area->name }}"
                                                        class="form-control"
                                                        required
                                                    >

                                                </div>

                                            </div>

                                            <div class="col-md-3">

                                                <select
                                                    name="is_active"
                                                    class="form-select"
                                                >
                                                    <option
                                                        value="1"
                                                        @selected($area->is_active)
                                                    >
                                                        Active
                                                    </option>

                                                    <option
                                                        value="0"
                                                        @selected(!$area->is_active)
                                                    >
                                                        Inactive
                                                    </option>
                                                </select>

                                            </div>

                                            <div class="col-md-2">

                                                <button
                                                    class="btn btn-sm
                                                           btn-outline-secondary
                                                           w-100"
                                                >
                                                    Save
                                                </button>

                                            </div>

                                        </form>

                                    @empty

                                        <div class="text-muted small">
                                            No areas under this division.
                                        </div>

                                    @endforelse

                                </div>

                            </div>

                        @empty

                            <div class="text-muted">
                                No divisions under this floor.
                            </div>

                        @endforelse

                    </div>

                </div>

            </div>

        @empty

            <div class="text-muted text-center py-4">
                No locations found.
            </div>

        @endforelse

    </div>
</div>


{{-- =========================================================
     FLOOR -> DIVISION DATA FOR ADD AREA
========================================================= --}}



@endsection