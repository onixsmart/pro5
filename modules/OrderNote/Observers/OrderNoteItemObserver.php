<?php

namespace Modules\OrderNote\Observers;

use App\Models\Tenant\Item;
use Modules\Inventory\Models\InventoryConfiguration;
use Modules\Inventory\Traits\InventoryTrait;
use Modules\Item\Models\ItemLot;
use Modules\Item\Models\ItemLotsGroup;
use Modules\Order\Models\OrderNoteItem;

class OrderNoteItemObserver
{
    use InventoryTrait;

    public function creating(OrderNoteItem $order_note_item)
    {

        $inventory_configuration = InventoryConfiguration::query()->first();
        if($inventory_configuration->stock_change_by_order_notes) {
            $item = $order_note_item->item;
            $document = $order_note_item->order_note;
            $warehouse_id = $order_note_item->warehouse_id;

            $presentationQuantity = $item->presentation->quantity_unit ?? 1;
            // $warehouse = $this->findWarehouse($order_note_item->order_note->establishment_id);
            // $warehouse = ($warehouse_id) ? $this->findWarehouse($this->findWarehouseById($warehouse_id)->establishment_id) : $this->findWarehouse($order_note_item->order_note->establishment_id);
            $item_id =$order_note_item->item_id;
            // $factor = 1;
            // Factor proviende de Document.
            $factor = ($document->document_type_id  && $document->document_type_id === '07') ? 1 : -1;

            if (!$item->is_set) {
                $presentationQuantity = $item->presentation->quantity_unit ?? 1;
                $quanty = ($factor * ($order_note_item->quantity * $presentationQuantity));

                $warehouse = ($warehouse_id) ?
                    $this->findWarehouse($this->findWarehouseById($warehouse_id)->establishment_id) :
                    $this->findWarehouse();
                //$this->createInventory($item_id, $factor * $order_note_item->quantity, $warehouse->id);
                $this->createInventoryKardex($document, $item_id, $quanty, $warehouse->id);
                if (!$document->sale_note_id && !$document->order_note_id && !$document->dispatch_id) {
                    $this->updateStock($item_id, ($quanty), $warehouse->id);
                } else {
                    if ($document->dispatch) {
                        if (!$document->dispatch->transfer_reason_type->discount_stock) {
                            $this->updateStock($item_id, ($quanty), $warehouse->id);
                        }
                    }
                }

            } else {

                $item = Item::query()->findOrFail($item_id);
                foreach ($item->sets as $it) {
                    /** @var Item $ind_item */

                    $ind_item = $it->individual_item;
                    $item_id = $ind_item->id;
                    $item_set_quantity = ($it->quantity) ?: 1;
                    $presentationQuantity = 1;

                    $warehouse = $this->findWarehouse();
                    $quanty = $factor * ($order_note_item->quantity * $presentationQuantity * $item_set_quantity);

                    $this->createInventoryKardex($document, $item_id, ($quanty), $warehouse->id);

                    if (!$document->sale_note_id && !$document->order_note_id && !$document->dispatch_id) {
                        $this->updateStock($item_id, ($quanty), $warehouse->id);
                    } else {
                        if ($document->dispatch) {
                            if (!$document->dispatch->transfer_reason_type->discount_stock) {
                                $this->updateStock($item_id, ($quanty), $warehouse->id);
                            }
                        }
                    }

                }
            }


            // $this->createInventoryKardex($order_note_item->order_note, $order_note_item->item_id, (-1 * ($order_note_item->quantity * $presentationQuantity)), $warehouse->id);
            // $this->updateStock($order_note_item->item_id, (-1 * ($order_note_item->quantity * $presentationQuantity)), $warehouse->id);

            // control de lotes
            if (isset($order_note_item->item->IdLoteSelected))
            {
                $IdLoteSelected = $order_note_item->item->IdLoteSelected;

                if(is_array($IdLoteSelected))
                {
                    foreach ($IdLoteSelected as $lot_selected)
                    {
                        $lot = ItemLotsGroup::find($lot_selected->id);
                        $lot->quantity = $lot->quantity - ($lot_selected->compromise_quantity * $presentationQuantity ?? 1);
                        $lot->save();
                    }
                }
            }
            else
            {

                if (isset($order_note_item->item->lots_group)) {
                    if(is_array($order_note_item->item->lots_group) && count($order_note_item->item->lots_group) > 0) {
                        $lots_group = $order_note_item->item->lots_group;

                        foreach ($lots_group as $item) {
                            $lot = ItemLotsGroup::query()->find($item->id);
                            $lot->quantity = $lot->quantity - $item->compromise_quantity;
                            $lot->save();
                        }
                    }
                }

            }
            // control de lotes

            if(isset($item->lots) )
            {
                foreach ($item->lots as $it) {

                    if($it->has_sale == true)
                    {
                        $r = ItemLot::query()->find($it->id);
                        $r->item_loteable_type = get_class($document);
                        $r->item_loteable_id = $document->id;
                        $r->has_sale = true;
                        $r->save();
                    }

                }
            }
        }
    }
}
