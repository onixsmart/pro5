<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Tenant\Document;
use App\Models\Tenant\User;

use App\Models\Tenant\Configuration;
use Exception;
use Modules\Document\Helpers\DocumentHelper;
use Illuminate\Support\Facades\Log;

use App\Models\Tenant\Establishment;

use App\Models\Tenant\SaleNote;

class LockedEmissionProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->locked_emission();
        $this->locked_users();
        $this->locked_establishments();
        $this->update_quantity_documents();
        $this->update_quantity_sales_notes();
        $this->update_sales_documents();
        $this->locked_sales();
    }


    private function update_quantity_documents()
    {
        Document::created(function ($document) {
            
            $configuration = Configuration::first();
            $configuration->quantity_documents++; 
            $configuration->save();
        
        }); 
    }

    private function update_sales_documents()
    {
        Document::created(function ($document) {
            
            $document=Document::find($document->id);


            $configuration = Configuration::first();
            $configuration->quantity_sales+=$document->total; 
            $configuration->save();
        }); 
    }
    

    private function locked_emission()
    {

        Document::created(function ($document) {

            $configuration = Configuration::firstOrFail();
            
            if($configuration->locked_emission)
            {
                $exceed_limit = DocumentHelper::exceedLimitDocuments($configuration);
                if($exceed_limit['success']) throw new Exception($exceed_limit['message']);
            }

            // $configuration = Configuration::first();
            // // $quantity_documents = Document::count();
            // $quantity_documents = $configuration->quantity_documents;

            // if($configuration->locked_emission && $configuration->limit_documents !== 0){
            //     if($quantity_documents >= $configuration->limit_documents)
            //         throw new Exception("Ha superado el límite permitido para la emisión de comprobantes");

            // }

        });

    }


    private function locked_users()
    {

        User::creating(function ($document) {
            
            
            $configuration = Configuration::first();

            $quantity_users = User::count();

            if($configuration->locked_users &&  $configuration->plan->limit_users !== 0){

                if($quantity_users >= $configuration->plan->limit_users )
                {
                    throw new Exception("Ha superado el límite permitido para la creación de usuarios");
                }
            }

        });
    }

    private function locked_establishments()
    {

        Establishment::creating(function ($document) {
            
            
            $configuration = Configuration::first();

            $quantity_establishments = Establishment::count();

            if($configuration->locked_establishments &&  $configuration->plan->limit_establishments !== 0){

                if($quantity_establishments >= $configuration->plan->limit_establishments )
                {
                    throw new Exception("Ha superado el límite permitido para la creación de establecimientos");
                }
            }

        });
    }

    private function locked_sales()
    {

        Document::created(function ($document) {

            $configuration = Configuration::firstOrFail();
            
            if($configuration->locked_sales)
            {
                $exceed_sales = DocumentHelper::LimitSalesDocuments($configuration);
                if($exceed_sales['success']) throw new Exception($exceed_sales['message']);
            }

        });
    }

    private function update_quantity_sales_notes()
    {
        SaleNote::created(function ($document) {
            
            $configuration = Configuration::first();
            $configuration->quantity_sales_notes++; 
            $configuration->save();
        
        }); 
    }
}
