<?php

namespace Modules\Inventory\Models;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Builder;


class InventoryConfiguration extends ModelTenant
{

    protected $fillable = [
        'stock_control',
        'generate_internal_id',
        'cost_control'
    ];

    protected $casts = [
        'stock_control' => 'boolean',
        'generate_internal_id' => 'boolean',
        'cost_control' => 'boolean',
    ];


    /**
     *
     * Obtener campo individual de la configuracion
     *
     * @param Builder $query
     * @param string $column
     * @return Builder
     */
    public function scopeGetRecordIndividualColumn($query, $column)
    {
        return $query->select($column)->firstOrFail()->{$column};
    }

}
