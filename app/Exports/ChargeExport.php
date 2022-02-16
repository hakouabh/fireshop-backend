<?php

namespace App\Exports;

use App\Charge;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ChargeExport implements FromCollection, ShouldAutoSize, WithMapping, WithHeadings
{
    use Exportable;

    protected $request;

    function __construct($request) {
            $this->request = $request;
    }


    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $charge = Charge::with('type')
                            ->where('company_id', Auth::user()->company->id);
        
        if($this->request->has('filter')){
            $params = (object) json_decode($this->request->filter, true);
            
            if(is_array($params->type)){
                $params->type = (object) $params->type;
            }
            if(is_array($params->date_range)){
                $params->date_range = (object) $params->date_range;
            }
            if(is_object($params->type) && $params->type->id){
                $charge = $charge->where('type_id', $params->type->id);
            }
            if(is_object($params->date_range) && ($params->date_range->from && $params->date_range->to)){
                $charge = $charge->whereBetween('created_at', [
                    Carbon::createFromFormat('Y-m-d', $params->date_range->from)->format('Y-m-d')." 00:00:00",
                    Carbon::createFromFormat('Y-m-d', $params->date_range->to)->format('Y-m-d')." 23:59:59"
                ]);
            }
        }
        return $charge->orderby('created_at','desc')->get() ;
    }

    public function map($charge):array{
        return [
            $charge->amount,
            $charge->type->name,
            $charge->description,
            Carbon::parse($charge->created_at)->toFormattedDateString()
        ];

    }

    public function headings():array {

        return[
            'Montant',
            'Catégorie',
            'Description',
            'Date et Heure'
        ];

    }
}

