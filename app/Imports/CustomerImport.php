<?php

namespace App\Imports;

use App\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithHeadingRow;



class CustomerImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Customer([
            'full_name' => $row['full_name'],
            'company_name' => $row['company_name'],
            'email' => $row['email'],
            'address' => $row['address'],
            'phone' => $row['phone'],
            'city' => $row['city'],
            'company_id' => Auth::user()->company->id,
        ]);
    }
}
