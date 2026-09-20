@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')

<div class="page-header">

    <div>
        <h1>Edit Category</h1>
        <p>Update category information.</p>
    </div>

    <a href="{{ route('categories.index') }}" class="back-btn">
        ← Back to Categories
    </a>

</div>


<div class="form-card">

    <form
        action="{{ route('categories.update', $category) }}"
        method="POST"
    >

        @csrf

        @method('PUT')


        <div class="form-group">

            <label for="name">
                Category Name
            </label>

            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $category->name) }}"
                required
            >

            @error('name')
                <p class="error-message">
                    {{ $message }}
                </p>
            @enderror

        </div>


        <div class="form-group">

            <label for="description">
                Description
            </label>

            <textarea
                id="description"
                name="description"
                rows="5"
            >{{ old('description', $category->description) }}</textarea>

            @error('description')
                <p class="error-message">
                    {{ $message }}
                </p>
            @enderror

        </div>


        <div class="checkbox-group">

            <input
                type="checkbox"
                id="is_active"
                name="is_active"
                value="1"
                @checked(old('is_active', $category->is_active))
            >

            <label for="is_active">
                Active Category
            </label>

        </div>


        <div class="form-actions">

            <button type="submit" class="save-btn">
                Update Category
            </button>

            <a
                href="{{ route('categories.index') }}"
                class="cancel-btn"
            >
                Cancel
            </a>

        </div>

    </form>

</div>

@endsection


@push('styles')

<style>

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 30px;
    }

    .page-header h1 {
        margin: 0;
        font-size: 32px;
    }

    .page-header p {
        margin: 8px 0 0;
        color: #6b7280;
    }

    .back-btn {
        padding: 11px 16px;
        background: #e5e7eb;
        color: #374151;
        text-decoration: none;
        border-radius: 8px;
        font-weight: bold;
    }

    .form-card {
        max-width: 700px;
        background: white;
        padding: 30px;
        border-radius: 14px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .form-group {
        margin-bottom: 22px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #374151;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 15px;
        font-family: inherit;
    }

    .form-group textarea {
        resize: vertical;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #2563eb;
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 25px;
    }

    .checkbox-group input {
        width: 18px;
        height: 18px;
    }

    .checkbox-group label {
        font-weight: 500;
    }

    .form-actions {
        display: flex;
        gap: 12px;
    }

    .save-btn,
    .cancel-btn {
        padding: 11px 20px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: bold;
        cursor: pointer;
        text-decoration: none;
    }

    .save-btn {
        border: none;
        background: #2563eb;
        color: white;
    }

    .save-btn:hover {
        opacity: 0.9;
    }

    .cancel-btn {
        background: #e5e7eb;
        color: #374151;
    }

    .error-message {
        margin: 7px 0 0;
        color: #dc2626;
        font-size: 14px;
    }

    @media (max-width: 768px) {

        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .form-card {
            padding: 20px;
        }

    }

</style>

@endpush