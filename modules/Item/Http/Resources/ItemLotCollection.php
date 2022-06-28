<?php

namespace Modules\Item\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ItemLotCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request) {
        return $this->collection->transform(function($row, $key) {
            $status = '';
            if($row->has_sale){
                $status = 'SI';
            }else{
                $status = 'NO';
            }

            $number = '';
            $document = $row->item_loteable;
            if($document) {
                $number = $document->series.'-'.$document->number;
            }
            return [
                'id' => $row->id,
                'series' => $row->series,
                'item_description' => $row->item->description,
                'date' => $row->date,
                'state' => $row->state,
                'item_id' => $row->item_id,
                'warehouse_id' => $row->warehouse_id,
                'status' => $status,
                'has_sale' => (bool)$row->has_sale,
                'document_number' => $number
                // 'lot_code' => ($row->item_loteable_type) ? (isset($row->item_loteable->lot_code) ? $row->item_loteable->lot_code:null):null
            ];
        });
    }
}
