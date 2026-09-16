@extends('layouts.app')

@section('title', 'Add Category')

@section('content')

<div class="row justify-content-center">

    <div class="col-lg-7">

        <div class="card stat-card">

            <div class="card-header bg-white">

                <strong>
                    Add Category
                </strong>

            </div>


            <div class="card-body">

                <form
                    method="POST"
                    action="{{ route('categories.store') }}"
                >

                    @csrf


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
                            value="{{ old('name') }}"
                            placeholder="Example: Printer Not Working"
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
                            value="{{ old('code') }}"
                            placeholder="Example: PRINTER"
                            required
                        >

                        <div class="form-text">
                            Use a short unique code.
                        </div>

                    </div>


                    {{-- Description --}}

                    <div class="mb-4">

                        <label
                            for="description"
                            class="form-label"
                        >
                            Description
                            <span class="text-muted">
                                (optional)
                            </span>
                        </label>

                        <textarea
                            name="description"
                            id="description"
                            class="form-control"
                            rows="4"
                        >{{ old('description') }}</textarea>

                    </div>


                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-check-circle me-1"></i>
                            Save Category
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