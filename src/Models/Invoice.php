<?php

namespace banelsems\LaraSgmefQR\src\Models;

use App\Models\Sale;
use App\Enum\StatusInvoiceEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sale_id',
        'invoiceRequestDataDto',
        'invoiceResponseDataDto',
        'statusInvoice',
        'securityElementsDto',
    ];

    /**
     * Get the sale associated with the invoice.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    protected $casts = [
        'invoiceRequestDataDto' => 'array',
        'invoiceResponseDataDto' => 'array',
        'securityElementsDto' => 'array',
        'statusInvoice' => StatusInvoiceEnum::class, // Use explicit type casting
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
}