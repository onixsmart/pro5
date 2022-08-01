<?php

use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\SaleNoteItem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Item\Models\ItemLot;

class TenantChangeColumnsLotsToItemLots extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_lots', function (Blueprint $table) {
            $table->unsignedInteger('item_loteable_id')->nullable()->change();
            $table->string('item_loteable_type')->nullable()->change();
        });

        ItemLot::query()->update([
            'item_loteable_type' => null,
            'item_loteable_id' => null,
        ]);

        SaleNoteItem::query()
            ->chunk(100, function ($document_items) {
                foreach ($document_items as $document_item) {
                    $document = $document_item->sale_note;
                    $lots = $document_item->item->lots;
                    foreach ($lots as $lot) {
                        ItemLot::query()->where('id', $lot->id)->update([
                            'item_loteable_type' => get_class($document),
                            'item_loteable_id' => $document->id,
                        ]);
                    }
                }
            });

        DocumentItem::query()
            ->chunk(100, function ($document_items) {
                foreach ($document_items as $document_item) {
                    $document = $document_item->document;
                    $lots = $document_item->item->lots;
                    foreach ($lots as $lot) {
                        ItemLot::query()->where('id', $lot->id)->update([
                            'item_loteable_type' => get_class($document),
                            'item_loteable_id' => $document->id,
                        ]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_lots', function (Blueprint $table)
        {

        });
    }
}
