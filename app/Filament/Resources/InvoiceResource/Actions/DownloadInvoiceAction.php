<?php

namespace App\Filament\Resources\InvoiceResource\Actions;

use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Barryvdh\DomPDF\Facade\Pdf;

class DownloadInvoiceAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'download_invoice';
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->label('Download PDF');
        $this->icon('heroicon-o-arrow-down-on-square');
        $this->color('success');
        
        $this->action(function (Model $record) {
            $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $record]);
            
            return response()->streamDownload(
                fn () => print($pdf->output()),
                "invoice-{$record->invoice_number}.pdf"
            );
        });
    }
}