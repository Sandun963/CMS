@extends('layouts.app')
@section('title', $breakdownRequest->request_number)
@section('content')

@php
    $user = auth()->user();
    $latestAssignment = $breakdownRequest->assignments->last();
    $latestOfficerAssignment = $latestAssignment?->officerAssignments->last();
    $latestWorkReport = $latestOfficerAssignment?->workReport;
@endphp

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h4 class="mb-1">{{ $breakdownRequest->request_number }} <span class="badge {{ $breakdownRequest->statusBadgeClass() }}">{{ $breakdownRequest->status }}</span></h4>
        <div class="text-muted small">Submitted {{ $breakdownRequest->created_at->format('d M Y, H:i') }} by {{ $breakdownRequest->requestedBy->name }} ({{ $breakdownRequest->department->name ?? '-' }})</div>
    </div>
    <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="row g-3">
    <div class="col-lg-8">

        {{-- Request details --}}
        <div class="card stat-card mb-3">
            <div class="card-header bg-white"><strong>Request Details</strong></div>
            <div class="card-body">
                <h6>{{ $breakdownRequest->title }}</h6>
                <p class="mb-2">{{ $breakdownRequest->description }}</p>
                <div class="row small text-muted">
                    <div class="col-md-4"><strong>Category:</strong> {{ $breakdownRequest->category->name ?? '-' }}</div>
                    <div class="col-md-4"><strong>Location:</strong> {{ $breakdownRequest->location ?? '-' }}</div>
                </div>
                @if($breakdownRequest->attachments->count())
                <hr>
                <div class="small fw-semibold mb-1">Attachments</div>
                @foreach($breakdownRequest->attachments as $att)
                    <a href="{{ $att->url() }}" target="_blank" class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-paperclip"></i> {{ $att->original_name }}</a>
                @endforeach
                @endif
            </div>
        </div>

        {{-- STEP 2: IT Head assigns to Assign Officer --}}
        @if($user->isItHead() && in_array($breakdownRequest->status, ['New', 'Reopened']))
        <div class="card stat-card mb-3 border-start border-4 border-danger">
            <div class="card-header bg-white"><strong>Step 2 — Review & Assign to Officer</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('assignments.store', $breakdownRequest) }}">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small">Assign Officer</label>
                            <select name="assign_officer_id" class="form-select" required>
                                <option value="">Select officer</option>
                                @foreach($assignOfficers as $o)
                                <option value="{{ $o->id }}">{{ $o->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Note (optional)</label>
                            <input type="text" name="note" class="form-control">
                        </div>
                    </div>
                    <button class="btn btn-primary btn-sm mt-3"><i class="bi bi-send me-1"></i>Assign</button>
                </form>
            </div>
        </div>
        @endif

        {{-- STEP 3: Assign Officer forwards to Technical Officer --}}
        @if($user->isAssignOfficer() && $latestAssignment && $latestAssignment->assign_officer_id === $user->id && $latestAssignment->status === 'Pending')
        <div class="card stat-card mb-3 border-start border-4 border-success">
            <div class="card-header bg-white"><strong>Step 3 — Assign Technical Officer</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('officer-assignments.store', $latestAssignment) }}">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-5">
                            <label class="form-label small">Technical Officer</label>
                            <select name="technical_officer_id" class="form-select" required>
                                <option value="">Select technician</option>
                                @foreach($technicalOfficers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} @if($t->specialty)({{ $t->specialty }})@endif</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Due Date (optional)</label>
                            <input type="date" name="due_date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Note (optional)</label>
                            <input type="text" name="note" class="form-control">
                        </div>
                    </div>
                    <button class="btn btn-success btn-sm mt-3"><i class="bi bi-send me-1"></i>Forward to Technician</button>
                </form>
            </div>
        </div>
        @endif

        {{-- STEP 4: Technical Officer files work report --}}
        @if($user->isTechnicalOfficer() && $latestOfficerAssignment && $latestOfficerAssignment->technical_officer_id === $user->id && $latestOfficerAssignment->status !== 'Done')
        <div class="card stat-card mb-3 border-start border-4 border-info">
            <div class="card-header bg-white"><strong>Step 4 — Resolve Breakdown / File Work Report</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('work-reports.store', $latestOfficerAssignment) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">Problem Identified</label>
                        <textarea name="problem_identified" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Work Performed <span class="text-danger">*</span></label>
                        <textarea name="work_performed" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Parts Used (optional)</label>
                        <input type="text" name="parts_used" class="form-control">
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small">Completion Status <span class="text-danger">*</span></label>
                            <select name="completion_status" class="form-select" required>
                                <option value="Done">Done</option>
                                <option value="Not Done">Not Done</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Attach Photos (optional)</label>
                            <input type="file" name="photos[]" class="form-control" multiple accept=".jpg,.jpeg,.png">
                        </div>
                    </div>
                    <div class="mb-2 mt-2">
                        <label class="form-label small">Remarks / Notes</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>
                    <button class="btn btn-info btn-sm mt-2"><i class="bi bi-tools me-1"></i>Submit Work Report</button>
                </form>
            </div>
        </div>
        @endif

        {{-- STEP 5: Ministry User verifies & closes --}}
        @if($user->isMinistryUser() && $breakdownRequest->requested_by === $user->id && $breakdownRequest->status === 'Resolved' && $latestWorkReport && !$latestWorkReport->confirmation)
        <div class="card stat-card mb-3 border-start border-4 border-primary">
            <div class="card-header bg-white"><strong>Step 5 — Verify & Close</strong></div>
            <div class="card-body">
                <p class="small text-muted">The technician reported this as resolved. Please confirm.</p>
                <form method="POST" action="{{ route('confirmations.store', $latestWorkReport) }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">Is the issue resolved? <span class="text-danger">*</span></label>
                        <select name="is_resolved" class="form-select" required>
                            <option value="Yes">Yes — Close Request</option>
                            <option value="No">No — Reopen Request</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Feedback (optional)</label>
                        <textarea name="feedback" class="form-control" rows="2"></textarea>
                    </div>
                    <button class="btn btn-primary btn-sm"><i class="bi bi-check2-circle me-1"></i>Submit Feedback</button>
                </form>
            </div>
        </div>
        @endif

        {{-- Work report history --}}
        @foreach($breakdownRequest->assignments as $assignment)
            @foreach($assignment->officerAssignments as $oa)
                @if($oa->workReport)
                <div class="card stat-card mb-3">
                    <div class="card-header bg-white"><strong>Work Report</strong> — {{ $oa->technicalOfficer->name }}</div>
                    <div class="card-body small">
                        <p><strong>Problem:</strong> {{ $oa->workReport->problem_identified ?: '-' }}</p>
                        <p><strong>Work performed:</strong> {{ $oa->workReport->work_performed }}</p>
                        <p><strong>Parts used:</strong> {{ $oa->workReport->parts_used ?: '-' }}</p>
                        <p><strong>Status:</strong> <span class="badge {{ $oa->workReport->completion_status === 'Done' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $oa->workReport->completion_status }}</span></p>
                        @if($oa->workReport->attachments->count())
                        <div class="mb-1">
                            @foreach($oa->workReport->attachments as $att)
                            <a href="{{ $att->url() }}" target="_blank" class="badge bg-light text-dark border me-1"><i class="bi bi-image"></i> {{ $att->original_name }}</a>
                            @endforeach
                        </div>
                        @endif
                        @if($oa->workReport->confirmation)
                        <hr>
                        <p class="mb-0"><strong>Department confirmation:</strong>
                            <span class="badge {{ $oa->workReport->confirmation->is_resolved === 'Yes' ? 'bg-success' : 'bg-danger' }}">{{ $oa->workReport->confirmation->is_resolved === 'Yes' ? 'Confirmed Resolved' : 'Not Resolved / Reopened' }}</span>
                        </p>
                        @if($oa->workReport->confirmation->feedback)
                        <p class="mb-0 text-muted">"{{ $oa->workReport->confirmation->feedback }}"</p>
                        @endif
                        @endif
                    </div>
                </div>
                @endif
            @endforeach
        @endforeach

    </div>

    <div class="col-lg-4">
        {{-- Assignment chain summary --}}
        <div class="card stat-card mb-3">
            <div class="card-header bg-white"><strong>Assignment Chain</strong></div>
            <ul class="list-group list-group-flush small">
                <li class="list-group-item">
                    <div class="text-muted">Requested by</div>
                    {{ $breakdownRequest->requestedBy->name }}
                </li>
                @if($latestAssignment)
                <li class="list-group-item">
                    <div class="text-muted">Assigned by (IT Head)</div>
                    {{ $latestAssignment->assignedBy->name }} → <strong>{{ $latestAssignment->assignOfficer->name }}</strong>
                </li>
                @endif
                @if($latestOfficerAssignment)
                <li class="list-group-item">
                    <div class="text-muted">Forwarded to Technician</div>
                    <strong>{{ $latestOfficerAssignment->technicalOfficer->name }}</strong>
                    @if($latestOfficerAssignment->due_date)
                    <div class="text-muted">Due: {{ $latestOfficerAssignment->due_date->format('d/m/Y') }}</div>
                    @endif
                </li>
                @endif
            </ul>
        </div>

        {{-- Activity log --}}
        <div class="card stat-card">
            <div class="card-header bg-white"><strong>Activity Log</strong></div>
            <ul class="list-group list-group-flush small">
                @forelse($breakdownRequest->activityLogs as $log)
                <li class="list-group-item">
                    <div>{{ $log->action }}</div>
                    @if($log->details)<div class="text-muted">{{ $log->details }}</div>@endif
                    <div class="text-muted" style="font-size: 0.75rem;">{{ $log->user->name ?? 'System' }} · {{ $log->created_at->diffForHumans() }}</div>
                </li>
                @empty
                <li class="list-group-item text-muted">No activity yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

@endsection
