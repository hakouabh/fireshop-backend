<?php

namespace App\Exports;

use App\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OperationExport implements FromCollection, ShouldAutoSize, WithMapping, WithHeadings
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
        $order = Operation::query()->with(['order.order_detail','customer'])
            ->where('company_id', Auth::user()->company->id);

        if($this->request->has('filter')){
            $params = (object) json_decode($this->request->filter, true);

            if(is_array($params->date_range)){
                $params->date_range = (object) $params->date_range;
            }
            
            if($params->customer){
                $order = $order->whereHas('customer', function($q) use ($params) {
                  $q->where('full_name', 'like', '%' . $params->customer . '%');
                });
            }
            if($params->phone){
                $order = $order->whereHas('customer', function($q) use ($params) {
                  $q->where('phone', 'like', '%' . $params->phone . '%');
                });
            }

            if(is_object($params->date_range) && ($params->date_range->from && $params->date_range->to)){
                $order = $order->whereBetween('created_at', [
                    Carbon::createFromFormat('Y-m-d', $params->date_range->from)->format('Y-m-d')." 00:00:00",
                    Carbon::createFromFormat('Y-m-d', $params->date_range->to)->format('Y-m-d')." 23:59:59"
                ]);
            }
        }
        return $order->orderby('created_at','desc')->get() ;
    }

    public function map($operation):array{
        $customer_name = 'Client Divers';
        if($operation->customer != null)
        $customer_name = $operation->customer->full_name;
        return [
            $customer_name,
            $operation->total,
            $operation->payment,
            $operation->discount,
            Carbon::parse($operation->created_at)->toFormattedDateString()
        ];

    }

    public function headings():array {

        return[
            'Client',
            'Total',
            'Payment',
            'Remise',
            'Date et Heure'
        ];

    }
}
