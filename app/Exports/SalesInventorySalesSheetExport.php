<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalesInventorySalesSheetExport implements FromView, WithTitle
{
    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function __construct(
        private array $rows,
        private string $title,
        private array $meta
    ) {
    }

    public function view(): View
    {
        return view('excel.sales-inventory-sales', [
            'rows' => $this->rows,
            'meta' => $this->meta,
        ]);
    }

    public function title(): string
    {
        return $this->title;
    }
}
