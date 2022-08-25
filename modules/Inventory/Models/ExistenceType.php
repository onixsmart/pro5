<?php

namespace Modules\Inventory\Models;

use App\Models\Tenant\Dispatch;
use App\Models\Tenant\Document;
use App\Models\Tenant\Item;
use App\Models\Tenant\ItemWarehousePrice;
use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\SaleNote;
use Modules\Order\Models\OrderNote;

class ExistenceType extends ModelTenant
{
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
