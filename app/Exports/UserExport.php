<?php

namespace App\Exports;

use App\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UserExport implements FromQuery,ShouldAutoSize,WithMapping,WithHeadings
{
    use Exportable;


    public function query()
    {
        return User::query();
    }

    public function map($user):array{

        return [
            $user->id,
            $user->email,
            $user->name
        ];

    }

    public function headings():array {

        return[

            'id',
            'Email',
            'Name'

        ];

    }

}
