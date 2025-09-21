<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hospital;
use App\Models\Clinic;

class HospitalsClinicsSeeder extends Seeder
{
    public function run()
    {
        // Sample Hospitals
        $hospitals = [
            [
                'name' => 'Hospital Kuala Lumpur',
                'address' => 'Jalan Pahang, 53000 Kuala Lumpur',
                'city' => 'Kuala Lumpur',
                'state' => 'Kuala Lumpur',
                'postal_code' => '53000',
                'phone_number' => '03-26155555',
                'is_panel' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Sunway Medical Centre',
                'address' => '5, Jalan Lagoon Selatan, Bandar Sunway, 47500 Subang Jaya',
                'city' => 'Subang Jaya',
                'state' => 'Selangor',
                'postal_code' => '47500',
                'phone_number' => '03-74918000',
                'is_panel' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Gleneagles Kuala Lumpur',
                'address' => '282 & 286, Jalan Ampang, 50450 Kuala Lumpur',
                'city' => 'Kuala Lumpur',
                'state' => 'Kuala Lumpur',
                'postal_code' => '50450',
                'phone_number' => '03-41413000',
                'is_panel' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Pantai Hospital Kuala Lumpur',
                'address' => '8, Jalan Bukit Pantai, 59200 Kuala Lumpur',
                'city' => 'Kuala Lumpur',
                'state' => 'Kuala Lumpur',
                'postal_code' => '59200',
                'phone_number' => '03-22961000',
                'is_panel' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Hospital Selayang',
                'address' => 'Lebuhraya Selayang-Kepong, 68100 Batu Caves',
                'city' => 'Batu Caves',
                'state' => 'Selangor',
                'postal_code' => '68100',
                'phone_number' => '03-61203200',
                'is_panel' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Hospital Putrajaya',
                'address' => 'Presint 7, 62250 Putrajaya',
                'city' => 'Putrajaya',
                'state' => 'Putrajaya',
                'postal_code' => '62250',
                'phone_number' => '03-83124200',
                'is_panel' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Hospital Seberang Jaya',
                'address' => 'Jalan Tun Hussein Onn, 13700 Seberang Jaya',
                'city' => 'Seberang Jaya',
                'state' => 'Pulau Pinang',
                'postal_code' => '13700',
                'phone_number' => '04-3825333',
                'is_panel' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Hospital Sultanah Aminah',
                'address' => 'Jalan Persiaran Abu Bakar Sultan, 80000 Johor Bahru',
                'city' => 'Johor Bahru',
                'state' => 'Johor',
                'postal_code' => '80000',
                'phone_number' => '07-2257000',
                'is_panel' => true,
                'is_active' => true,
            ],
        ];

        foreach ($hospitals as $hospital) {
            Hospital::create($hospital);
        }

        // Sample Clinics
        $clinics = [
            [
                'name' => 'Klinik Kesihatan Bandar',
                'address' => 'Jalan Raja Laut, 50350 Kuala Lumpur',
                'city' => 'Kuala Lumpur',
                'state' => 'Kuala Lumpur',
                'postal_code' => '50350',
                'phone_number' => '03-26912020',
                'is_panel' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Klinik Kesihatan Petaling Jaya',
                'address' => 'Jalan SS 2/61, 47300 Petaling Jaya',
                'city' => 'Petaling Jaya',
                'state' => 'Selangor',
                'postal_code' => '47300',
                'phone_number' => '03-79542020',
                'is_panel' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Klinik Kesihatan Georgetown',
                'address' => 'Jalan Macalister, 10400 Georgetown',
                'city' => 'Georgetown',
                'state' => 'Pulau Pinang',
                'postal_code' => '10400',
                'phone_number' => '04-2288200',
                'is_panel' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Klinik Kesihatan Johor Bahru',
                'address' => 'Jalan Tun Abdul Razak, 80000 Johor Bahru',
                'city' => 'Johor Bahru',
                'state' => 'Johor',
                'postal_code' => '80000',
                'phone_number' => '07-2231200',
                'is_panel' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Klinik Kesihatan Ipoh',
                'address' => 'Jalan Raja Dr Nazrin Shah, 30300 Ipoh',
                'city' => 'Ipoh',
                'state' => 'Perak',
                'postal_code' => '30300',
                'phone_number' => '05-2083200',
                'is_panel' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Klinik Kesihatan Kota Kinabalu',
                'address' => 'Jalan Lintas, 88300 Kota Kinabalu',
                'city' => 'Kota Kinabalu',
                'state' => 'Sabah',
                'postal_code' => '88300',
                'phone_number' => '088-324600',
                'is_panel' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Klinik Kesihatan Kuching',
                'address' => 'Jalan Tun Ahmad Zaidi Adruce, 93100 Kuching',
                'city' => 'Kuching',
                'state' => 'Sarawak',
                'postal_code' => '93100',
                'phone_number' => '082-244600',
                'is_panel' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Klinik Kesihatan Alor Setar',
                'address' => 'Jalan Sultanah, 05000 Alor Setar',
                'city' => 'Alor Setar',
                'state' => 'Kedah',
                'postal_code' => '05000',
                'phone_number' => '04-7341200',
                'is_panel' => true,
                'is_active' => true,
            ],
        ];

        foreach ($clinics as $clinic) {
            Clinic::create($clinic);
        }

        $this->command->info('Hospitals and Clinics seeded successfully!');
    }
}
