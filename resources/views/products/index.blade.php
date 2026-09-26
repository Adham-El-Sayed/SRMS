@extends('layouts.app')

@section('title', __('Products'))

@section('content')

    <div class="header">
        <div>
            <h1>{{ __('Products') }}</h1>
            <p>{{ __('Manage your restaurant menu products.') }}</p>
        </div>

        <a href="{{ route('products.create') }}" class="btn">
            {{ __('+ Add Product') }}
        </a>
    </div>


    @if(session('success'))

        <div class="success-message">
            {{ session('success') }}
        </div>

    @endif


    @if($products->isEmpty())

        <div class="empty">

            <h2>{{ __('No Products') }}</h2>

            <p>{{ __('There are currently no products.') }}</p>

            <a href="{{ route('products.create') }}" class="btn">
                {{ __('+ Add Your First Product') }}
            </a>

        </div>

    @else

        <div class="table-wrapper">

            <table>

                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Price') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>


                <tbody>

                    @foreach($products as $product)

                        <tr>

                            <td>
                                <strong>{{ $product->name }}</strong>
                            </td>


                            <td>
                                {{ $product->category?->name ?? '-' }}
                            </td>


                            <td>
                                {{ $product->description ?? '-' }}
                            </td>


                            <td class="price">
                                {{ number_format($product->price, 2) }} {{ __('EGP') }}
                            </td>


                            <td>

                                <span class="status {{ $product->is_active ? 'active' : 'inactive' }}">
                                    {{ __($product->is_active ? 'Active' : 'Inactive') }}
                                </span>

                            </td>


                            <td>

                                <div class="actions">

                                    <a
                                        href="{{ route('products.edit', $product) }}"
                                        class="edit-btn"
                                    >
                                        {{ __('Edit') }}
                                    </a>


                                    <button
                                        type="button"
                                        class="delete-btn"
                                        data-id="{{ $product->id }}"
                                        data-name="{{ $product->name }}"
                                    >
                                        {{ __('Delete') }}
                                    </button>

                                </div>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    @endif


    {{-- Delete Modal --}}
    <div id="deleteModal" class="modal-overlay">

        <div class="delete-modal">

            <div class="modal-icon">
                !
            </div>

            <h2>{{ __('Delete Product?') }}</h2>

            <p>
                {{ __('Are you sure you want to delete') }}
                <strong id="productName"></strong>?
            </p>

            <p class="modal-warning">
                {{ __('This action cannot be undone.') }}
            </p>


            <div class="modal-actions">

                <button
                    type="button"
                    class="cancel-btn"
                    id="cancelDeleteBtn"
                >
                    {{ __('Cancel') }}
                </button>


                <form
                    id="deleteForm"
                    method="POST"
                >

                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="confirm-delete-btn"
                    >
                        {{ __('Yes, Delete') }}
                    </button>

                </form>

            </div>

        </div>

    </div>

@endsection


@push('styles')

<style>

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 30px;
    }

    .header h1 {
        margin: 0;
        font-size: 32px;
    }

    .header p {
        margin: 8px 0 0;
        color: #6b7280;
    }


    .btn {
        display: inline-block;
        padding: 11px 18px;
        background: #2563eb;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: bold;
        white-space: nowrap;
        transition: 0.2s;
    }

    .btn:hover {
        opacity: 0.9;
    }


    .success-message {
        margin-bottom: 20px;
        padding: 14px 18px;
        background: #d1e7dd;
        color: #0f5132;
        border-radius: 10px;
    }


    .table-wrapper {
        background: white;
        border-radius: 14px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        overflow-x: auto;
    }


    table {
        width: 100%;
        border-collapse: collapse;
    }


    th,
    td {
        padding: 16px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }


    th {
        background: #f9fafb;
        color: #6b7280;
        font-size: 14px;
    }


    tr:last-child td {
        border-bottom: none;
    }


    .price {
        font-weight: bold;
        white-space: nowrap;
    }


    .status {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: bold;
    }


    .status.active {
        background: #d1e7dd;
        color: #0f5132;
    }


    .status.inactive {
        background: #f8d7da;
        color: #842029;
    }


    .actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }


    .edit-btn,
    .delete-btn {
        border: none;
        border-radius: 8px;
        padding: 9px 14px;
        font-size: 14px;
        font-weight: bold;
        cursor: pointer;
        text-decoration: none;
        transition: 0.2s;
    }


    .edit-btn {
        background: #2563eb;
        color: white;
    }


    .delete-btn {
        background: #dc2626;
        color: white;
    }


    .edit-btn:hover,
    .delete-btn:hover {
        opacity: 0.9;
    }


    .empty {
        background: white;
        padding: 50px;
        border-radius: 14px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        color: #6b7280;
    }


    .empty h2 {
        color: #1f2937;
    }


    /* =========================
       Delete Modal
    ========================= */

    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
        z-index: 9999;
        padding: 20px;
    }


    .modal-overlay.show {
        display: flex;
    }


    .delete-modal {
        background: white;
        width: 100%;
        max-width: 420px;
        border-radius: 16px;
        padding: 30px;
        text-align: center;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    }


    .modal-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fee2e2;
        color: #dc2626;
        font-size: 32px;
        font-weight: bold;
    }


    .delete-modal h2 {
        margin: 0 0 12px;
        color: #1f2937;
    }


    .delete-modal p {
        color: #6b7280;
        line-height: 1.6;
    }


    .modal-warning {
        font-size: 14px;
        color: #dc2626 !important;
    }


    .modal-actions {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin-top: 25px;
    }


    .cancel-btn,
    .confirm-delete-btn {
        border: none;
        border-radius: 8px;
        padding: 11px 18px;
        font-size: 14px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.2s;
    }


    .cancel-btn {
        background: #e5e7eb;
        color: #374151;
    }


    .confirm-delete-btn {
        background: #dc2626;
        color: white;
    }


    .cancel-btn:hover,
    .confirm-delete-btn:hover {
        opacity: 0.9;
    }


    @media (max-width: 768px) {

        .header {
            flex-direction: column;
            align-items: flex-start;
        }

        .actions {
            flex-wrap: wrap;
        }

        .modal-actions {
            flex-direction: column;
        }

        .modal-actions form {
            width: 100%;
        }

        .cancel-btn,
        .confirm-delete-btn {
            width: 100%;
        }

    }

</style>

@endpush


@push('scripts')

<script>

    const deleteModal = document.getElementById('deleteModal');

    const productName = document.getElementById('productName');

    const deleteForm = document.getElementById('deleteForm');

    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');


    document.querySelectorAll('.delete-btn').forEach(function (button) {

        button.addEventListener('click', function () {

            const id = this.dataset.id;

            const name = this.dataset.name;


            productName.textContent = name;


            deleteForm.action =
                "{{ url('/products') }}/" + id;


            deleteModal.classList.add('show');

        });

    });


    function closeDeleteModal() {

        deleteModal.classList.remove('show');

    }


    cancelDeleteBtn.addEventListener('click', function () {

        closeDeleteModal();

    });


    deleteModal.addEventListener('click', function (event) {

        if (event.target === deleteModal) {

            closeDeleteModal();

        }

    });


    document.addEventListener('keydown', function (event) {

        if (
            event.key === 'Escape' &&
            deleteModal.classList.contains('show')
        ) {

            closeDeleteModal();

        }

    });

</script>

@endpush