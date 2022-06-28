<?php

namespace Modules\Inventory\Models;

use App\Models\Tenant\ModelTenant;

class InventoryConfiguration extends ModelTenant
{
    protected $fillable = [
        'stock_control',
        'generate_internal_id',
        'stock_change_by_order_notes'
    ];

    protected $casts = [
        'stock_control' => 'boolean',
        'generate_internal_id' => 'boolean',
        'stock_change_by_order_notes' => 'boolean',
    ];
}
