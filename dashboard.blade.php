@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <h6 class="card-title">Total Kategori</h6>
                <h2 class="mb-0">{{ $totalCategories }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <h6 class="card-title">Total Buku</h6>
                <h2 class="mb-0">{{ $totalBooks }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <h6 class="card-title">Barang Masuk</h6>
                <h2 class="mb-0">{{ $totalStockIns ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <h6 class="card-title">Barang Keluar</h6>
                <h2 class="mb-0">{{ $totalStockOuts ?? 0 }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row mt-2">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Peringatan: Stok Buku Hampir Habis (< 10)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Buku</th>
                                <th>Judul</th>
                                <th>Kategori</th>
                                <th>Stok Tersisa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockBooks as $book)
                            <tr>
                                <td>{{ $book->code }}</td>
                                <td>{{ $book->title }}</td>
                                <td>{{ $book->category->name ?? '-' }}</td>
                                <td><span class="badge bg-danger">{{ $book->stock }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-3">Semua stok buku dalam kondisi aman.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
