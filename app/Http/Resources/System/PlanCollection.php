<?php

namespace App\Http\Resources\System;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PlanCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {
            return [
                'id' => $row->id,
                'name' => $row->name,
                'pricing' => $row->pricing,
                'limit_users' => $row->limit_users,
                'limit_documents' => $row->limit_documents,
                'limit_sales' => $row->limit_sales,
                'limit_establishments' => $row->limit_establishments,
                'locked_sales_notes' => (bool) $row->locked_sales_notes,
                // 'plan_documents' => $row->plan_documents, 
                'locked' => (bool) $row->locked, 
            ];
        });
    }
}