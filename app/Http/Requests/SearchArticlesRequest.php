<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchArticlesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword' => 'nullable|string|max:255',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'sources' => 'nullable|array',
            'sources.*' => 'integer|exists:sources,id',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
            'authors' => 'nullable|array',
            'authors.*' => 'integer|exists:authors,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
