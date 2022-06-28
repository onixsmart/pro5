<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InventoryConfigurationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'stock_control' => $this->stock_control,
            'generate_internal_id' => $this->generate_internal_id,
            'stock_change_by_order_notes' => $this->stock_change_by_order_notes,
        ];
    }
}
