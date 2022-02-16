<?php

namespace App\Exports;
use Carbon\Carbon;
use App\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;


class CustomerExport implements FromCollection, ShouldAutoSize, WithMapping, WithHeadings
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
        $customer = Customer::where('company_id', Auth::user()->company->id);
        
        if($this->request->has('filter')){
            $params = (object) json_decode($this->request->filter, true);

            if(is_array($params->date_range)){
                $params->date_range = (object) $params->date_range;
            }
            if($params->name){
                $customer = $customer->where('full_name', 'like', '%' . $params->name . '%');
            }
            if($params->company){
                $customer = $customer->where('company_name', 'like', '%' . $params->company . '%');
            }
            if($params->email){
                $customer = $customer->where('email', 'like', '%' . $params->email . '%');
            }
            if($params->phone){
                $customer = $customer->where('phone', 'like', '%' . $params->phone . '%');
            }

            if(is_object($params->date_range) && ($params->date_range->from && $params->date_range->to)){
                $customer = $customer->whereBetween('created_at', [
                    Carbon::createFromFormat('Y-m-d', $params->date_range->from)->format('Y-m-d')." 00:00:00",
                    Carbon::createFromFormat('Y-m-d', $params->date_range->to)->format('Y-m-d')." 23:59:59"
                ]);
            }
        }

        return $customer->orderby('created_at','desc')->get() ;

    }

    public function map($customer):array{
        return [
            $customer->full_name,
            $customer->email,
            $customer->phone,
            $customer->city,
            Carbon::parse($customer->created_at)->toFormattedDateString()
        ];

    }

    public function headings():array {

        return[
            'Nom et Prénom',
            'E-Mail',
            'NUMÉRO DE TÉLÉPHONE',
            'Ville',
            'Date et Heure'
        ];

    }
}
