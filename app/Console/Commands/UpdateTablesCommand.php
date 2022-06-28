<?php

namespace App\Console\Commands;

use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\SaleNoteItem;
use Illuminate\Console\Command;
use Modules\Item\Models\ItemLot;
use Modules\Order\Models\OrderNote;
use Modules\Order\Models\OrderNoteItem;

class UpdateTablesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualización de registro de tablas';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Hora de inicio: ' . date('Y-m-d H:i:s'));
        $this->info('Inicializando Proceso');

        Quotation::query()
            ->chunk(100, function ($documents) {
                foreach ($documents as $document) {
                    $filename = explode('-', $document->filename);
                    if (count($filename) > 1 && $filename[1] !== '') {
                        $document->update([
                            'series' => 'COT1',
                            'number' => $filename[1]
                        ]);
                    }
                }
            });

        OrderNote::query()
            ->chunk(100, function ($documents) {
                foreach ($documents as $document) {
                    $filename = explode('-', $document->filename);
                    if (count($filename) > 1 && $filename[1] !== '') {
                        $document->update([
                            'series' => 'PD01',
                            'number' => $filename[1]
                        ]);
                    }
                }
            });

        ItemLot::query()->update([
            'item_loteable_type' => null,
            'item_loteable_id' => null,
        ]);

        OrderNoteItem::query()
            ->chunk(100, function ($document_items) {
                foreach ($document_items as $document_item) {
                    $document = $document_item->order_note;
                    if(property_exists($document_item->item, 'lots')) {
                        $lots = $document_item->item->lots;
                        foreach ($lots as $lot) {
                            ItemLot::query()->where('id', $lot->id)->update([
                                'item_loteable_type' => get_class($document),
                                'item_loteable_id' => $document->id,
                            ]);
                        }
                    }
                }
            });

        SaleNoteItem::query()
            ->chunk(100, function ($document_items) {
                foreach ($document_items as $document_item) {
                    $document = $document_item->sale_note;
                    if(property_exists($document_item->item, 'lots')) {
                        $lots = $document_item->item->lots;
                        foreach ($lots as $lot) {
                            ItemLot::query()->where('id', $lot->id)->update([
                                'item_loteable_type' => get_class($document),
                                'item_loteable_id' => $document->id,
                            ]);
                        }
                    }

                }
            });

        DocumentItem::query()
            ->chunk(100, function ($document_items) {
                foreach ($document_items as $document_item) {
                    $document = $document_item->document;
                    if(property_exists($document_item->item, 'lots')) {
                        $lots = $document_item->item->lots;
                        foreach ($lots as $lot) {
                            ItemLot::query()->where('id', $lot->id)->update([
                                'item_loteable_type' => get_class($document),
                                'item_loteable_id' => $document->id,
                            ]);
                        }
                    }
                }
            });

        $this->info('Finalizando Proceso');
        $this->info('Hora de término: ' . date('Y-m-d H:i:s'));
    }
}
