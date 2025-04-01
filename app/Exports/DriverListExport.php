<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DriverListExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return User::select([
            'first_name',
            'last_name',
            'email',
            'phone_number',
            'license_plate',
            'created_at'
        ])->where('role_id', 0)->get();
    }

    public function headings(): array
    {
        return [
            'First Name',
            'Last Name',
            'Email',
            'Phone Number',
            'License Plate',
            'Created At'
        ];
    }

    public function map($user): array
    {
        return [
            $user->first_name,
            $user->last_name,
            $user->email,
            $user->phone_number,
            $user->license_plate,
            $user->created_at->format('F j, Y  H : i : s'),
        ];
    }
}
