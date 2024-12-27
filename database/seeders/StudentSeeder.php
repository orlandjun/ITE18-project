<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run()
    {
        $students = [
            [
                'student_id' => '221-2021',
                'name' => 'John Doe',
                'course' => 'BSIT',
                'year_level' => '3rd',
                'qr_code' => '221-2021-VALID'
            ],
            [
                'student_id' => '221-2022',
                'name' => 'Jane Smith',
                'course' => 'BSIT',
                'year_level' => '3rd',
                'qr_code' => '221-2022-VALID'
            ],
            [
                'student_id' => '221-2023',
                'name' => 'Mike Johnson',
                'course' => 'BSIT',
                'year_level' => '3rd',
                'qr_code' => '221-2023-VALID'
            ],
            [
                'student_id' => '221-2024',
                'name' => 'Sarah Williams',
                'course' => 'BSIT',
                'year_level' => '3rd',
                'qr_code' => '221-2024-VALID'
            ],
            [
                'student_id' => '201-0061',
                'name' => 'Jade Collado',
                'course' => 'BSIT',
                'year_level' => '3rd',
                'qr_code' => '201-00617-VALID'
            ],
            [
                'student_id' => '201-0761',
                'name' => 'Orlandjun Gultia',
                'course' => 'BSIT',
                'year_level' => '3rd',
                'qr_code' => '201-0761-VALID'
            ],
        ];

        foreach ($students as $student) {
            Student::create($student);
        }
    }
} 