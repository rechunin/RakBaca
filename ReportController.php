<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockIn;
use App\Models\StockOut;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;
use App\Models\Category;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->type ?? 'in';

        // Determine date range: prefer explicit start/end, otherwise support month/year
        if ($request->filled('month') && $request->filled('year')) {
            $startDate = Carbon::createFromDate($request->year, $request->month, 1)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::createFromDate($request->year, $request->month, 1)->endOfMonth()->format('Y-m-d');
        } else {
            $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = $request->end_date ?? Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        $categoryId = $request->category_id;

        $query = ($type == 'in') ? StockIn::with('book') : StockOut::with('book');
        $query = $query->whereBetween('date', [$startDate, $endDate]);

        if ($categoryId) {
            $query = $query->whereHas('book', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        $data = $query->get();

        $categories = Category::oldest()->get();

        return view('reports.index', compact('data', 'startDate', 'endDate', 'type', 'categories'));
    }

    public function exportPdf(Request $request)
    {
        $type = $request->type;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        if ($request->filled('month') && $request->filled('year')) {
            $startDate = Carbon::createFromDate($request->year, $request->month, 1)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::createFromDate($request->year, $request->month, 1)->endOfMonth()->format('Y-m-d');
        }

        $categoryId = $request->category_id;

        $query = ($type == 'in') ? StockIn::with('book') : StockOut::with('book');
        $query = $query->whereBetween('date', [$startDate, $endDate]);
        if ($categoryId) {
            $query = $query->whereHas('book', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        $data = $query->get();
        $title = $type == 'in' ? 'Laporan Barang Masuk' : 'Laporan Barang Keluar';

        $pdf = Pdf::loadView('reports.pdf', compact('data', 'title', 'startDate', 'endDate'));
        return $pdf->download("laporan_{$type}_{$startDate}_{$endDate}.pdf");
    }

    public function exportExcel(Request $request)
    {
        $type = $request->type;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        if ($request->filled('month') && $request->filled('year')) {
            $startDate = Carbon::createFromDate($request->year, $request->month, 1)->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::createFromDate($request->year, $request->month, 1)->endOfMonth()->format('Y-m-d');
        }

        $categoryId = $request->category_id;

        return Excel::download(new class($startDate, $endDate, $type, $categoryId) implements FromCollection, WithHeadings, WithMapping {
            private $startDate, $endDate, $type;
            private $categoryId;

            public function __construct($startDate, $endDate, $type, $categoryId = null) {
                $this->startDate = $startDate;
                $this->endDate = $endDate;
                $this->type = $type;
                $this->categoryId = $categoryId;
            }

            public function collection()
            {
                if ($this->type == 'in') {
                    $q = StockIn::with('book')->whereBetween('date', [$this->startDate, $this->endDate]);
                } else {
                    $q = StockOut::with('book')->whereBetween('date', [$this->startDate, $this->endDate]);
                }

                if ($this->categoryId) {
                    $q = $q->whereHas('book', function ($q2) {
                        $q2->where('category_id', $this->categoryId);
                    });
                }

                return $q->get();
            }

            public function headings(): array
            {
                return [
                    'Tanggal',
                    'Kode Buku',
                    'Judul Buku',
                    'Jumlah',
                    'Keterangan'
                ];
            }

            public function map($row): array
            {
                return [
                    $row->date,
                    $row->book->code ?? '-',
                    $row->book->title ?? '-',
                    $row->qty,
                    $row->description ?? '-'
                ];
            }
        }, "laporan_{$type}_{$startDate}_{$endDate}.xlsx");
    }
}
