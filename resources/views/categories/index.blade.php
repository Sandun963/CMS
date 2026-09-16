@extends('layouts.app')

@section('title', 'Manage Categories')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h4 class="mb-1">
            Manage Categories
        </h4>

        <p class="text-muted mb-0">
            Manage breakdown request categories.
        </p>
    </div>


    <a
        href="{{ route('categories.create') }}"
        class="btn btn-primary"
    >
        <i class="bi bi-plus-circle me-1"></i>
        Add Category
    </a>

</div>


<div class="card stat-card">

    <div class="card-header bg-white">
        <strong>
            Categories
        </strong>
    </div>


    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead>

                    <tr>
                        <th>Category</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th width="120">Action</th>
                    </tr>

                </thead>


                <tbody>

                    @forelse($categories as $category)

                        <tr>

                            <td class="fw-semibold">
                                {{ $category->name }}
                            </td>


                            <td>
                                {{ $category->code }}
                            </td>


                            <td>
                                {{ $category->description ?: '-' }}
                            </td>


                            <td>

                                @if($category->is_active)

                                    <span class="badge bg-success">
                                        Active
                                    </span>

                                @else

                                    <span class="badge bg-secondary">
                                        Inactive
                                    </span>

                                @endif

                            </td>


                            <td>

                                <a
                                    href="{{ route(
                                        'categories.edit',
                                        $category
                                    ) }}"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    <i class="bi bi-pencil"></i>
                                    Edit
                                </a>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td
                                colspan="5"
                                class="text-center text-muted py-4"
                            >
                                No categories found.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection