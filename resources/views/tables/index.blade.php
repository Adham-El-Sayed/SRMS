@extends('layouts.app')

@section('title', __('Restaurant Tables'))

@section('content')

    <div class="header">

        <div>
            <h1>{{ __('Restaurant Tables') }}</h1>
            <p>{{ __('Manage your restaurant tables and their availability.') }}</p>
        </div>

        <a href="{{ route('tables.create') }}" class="btn">
            {{ __('+ Add Table') }}
        </a>

    </div>


    @if(session('success'))

        <div class="success-message">
            {{ session('success') }}
        </div>

    @endif

    @if(session('error'))
        <div class="error-message">{{ session('error') }}</div>
    @endif


    @if($tables->count() > 0)

        <div class="tables-grid">

            @foreach($tables as $table)

                <div class="table-card">

                    <div class="table-header">

                        <div>
                            <h2>{{ __('Table') }} {{ $table->number }}</h2>
                            <p class="table-id">
                                {{ __('ID') }}: {{ $table->id }}
                            </p>
                        </div>

                        <span class="status {{ $table->status->value }}">
                            {{ __(ucfirst($table->status->value)) }}
                        </span>

                    </div>


                    <div class="table-info">

                        <div class="info-item">

                            <span class="info-label">
                                {{ __('Capacity') }}
                            </span>

                            <strong>
                                {{ $table->capacity }} {{ __('Seats') }}
                            </strong>

                        </div>


                        <div class="info-item">

                            <span class="info-label">
                                {{ __('QR Token') }}
                            </span>

                            <strong class="token">
                                {{ Str::limit($table->qr_token, 18) }}
                            </strong>

                        </div>

                    </div>


                    <div class="actions">

                        <a
                            href="{{ route('tables.qr', $table) }}"
                            class="qr-btn"
                        >
                            {{ __('QR Code') }}
                        </a>


                        <a
                            href="{{ route('tables.edit', $table) }}"
                            class="edit-btn"
                        >
                            {{ __('Edit') }}
                        </a>


                        <button
                            type="button"
                            class="delete-btn"
                            data-id="{{ $table->id }}"
                            data-name="Table {{ $table->number }}"
                        >
                            {{ __('Delete') }}
                        </button>

                    </div>

                </div>

            @endforeach

        </div>

    @else

        <div class="empty">

            <h2>{{ __('No Tables') }}</h2>

            <p>
                {{ __('There are currently no restaurant tables.') }}
            </p>

            <a href="{{ route('tables.create') }}" class="btn">
                {{ __('+ Add Your First Table') }}
            </a>

        </div>

    @endif


    {{-- Delete Modal --}}
    <div id="deleteModal" class="modal-overlay">

        <div class="delete-modal">

            <div class="modal-icon">
                !
            </div>

            <h2>{{ __('Delete Table?') }}</h2>

            <p>
                {{ __('Are you sure you want to delete') }}
                <strong id="tableName"></strong>?
            </p>

            <p class="modal-warning">
                {{ __('This action cannot be undone.') }}
            </p>


            <div class="modal-actions">

                <button
                    type="button"
                    class="cancel-btn"
                    onclick="closeDeleteModal()"
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

    /* =========================
       Header
    ========================= */

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
        color: #1f2937;
    }

    .header p {
        margin: 8px 0 0;
        color: #6b7280;
    }


    /* =========================
       Main Button
    ========================= */

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


    /* =========================
       Success Message
    ========================= */

    .success-message {
        margin-bottom: 20px;
        padding: 14px 18px;
        background: #d1e7dd;
        color: #0f5132;
        border-radius: 10px;
    }


    /* =========================
       Tables Grid
    ========================= */

    .tables-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }


    /* =========================
       Table Card
    ========================= */

    .table-card {
        background: white;
        padding: 22px;
        border-radius: 14px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: 0.2s;
    }

    .table-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }


    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 20px;
    }

    .table-header h2 {
        margin: 0;
        color: #1f2937;
        font-size: 22px;
    }

    .table-id {
        margin: 6px 0 0;
        font-size: 13px;
        color: #9ca3af;
    }


    /* =========================
       Status
    ========================= */

    .status {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: bold;
        white-space: nowrap;
    }

    .status.available {
        background: #d1e7dd;
        color: #0f5132;
    }

    .status.occupied {
        background: #f8d7da;
        color: #842029;
    }

    .status.reserved {
        background: #fff3cd;
        color: #856404;
    }


    /* =========================
       Table Information
    ========================= */

    .table-info {
        border-top: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
        padding: 15px 0;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        margin-bottom: 12px;
    }

    .info-item:last-child {
        margin-bottom: 0;
    }

    .info-label {
        color: #6b7280;
        font-size: 14px;
    }

    .info-item strong {
        color: #1f2937;
    }

    .token {
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 13px;
    }


    /* =========================
       Actions
    ========================= */

    .actions {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .qr-btn,
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

    .qr-btn {
        background: #64748b;
        color: white;
    }

    .edit-btn {
        background: #2563eb;
        color: white;
    }

    .delete-btn {
        background: #dc2626;
        color: white;
    }

    .qr-btn:hover,
    .edit-btn:hover,
    .delete-btn:hover {
        opacity: 0.9;
    }


    /* =========================
       Empty State
    ========================= */

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
        margin-top: 0;
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


    /* =========================
       Responsive
    ========================= */

    @media (max-width: 768px) {

        .header {
            flex-direction: column;
            align-items: flex-start;
        }

        .header h1 {
            font-size: 26px;
        }

        .tables-grid {
            grid-template-columns: 1fr;
        }

        .modal-actions {
            flex-direction: column;
        }

        .cancel-btn,
        .confirm-delete-btn,
        #deleteForm {
            width: 100%;
        }

    }

</style>

@endpush


@push('scripts')

<script>

    document.addEventListener('DOMContentLoaded', function () {

        const deleteButtons =
            document.querySelectorAll('.delete-btn');

        deleteButtons.forEach(function (button) {

            button.addEventListener('click', function () {

                openDeleteModal(
                    this.dataset.id,
                    this.dataset.name
                );

            });

        });

    });


    function openDeleteModal(id, name) {

        const modal =
            document.getElementById('deleteModal');

        const tableName =
            document.getElementById('tableName');

        const deleteForm =
            document.getElementById('deleteForm');


        tableName.textContent = name;


        deleteForm.action =
            "{{ url('/tables') }}/" + id;


        modal.classList.add('show');

    }


    function closeDeleteModal() {

        const modal =
            document.getElementById('deleteModal');

        modal.classList.remove('show');

    }


    document
        .getElementById('deleteModal')
        .addEventListener('click', function (event) {

            if (event.target === this) {

                closeDeleteModal();

            }

        });

</script>

@endpush