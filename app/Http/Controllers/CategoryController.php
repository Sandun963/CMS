<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display all categories.
     */
    public function index()
    {
        $categories = Category::orderBy('name')->get();

        return view(
            'categories.index',
            compact('categories')
        );
    }


    /**
     * Show create category page.
     */
    public function create()
    {
        return view('categories.create');
    }


    /**
     * Store new category.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:categories,name',
            ],

            'code' => [
                'required',
                'string',
                'max:50',
                'unique:categories,code',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);


        Category::create([
            'name' => $data['name'],
            'code' => strtoupper($data['code']),
            'description' => $data['description'] ?? null,
            'is_active' => true,
        ]);


        return redirect()
            ->route('categories.index')
            ->with(
                'success',
                'Category created successfully.'
            );
    }


    /**
     * Show edit category page.
     */
    public function edit(Category $category)
    {
        return view(
            'categories.edit',
            compact('category')
        );
    }


    /**
     * Update category.
     */
    public function update(
        Request $request,
        Category $category
    ) {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',

                Rule::unique(
                    'categories',
                    'name'
                )->ignore($category->id),
            ],

            'code' => [
                'required',
                'string',
                'max:50',

                Rule::unique(
                    'categories',
                    'code'
                )->ignore($category->id),
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);


        $category->update([
            'name' => $data['name'],
            'code' => strtoupper($data['code']),
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);


        return redirect()
            ->route('categories.index')
            ->with(
                'success',
                'Category updated successfully.'
            );
    }
}