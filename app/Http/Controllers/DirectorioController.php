<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DirectorioController extends Controller
{
    public function index(Request $request)
    {
        $contenido = '';
        $existe = Storage::exists('Directorio.txt');
        if(!$existe){
            Storage::disk('local')->put('Directorio.txt', $contenido);
        }

        $collection = '';

        $personas = file_get_contents(storage_path('app').'\\Directorio.txt');
        if(filled($personas))
        {
            $personas = substr($personas, 0, strrpos($personas, ';')) ;
            $personas = explode(';', $personas);

            $items = [];

            foreach($personas as $persona)
            {
                $datos = explode('|', $persona);
                foreach($datos as $key => $value)
                {
                    if(array_key_exists($key, $datos))
                    {
                        $keys = array_keys($datos);
                        $keys[array_search(0, $keys)] = 'nombre';
                        $keys[array_search(1, $keys)] = 'telefono';
                        $keys[array_search(2, $keys)] = 'cargo';
                        $keys[array_search(3, $keys)] = 'fecha';
                        $keys[array_search(4, $keys)] = 'genero';
                        $keys[array_search(5, $keys)] = 'EdoSolicitud';
                    }
                }
                $items = array_merge($items, [array_combine($keys, $datos)]);
            }

            $collection = $this->setQuery($request, $items);
        }


        return response()->json(
            $collection
        );
    }

    public function store(Request $request)
    {

        $rules  = [
            'nombre' => 'required|string|max:150',
            'telefono' => 'required|numeric|digits:10',
            'cargo' => 'required|string',
            'fecha' => 'required|date|max:10',
            'genero' => 'required|string|in:masculino,femenino',
            'EdoSolicitud' => 'required|string',
        ];

        $validator = Validator::make($request->json()->all(), $rules);
        if($validator->fails())
        {
            return response()->json([
                $validator->errors()->all()
            ]);
        }

        $contenido = '';
        $existe = Storage::exists('Directorio.txt');
        if(!$existe){
            Storage::disk('local')->put('Directorio.txt', $contenido);
        }

        $contenido = $request->nombre.'|'.$request->telefono.'|'.$request->cargo.'|'.$request->fecha.'|'.$request->genero.'|'.$request->EdoSolicitud.";";
        file_put_contents(storage_path('app').'\\Directorio.txt', $contenido, FILE_APPEND);

        return response()->json([
            'message' => 'El registro se agregó correctamente.'
        ], 201);
    }

    private function setQuery($request, $items)
    {
        $query = collect((object) $items);
        if(filled($request->telefono))
        {
            $query = $query->where('telefono', $request->telefono);
        }

        if(filled($request->fecha_inicio) && filled($request->fecha_fin))
        {
            $query = $query->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin]);
        }

        return $query->all();
    }
}
