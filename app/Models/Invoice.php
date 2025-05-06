<?php

namespace App\Models;
use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{

        protected $fillable=[
            "invoice_number",
            "invoice_date",
            "due_date",
            "client_name",
            "client_address",
            "items",
            "total_amount",
            "status"
           ];
           protected $casts = [
              'invoice_date' => 'date',
              'due_date' => 'date',
              'items' => 'array' 
          ];
    
}
