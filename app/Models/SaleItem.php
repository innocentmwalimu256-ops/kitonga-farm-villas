<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $table = 'sale_items';

    protected $fillable = [
        'sale_id',
        'product_id',
        'service_name',
        'description_snapshot',
        'quantity',
        'unit_price',
        'cost_snapshot',
        'total'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'cost_snapshot' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
