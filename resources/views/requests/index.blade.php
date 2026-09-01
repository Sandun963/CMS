@extends('layouts.app')

@section(
    'title',
    auth()->user()->isMinistryUser()
        ? 'My Requests'
        : 'All Requests'
)

@section('content')


{{-- =========================================================
     PAGE HEADER
========================================================= --}}
<div class="d-flex justify-content-between align-items-center mb-3">

    <h4 class="mb-0">
        {{
            auth()->user()->isMinistryUser()
                ? 'My Requests'
                : 'All Requests'
        }}
    </h4>


    @if(auth()->user()->isMinistryUser())

        <a
            href="{{ route('requests.create') }}"
            class="btn btn-primary"
        >
            <i class="bi bi-plus-circle me-1"></i>
            Submit New Request
        </a>

    @endif

</div>



{{-- =========================================================
     FILTERS
========================================================= --}}
<form
    method="GET"
    action="{{ route('requests.index') }}"
    class="card stat-card p-3 mb-3"
>

    <div class="row g-2">


        {{-- Search --}}
        <div class="col-md-3">

            <input
                type="text"
                name="q"
                class="form-control form-control-sm"
                placeholder="Search request no. / title"
                value="{{ request('q') }}"
            >

        </div>


        {{-- Status --}}
        <div class="col-md-2">

            <select
                name="status"
                class="form-select form-select-sm"
            >

                <option value="">
                    All Statuses
                </option>

                @foreach([
                    'New',
                    'Assigned',
                    'In Progress',
                    'Resolved',
                    'Closed',
                    'Reopened'
                ] as $s)

                    <option
                        value="{{ $s }}"
                        @selected(
                            request('status') === $s
                        )
                    >
                        {{ $s }}
                    </option>

                @endforeach

            </select>

        </div>



        {{-- =====================================================
             LOCATION FILTERS
             Only show for non-Ministry users
        ====================================================== --}}
        @if(!auth()->user()->isMinistryUser())


            {{-- Floor --}}
            <div class="col-md-2">

                <select
                    name="floor_id"
                    id="filterFloor"
                    class="form-select form-select-sm"
                >

                    <option value="">
                        All Floors
                    </option>

                    @foreach($floors as $floor)

                        <option
                            value="{{ $floor->id }}"
                            @selected(
                                (string) request('floor_id')
                                ===
                                (string) $floor->id
                            )
                        >
                            {{ $floor->name }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Division --}}
            <div class="col-md-3">

                <select
                    name="division_id"
                    id="filterDivision"
                    class="form-select form-select-sm"
                    {{ request('floor_id') ? '' : 'disabled' }}
                >

                    <option value="">
                        {{
                            request('floor_id')
                                ? 'All Divisions'
                                : 'Select Floor First'
                        }}
                    </option>

                </select>

            </div>


        @endif


        {{-- Filter Button --}}
        <div class="col-md-2">

            <button
                type="submit"
                class="btn btn-sm btn-primary w-100"
            >
                <i class="bi bi-search me-1"></i>
                Filter
            </button>

        </div>


    </div>



    {{-- Clear Filters --}}
    @if(
        request('q')
        || request('status')
        || request('floor_id')
        || request('division_id')
    )

        <div class="mt-2">

            <a
                href="{{ route('requests.index') }}"
                class="btn btn-sm btn-outline-secondary"
            >
                <i class="bi bi-x-circle me-1"></i>
                Clear Filters
            </a>

        </div>

    @endif

</form>



{{-- =========================================================
     REQUEST TABLE
========================================================= --}}
<div class="card stat-card">

    <div class="table-responsive">

        <table class="table mb-0 align-middle">

            <thead>

                <tr>

                    <th>
                        Request No.
                    </th>

                    <th>
                        Title
                    </th>

                    <th>
                        Status
                    </th>

                    <th>
                        Assigned To
                    </th>

                    <th>
                        Date
                    </th>

                    <th></th>

                </tr>

            </thead>


            <tbody>


                @forelse($requests as $r)

                    <tr>


                        {{-- Request Number --}}
                        <td>

                            {{ $r->request_number }}

                        </td>


                        {{-- Title --}}
                        <td>

                            {{ $r->title }}

                        </td>


                        {{-- Status --}}
                        <td>

                            <span
                                class="badge {{
                                    $r->statusBadgeClass()
                                }}"
                            >
                                {{ $r->status }}
                            </span>

                        </td>


                        {{-- Assigned To --}}
                        <td>

                            {{
                                $r->assignedTo?->name
                                ?? '-'
                            }}

                        </td>


                        {{-- Date --}}
                        <td class="small text-muted">

                            {{
                                $r->created_at
                                    ->format('d/m/Y')
                            }}

                        </td>


                        {{-- View --}}
                        <td>

                            <a
                                href="{{
                                    route(
                                        'requests.show',
                                        $r
                                    )
                                }}"
                                class="
                                    btn
                                    btn-sm
                                    btn-outline-primary
                                "
                            >
                                View
                            </a>

                        </td>


                    </tr>


                @empty


                    <tr>

                        <td
                            colspan="6"
                            class="
                                text-center
                                text-muted
                                py-4
                            "
                        >
                            No requests found.
                        </td>

                    </tr>


                @endforelse


            </tbody>

        </table>

    </div>



    {{-- Pagination --}}
    <div class="card-footer bg-white">

        {{ $requests->links() }}

    </div>

</div>



{{-- =========================================================
     FLOOR -> DIVISION FILTER
========================================================= --}}
@if(!auth()->user()->isMinistryUser())

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const floorSelect =
            document.getElementById(
                'filterFloor'
            );

        const divisionSelect =
            document.getElementById(
                'filterDivision'
            );


        const selectedDivisionId =
            @json(request('division_id'));



        /*
        |--------------------------------------------------------------------------
        | Title Case
        |--------------------------------------------------------------------------
        */

        function toTitleCase(text) {

            if (!text) {
                return '';
            }


            const abbreviations = [
                'IT',
                'ICT',
                'ITRDA',
                'HR'
            ];


            return text
                .toLowerCase()
                .replace(
                    /\b\w+/g,
                    function (word) {

                        const upper =
                            word.toUpperCase();


                        if (
                            abbreviations.includes(
                                upper
                            )
                        ) {

                            return upper;

                        }


                        return (
                            word.charAt(0)
                                .toUpperCase()
                            +
                            word.slice(1)
                        );

                    }
                );

        }



        /*
        |--------------------------------------------------------------------------
        | Load Divisions
        |--------------------------------------------------------------------------
        */

        async function loadDivisions(
            floorId,
            selectedId = null
        ) {

            divisionSelect.disabled =
                true;


            /*
            | No floor selected
            */

            if (!floorId) {

                divisionSelect.innerHTML =
                    '<option value="">Select Floor First</option>';

                return;

            }


            /*
            | Loading state
            */

            divisionSelect.innerHTML =
                '<option value="">Loading divisions...</option>';


            try {

                const response =
                    await fetch(
                        `/locations/floors/${floorId}/divisions`,
                        {
                            headers: {
                                'Accept':
                                    'application/json'
                            }
                        }
                    );


                if (!response.ok) {

                    throw new Error(
                        'Unable to load divisions.'
                    );

                }


                const divisions =
                    await response.json();


                divisionSelect.innerHTML =
                    '<option value="">All Divisions</option>';



                /*
                | Add divisions
                */

                divisions.forEach(
                    function (division) {

                        const option =
                            document.createElement(
                                'option'
                            );


                        option.value =
                            division.id;


                        option.textContent =
                            toTitleCase(
                                division.name
                            );


                        /*
                        | Restore selected division
                        */

                        if (
                            selectedId
                            &&
                            String(
                                selectedId
                            )
                            ===
                            String(
                                division.id
                            )
                        ) {

                            option.selected =
                                true;

                        }


                        divisionSelect
                            .appendChild(
                                option
                            );

                    }
                );


                divisionSelect.disabled =
                    false;


            } catch (error) {

                console.error(
                    'Division loading error:',
                    error
                );


                divisionSelect.innerHTML =
                    '<option value="">Unable to load divisions</option>';


                divisionSelect.disabled =
                    true;

            }

        }



        /*
        |--------------------------------------------------------------------------
        | Floor Changed
        |--------------------------------------------------------------------------
        */

        floorSelect.addEventListener(
            'change',
            function () {

                loadDivisions(
                    this.value
                );

            }
        );



        /*
        |--------------------------------------------------------------------------
        | Restore Division After Filtering
        |--------------------------------------------------------------------------
        */

        if (floorSelect.value) {

            loadDivisions(
                floorSelect.value,
                selectedDivisionId
            );

        }

    }
);

</script>

@endif


@endsection