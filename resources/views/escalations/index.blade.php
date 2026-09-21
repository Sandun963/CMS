@extends('layouts.app')

@section('title', 'Escalated Cases')

@section('content')

    <div
        class="d-flex
            justify-content-between
            align-items-center
            mb-4"
    >

        <h4 class="mb-0">
            Escalated Cases
        </h4>


        <a
            href="{{ route('escalations.summary.pdf') }}"
            class="btn btn-danger"
        >

            <i class="bi bi-file-earmark-pdf me-1"></i>

            Export Summary PDF

        </a>

    </div>


<div class="card stat-card">

    <div class="card-body">

        @if($escalations->isEmpty())

            <div class="text-center text-muted py-4">

                No escalated cases available.

            </div>

        @else

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>
                            <th>Request No.</th>
                            <th>Division</th>
                            <th>Forwarded By</th>
                            <th>Forwarded Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>

                    </thead>

                    <tbody>

                    @foreach($escalations as $escalation)

                        @php
                            $breakdown =
                                $escalation->breakdownRequest;
                        @endphp

                        <tr>

                            <td>
                                {{ $breakdown->request_number }}
                            </td>

                            <td>
                                {{ $breakdown->division->name ?? '-' }}
                            </td>

                            <td>
                                {{ $escalation->forwardedBy->name ?? '-' }}
                            </td>

                            <td>
                                {{ optional(
                                    $escalation->forwarded_at
                                )->format('d M Y h:i A') }}
                            </td>

                            <td>

                                @if(
                                    $escalation->status
                                    === 'Pending Review'
                                )

                                    <span class="badge bg-warning text-dark">

                                        Pending Review

                                    </span>

                                @else

                                    <span class="badge bg-success">

                                        Reviewed

                                    </span>

                                @endif

                            </td>

                            <td>

                                <a
                                    href="{{ route(
                                        'escalations.show',
                                        $escalation
                                    ) }}"
                                    class="btn btn-sm btn-outline-primary"
                                >

                                    Review

                                </a>

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>

        @endif

    </div>

</div>

@endsection