<?php

namespace Modules\Item\Imports;

use App\Models\Tenant\Establishment;
use App\Models\Tenant\Item;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Traits\InventoryTrait;

class ItemStockInitialImport implements ToCollection
{
    use Importable;
    use InventoryTrait;

    protected $data;

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        unset($rows[0]);
        $establishment = Establishment::query()
            ->select('id')
            ->first();
        $warehouse = Warehouse::query()
            ->select('id')
            ->where('establishment_id', $establishment->id)->first();

        foreach ($rows as $row) {
            $internal_id = trim($row[0]);
            $stock = trim($row[1]);
            $item = Item::query()
                ->select('id')
                ->where('internal_id', $internal_id)->first();
            if ($item) {
                $this->createInitialInventory($item->id, $stock, $warehouse->id);
            }
        }
    }

    public function getData()
    {
        return $this->data;
    }
}
