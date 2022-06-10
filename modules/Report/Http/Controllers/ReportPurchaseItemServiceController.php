<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Resources\Tenant\PurchaseItemCollection;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\PurchaseItem;
use Barryvdh\DomPDF\Facade as PDF;
use Modules\Report\Exports\GeneralItemExport;
use Modules\Report\Exports\PurchaseExport;
use Illuminate\Http\Request;
use Modules\Report\Traits\ReportTrait;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\Company;
use Modules\Expense\Models\Expense;
use Modules\Expense\Models\ExpenseItem;
use Carbon\Carbon;
use App\Http\Resources\Tenant\PurchaseCollection;
use Modules\Report\Http\Resources\GeneralItemCollection;

/**
 * Class ReportPurchaseController
 *
 * @package Modules\Report\Http\Controllers
 */
class ReportPurchaseItemServiceController extends Controller
{
    use ReportTrait;

    public function general_items(Request $request)
    {
        $typeresource = 'reports/purchases/general_items';
        $typereport = 'purchase';
        $configuration = Configuration::getPublicConfig();
        $apply_conversion_to_pen = $this->applyConversiontoPen($request);

        return view('report::general_items.index_service',compact('typeresource','typereport','configuration', 'apply_conversion_to_pen'));
    }

    /**
     * @return array
     */
    public function filter() {
        $customers = $this->getPersons('customers');
        $suppliers = $this->getPersons('suppliers');
        $items = $this->getItems('items');
        $brands = $this->getBrands();
        $web_platforms = $this->getWebPlatforms();
        $document_types = DocumentType::whereIn('id', ['02', '14'])->get();

        $categories = $this->getCategories();
        $users = $this->getUsers();

        return compact('document_types', 'suppliers', 'customers', 'items','web_platforms', 'brands', 'categories', 'users');
    }

    public function records(Request $request)
    {
        $purchaseType=true;
        $purchase = $this->getRecordsItems($request->all(),$purchaseType);
        $expense = $this->getRecordsItems($request->all(),false);

        $allPurchase= new GeneralItemCollection($purchase->paginate(config('tenant.items_per_page')));
        $allExpense= new GeneralItemCollection($expense->paginate(config('tenant.items_per_page')));
        $allRecords=$allPurchase->concat($allExpense);

        return $allRecords;
    }


    public function getRecordsItems($request, $purchaseType){
        $purchseType=$purchaseType;
        $data_of_period = $this->getDataOfPeriod($request);
        /* $data_type = $this->getDataType($request); */

        $document_type_id = $request['document_type_id'];
        $d_start = $data_of_period['d_start'];
        $d_end = $data_of_period['d_end'];

        $person_id = $request['person_id'];
        $type_person = $request['type_person'];
        $item_id = $request['item_id'];
        $brand_id = $request['brand_id'];
        $category_id = $request['category_id'];

        $user_id = $request['user_id'];
        $user_type = $request['user_type'] != null ? $request['user_type'] : 'VENDEDOR';
        $web_platform_id = $request['web_platform_id'];

        $records = $this->dataItems($d_start, $d_end, $document_type_id, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $user_id, $user_type,$purchaseType);

        return $records;

    }


    /**
     * @param $date_start
     * @param $date_end
     * @param $document_type_id
     * @param $data_type
     * @param $person_id
     * @param $type_person
     * @param $item_id
     * @param $web_platform_id
     * @param $brand_id
     * @param $category_id
     * @param $user_id
     * @param $user_type
     *
     * @return \App\Models\Tenant\SaleNoteItem|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    private function dataItems($date_start, $date_end, $document_type_id, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $user_id, $user_type,$purchaseType)
    {
        if(!$document_type_id){
            $document_type_id=['02','14'];
            $expense_type_id=['2','3'];
        }else{
            if ($document_type_id=='02') {
                $expense_type_id=$document_type_id;
            }else{
                $expense_type_id=$document_type_id;
            }
        }
        /* columna state_type_id */
        $documents_excluded = [
            '11' // Documentos anulados
        ];
        /* if( $document_type_id && $document_type_id == '80' ) {
            $relation = 'sale_note';
 */
            if ($purchaseType) {
                $data= PurchaseItem::whereHas('purchase', function($query) use($date_start, $date_end, $user_id, $documents_excluded, $document_type_id){
                    $query
                    ->whereBetween('date_of_issue', [$date_start, $date_end])
                    ->latest()
                    ->whereBetween('document_type_id', [$document_type_id])
                    ->whereTypeUser();
                    if(!empty($user_id)){
                        $query->where('user_id',$user_id);
                    }
                    $query->whereNotIn('state_type_id', $documents_excluded);
                });
            } else {
                $data= ExpenseItem::whereHas('expense', function($query) use($date_start, $date_end, $user_id, $documents_excluded, $expense_type_id){
                    $query
                    ->whereBetween('date_of_issue', [$date_start, $date_end])
                    ->latest()
                    ->whereBetween('expense_type_id', [$expense_type_id])
                    ->whereTypeUser();
                    if(!empty($user_id)){
                        $query->where('user_id',$user_id);
                    }
                    $query->whereNotIn('state_type_id', $documents_excluded);
                });
            }
            
            

            


        /* } else {

            $model = $data_type['model'];
            $relation = $data_type['relation'];

            $document_types = $document_type_id ? [$document_type_id] : ['01','03'];

            $data = $model::whereHas($relation, function ($query) use ($date_start, $date_end, $document_types, $model,$documents_excluded) {
                $query
                    ->whereBetween('date_of_issue', [$date_start, $date_end])
                    ->whereIn('document_type_id', $document_types)
                    ->latest()
                    ->whereTypeUser();
                if ($model == 'App\Models\Tenant\DocumentItem') {
                    $query->whereNotIn('state_type_id', $documents_excluded);
                }
            });
            if ($user_id && $user_type === 'CREADOR') {
                $data = $data->whereHas($relation.'.user', function($query) use($user_id){
                    $query->where('user_id', $user_id);
                });
            }
			if ($user_id && $user_type === 'VENDEDOR') {
				$data = $data->whereHas($relation . '.seller', function ($query) use ($user_id) {
					$query->where('seller_id', $user_id);
				});
			}
        } */


        if($person_id && $type_person){

            $column = ($type_person == 'customers') ? 'customer_id':'supplier_id';

            $data =  $data->whereHas($relation, function($query) use($column, $person_id){
                                $query->where($column, $person_id);
                            });

        }

        if($item_id){
            $data =  $data->where('item_id', $item_id);
        }

        if($web_platform_id || $brand_id || $category_id){
            $data = $data->whereHas('relation_item', function($q) use($web_platform_id, $brand_id, $category_id){
				if ($web_platform_id) {
					$q->where('web_platform_id', $web_platform_id);
                }
				if ($brand_id) {
					$q->where('brand_id', $brand_id);
				}
                if ($category_id) {
					$q->where('category_id', $category_id);
				}
            });
        }

        return $data;

    }


    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function index() {

        $typereport = 'purchase';
        $configuration = Configuration::getPublicConfig();
        return view('report::purchases_items.index',compact('typereport','configuration'));
    }


    public function pdf(Request $request) {
        ini_set('memory_limit', '4026M');
        ini_set("pcre.backtrack_limit", "5000000");
        $records = $this->getRecordsItems($request->all())->latest('id')->get();
        $type_name = ($request->type == 'sale') ? 'Ventas_':'Compras_';
        $type = $request->type;
        $document_type_id = $request['document_type_id'];
        $request_apply_conversion_to_pen = $request['apply_conversion_to_pen'];

        $pdf = PDF::loadView('report::general_items.report_pdf', compact("records", "type", "document_type_id", "request_apply_conversion_to_pen"))->setPaper('a4', 'landscape');

        $filename = 'Reporte_General_Productos_'.$type_name.Carbon::now();

        return $pdf->download($filename.'.pdf');
    }


    public function excel(Request $request) {
        ini_set('memory_limit', '4026M');
        ini_set("pcre.backtrack_limit", "5000000");
        $records = $this->getRecordsItems($request->all())->latest('id')->get();
        $type = ($request->type == 'sale') ? 'Ventas_':'Compras_';
        $document_type_id = $request['document_type_id'];
        $request_apply_conversion_to_pen = $request['apply_conversion_to_pen'];

        $generalItemExport= new GeneralItemExport();
        $generalItemExport
            ->records($records)
            ->type($request->type)
            ->document_type_id($document_type_id)
            ->request_apply_conversion_to_pen($request_apply_conversion_to_pen);
            
        return $generalItemExport->download('Reporte_General_Productos_'.$type.Carbon::now().'.xlsx');

    }
}
