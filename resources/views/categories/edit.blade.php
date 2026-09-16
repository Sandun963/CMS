@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')

<div class="row justify-content-center">

    <div class="col-lg-7">

        <div class="card stat-card">

            <div class="card-header bg-white">

                <strong>
                    Edit Category
                </strong>

            </div>


            <div class="card-body">

                <form
                    method="POST"
                    action="{{ route(
                        'categories.update',
                        $category
                    ) }}"
                >

                    @csrf
                    @method('PUT')


                    {{-- Category Name --}}

                    <div class="mb-3">

                        <label
                            for="name"
                            class="form-label fw-semibold"
                        >
                            Category Name
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="form-control"
                            value="{{ old(
                                'name',
                                $category->name
                            ) }}"
                            required
                        >

                    </div>


                    {{-- Code --}}

                    <div class="mb-3">

                        <label
                            for="code"
                            class="form-label fw-semibold"
                        >
                            Category Code
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="code"
                            id="code"
                            class="form-control"
                            value="{{ old(
                                'code',
                                $category->code
                            ) }}"
                            required
                        >

                    </div>


                    {{-- Description --}}

                    <div class="mb-3">

                        <label
                            for="description"
                            class="form-label"
                        >
                            Description
                        </label>

                        <textarea
                            name="description"
                            id="description"
                            class="form-control"
                            rows="4"
                        >{{ old(
                            'description',
                            $category->description
                        ) }}</textarea>

                    </div>


                    {{-- Active --}}

                    <div class="form-check form-switch mb-4">

                        <input
                            type="hidden"
                            name="is_active"
                            value="0"
                        >

                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="is_active"
                            id="is_active"
                            value="1"
                            @checked(
                                old(
                                    'is_active',
                                    $category->is_active
                                )
                            )
                        >

                        <label
                            class="form-check-label"
                            for="is_active"
                        >
                            Active
                        </label>

                        <div class="form-text">
                            Inactive categories will not appear
                            when users submit new requests.
                        </div>

                    </div>


                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-check-circle me-1"></i>
                            Update Category
                        </button>


                        <a
                            href="{{ route('categories.index') }}"
                            class="btn btn-outline-secondary"
                        >
                            Cancel
                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

@endsection