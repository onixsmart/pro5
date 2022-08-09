<?php

    namespace Modules\Inventory\Jobs;

    use Illuminate\Bus\Queueable;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Hyn\Tenancy\Models\Website;
    use Illuminate\Support\Facades\Log;
    use Exception;
    use App\Models\Tenant\DownloadTray;
    use Hyn\Tenancy\Environment;
    use App\CoreFacturalo\Helpers\Storage\StorageDocument;
    use App\Models\Tenant\Company;
    use App\Models\Tenant\Establishment;
    use Modules\Inventory\Exports\ValuedKardexSunatExport;
    use Barryvdh\DomPDF\Facade as PDF;
    use Modules\Inventory\Models\ItemWarehouse;
    use Mpdf\HTMLParserMode;
    use Mpdf\Mpdf;
    use Mpdf\Config\ConfigVariables;
    use Mpdf\Config\FontVariables;
    use Modules\Inventory\Helpers\SunatValuedKardex;
    use Modules\Report\Traits\ReportTrait;

    class ProccessKardexSunat implements ShouldQueue
    {
        use Dispatchable;
        use InteractsWithQueue;
        use Queueable;
        use SerializesModels;
        use StorageDocument;
        use ReportTrait;

        public $website_id;
        public $tray_id;
        public $warehouse_id;
        public $filter;
        public $params;


        /**
         * Create a new job instance.
         *
         * @return void
         */
        public function __construct(int $website_id, int $tray_id, int $warehouse_id, string $filter, array $params)
        {
            $this->website_id = $website_id;
            $this->tray_id = $tray_id;
            $this->warehouse_id = $warehouse_id;
            $this->filter = $filter;
            $this->params = $params;
        }

        /**
         * Execute the job.
         *
         * @return void
         */
        public function handle()
        {
            Log::debug("ProcessKardexReport Start WebsiteId => " . $this->website_id);

            $website = Website::find($this->website_id);
            $tenancy = app(Environment::class);
            $tenancy->tenant($website);

            $tray = DownloadTray::find($this->tray_id);
            $path = null;

            $website_id = $this->website_id;
            $tray_id = $this->tray_id;
            $warehouse_id = $this->warehouse_id;
            $filter = $this->filter;
            if (empty($tray)) {

                \Log::debug("No hay datos
                    $ website_id        =>" . var_export($website_id, true) . "
                    $ tray_id       =>" . var_export($tray_id, true) . "
                    $ warehouse_id      =>" . var_export($warehouse_id, true) . "
                    $ filter        =>" . var_export($filter, true) . "
                    ");

            } else {
                try {
                    $company = Company::query()->first();
                    $establishment = null;

                    if ($this->warehouse_id != 0) {
                        $establishment = Establishment::find($this->warehouse_id);
                    } else {
                        $establishment = Establishment::query()->first();
                    }
                    //ini_set('max_execution_time', 0);

                    //$records = $this->getRecordsTranform($this->warehouse_id, $this->filter);

                    $data_of_period = $this->getDataOfPeriod($this->params);


                    $params_sunat = (object)[
                        'establishment_id' => $this->params['establishment_id'],
                        'date_start' => $data_of_period['d_start'],
                        'date_end' => $data_of_period['d_end'],
                        'user_id' => $this->params['user_id'],
                    ];
                    
                    $data = SunatValuedKardex::getDataFormatSunat($params_sunat);
                    $additionalData = SunatValuedKardex::getDataAdditional($this->params, $params_sunat, $data['items']);
                    $records = $data['records'];
                    if (!is_object($tray)) {
                        //Log::debug('DE ' . var_export($tray, true));
                    }
                    $format = $tray->format;


                        Log::debug($records);
                        $filename = 'KARDEX_ReporteInv_' . date('YmdHis') . '-' . $tray->user_id;
                        Log::debug("Render excel init");
                        $kardexExport = new ValuedKardexSunatExport();
                        $kardexExport
                            ->additionalData($additionalData)
                            ->records($records)
                            ->company($company)
                            ->establishment($establishment);
                        Log::debug("Render excel finish");

                        Log::debug("Upload excel init");

                        $kardexExport->store(DIRECTORY_SEPARATOR . "download_tray_xlsx" . DIRECTORY_SEPARATOR . $filename . '.xlsx', 'tenant');

                        Log::debug("Upload excel finish");
                        $tray->file_name = $filename;
                        $path = 'download_tray_xlsx';

                    $tray->date_end = date('Y-m-d H:i:s');
                    $tray->status = 'FINISHED';
                    $tray->path = $path;
                    $tray->save();

                } catch (Exception $e) {
                    Log::debug("ProcessKardexReport Error transaction" . $e);
                }
            }

            Log::debug("ProcessKardexReport Finish transaction");
        }

        public function getRecordsTranform($warehouse_id, $filter)
        {
            Log::debug("warehouse_id" . $warehouse_id);

            Log::debug("getRecordsTranform init" . date('H:i:s'));
            $records = $this->getRecords($warehouse_id, $filter);

            $data = [];

            $records->chunk(1000, function ($items) use (&$data) {
                foreach ($items as $row) {
                    $item = $row->item;
                    $data[] = [
                        'barcode' => $item->barcode,
                        'internal_id' => $item->internal_id,
                        'name' => $item->description,
                        'item_category_name' => optional($item->category)->name,
                        'stock_min' => $item->stock_min,
                        'stock' => $row->stock,
                        'sale_unit_price' => $item->sale_unit_price,
                        'purchase_unit_price' => $item->purchase_unit_price,
                        'profit' => number_format($item->sale_unit_price - $item->purchase_unit_price, 2, '.', ''),
                        'model' => $item->model,
                        'brand_name' => $item->brand->name,
                        'date_of_due' => optional($item->date_of_due)->format('d/m/Y'),
                        'warehouse_name' => $row->warehouse->description
                    ];

                }
            });

            Log::debug("getRecordsTranform finish" . date('H:i:s'));

            return $data;
        }

        private function getRecords($warehouse_id = 0, $filter)
        {

            $query = ItemWarehouse::with(['warehouse', 'item' => function ($query) {
                $query->select('id', 'barcode', 'internal_id', 'description', 'category_id', 'brand_id', 'stock_min', 'sale_unit_price', 'purchase_unit_price', 'model', 'date_of_due');
                $query->with(['category', 'brand']);
                $query->without(['item_type', 'unit_type', 'currency_type', 'warehouses', 'item_unit_types', 'tags']);
            }])
                ->whereHas('item', function ($q) {
                    $q->where([
                        ['item_type_id', '01'],
                        ['unit_type_id', '!=', 'ZZ'],
                    ])
                        ->whereNotIsSet();
                });

            if ($filter === '02') {
                //$add = ($stock < 0);
                $query->where('stock', '<=', 0);

            }

            if ($filter === '03') {
                //$add = ($stock == 0);
                $query->where('stock', 0);
            }

            if ($filter === '04') {
                //$add = ($stock > 0 && $stock <= $item->stock_min);
                //$query->where('stock', 0);

                $query = ItemWarehouse::with(['warehouse', 'item' => function ($query) {
                    $query->select('id', 'barcode', 'internal_id', 'description', 'category_id', 'brand_id', 'stock_min', 'sale_unit_price', 'purchase_unit_price', 'model', 'date_of_due');
                    $query->with(['category', 'brand']);
                    $query->without(['item_type', 'unit_type', 'currency_type', 'warehouses', 'item_unit_types', 'tags']);
                }])
                    ->whereHas('item', function ($q) {
                        $q->where([
                            ['item_type_id', '01'],
                            ['unit_type_id', '!=', 'ZZ'],
                        ])
                            ->whereNotIsSet()
                            ->whereStockMin();
                    })->where('stock', '>', 0);

            }


            if ($filter === '05') {
                //$add = ($stock > $item->stock_min);

                $query = ItemWarehouse::with(['warehouse', 'item' => function ($query) {
                    $query->select('id', 'barcode', 'internal_id', 'description', 'category_id', 'brand_id', 'stock_min', 'sale_unit_price', 'purchase_unit_price', 'model', 'date_of_due');
                    $query->with(['category', 'brand']);
                    $query->without(['item_type', 'unit_type', 'currency_type', 'warehouses', 'item_unit_types', 'tags']);
                }])
                    ->whereHas('item', function ($q) {
                        $q->where([
                            ['item_type_id', '01'],
                            ['unit_type_id', '!=', 'ZZ'],
                        ])
                            ->whereNotIsSet()
                            ->whereStockMinValidate();
                    });
            }


            if ($warehouse_id != 0) {
                $query->where('item_warehouse.warehouse_id', $warehouse_id);
            }

            if ($this->params['category_id'] ?? null) $query->whereItemCategory($this->params['category_id']);

            if ($this->params['brand_id'] ?? null) $query->whereItemBrand($this->params['brand_id']);

            return $query;

        }

        /**
         * The job failed to process.
         *
         * @param Exception $exception
         *
         * @return void
         */
        public function failed(Exception $exception)
        {
            Log::error($exception->getMessage());
        }
    }
