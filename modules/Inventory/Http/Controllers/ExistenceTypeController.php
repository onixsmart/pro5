<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Modules\Inventory\Http\Requests\ExistenceTypeRequest;
use Modules\Inventory\Http\Resources\ExistenceTypeCollection;
use Modules\Inventory\Http\Resources\ExistenceTypeResource;
use Modules\Inventory\Models\ExistenceType;

class ExistenceTypeController extends Controller
{
    public function index()
    {
        return view('inventory::existence_types.index');
    }

    public function records()
    {
        $records = ExistenceType::all();

        return new ExistenceTypeCollection($records);
    }

    public function record($id)
    {
        $record = new ExistenceTypeResource(ExistenceType::query()->findOrFail($id));

        return $record;
    }

    public function store(ExistenceTypeRequest $request)
    {
        $id = $request->input('id');
        $unit_type = ExistenceType::query()->firstOrNew(['id' => $id]);
        $unit_type->fill($request->all());
        $unit_type->save();

        return [
            'success' => true,
            'message' => ($id)?'Tipo de existencia editada con éxito':'Tipo de existencia registrada con éxito'
        ];
    }

    public function destroy($id)
    {
        try {
            $record = ExistenceType::query()->findOrFail($id);
            $record->delete();

            return [
                'success' => true,
                'message' => 'Unidad eliminada con éxito'
            ];

        } catch (Exception $e) {
            return ($e->getCode() == '23000') ? [
                'success' => false,
                'message' => 'Tipo de existencia esta siendo usada por otros registros, no puede eliminar'
            ] : [
                'success' => false,
                'message' => 'Error inesperado, no se pudo eliminar la unidad'
            ];
        }
    }
}
