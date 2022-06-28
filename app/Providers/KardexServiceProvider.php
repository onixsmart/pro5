<?php

namespace App\Providers;

use App\Models\Tenant\Item;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\Document;
use App\Models\Tenant\PurchaseItem;
use App\Models\Tenant\SaleNoteItem;
use App\Models\Tenant\Kardex;
use Illuminate\Support\ServiceProvider;
use App\Traits\KardexTrait;
use Modules\Order\Models\OrderNoteItem;


/**
 * Se debe tener en cuenta este provider para llevar el control de Kardex
 */
class KardexServiceProvider extends ServiceProvider
{
    use KardexTrait;

    public function boot()
    {
        $this->save_item();
        $this->sale();
        $this->purchase();
        $this->sale_note();
        $this->order_note();
    }

    public function register()
    {

    }

    /**
     * Cuando se realiza una venta
     */
    private function sale()
    {
        DocumentItem::created(function (DocumentItem $document_item) {
            $document = Document::whereIn('document_type_id', ['01', '03'])->find($document_item->document_id);
            if ($document) {

                $kardex = $this->saveKardex('sale', $document_item->item_id, $document_item->document_id, $document_item->quantity, 'document');

                if ($document->state_type_id != 11) {

                    $this->updateStock($document_item->item_id, $kardex->quantity, true);

                }

            }
        });
    }

    /**
     *Cuando se realiza una compra
     */
    private function purchase()
    {
        PurchaseItem::created(function (PurchaseItem $purchase_item) {

            $kardex = $this->saveKardex('purchase', $purchase_item->item_id, $purchase_item->purchase_id, $purchase_item->quantity, 'purchase');

            $this->updateStock($purchase_item->item_id, $kardex->quantity, false);

        });
    }

    /**
     * Cuando se realiza una nota de venta
     */
    private function sale_note()
    {
        SaleNoteItem::created(function (SaleNoteItem $sale_note_item) {

            $kardex = $this->saveKardex('sale', $sale_note_item->item_id, $sale_note_item->sale_note_id, $sale_note_item->quantity, 'sale_note');

            $this->updateStock($sale_note_item->item_id, $kardex->quantity, true);

        });
    }

    /**
     * Cuando se realiza un pedido
     */
    private function order_note()
    {
        OrderNoteItem::created(function (OrderNoteItem $order_note_item) {
            $kardex = $this->saveKardex('sale', $order_note_item->item_id,
                $order_note_item->order_note_id, $order_note_item->quantity, 'order_note');
            $this->updateStock($order_note_item->item_id, $kardex->quantity, true);
        });
    }

    /**
     * Cuando se guarda un item
     */
    private function save_item()
    {

        Item::created(function (Item $item) {

            $stock = ($item->stock) ? $item->stock : 0;
            $kardex = $this->saveKardex(null, $item->id, null, $stock, null);

        });

    }


}
