<?php

namespace Database\Seeders;

use App\Models\Boardroom;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BoardroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Boardroom::firstOrCreate(
            ['name' => 'Executive Suite A'],
            [
                'capacity' => 20,
                'location' => '3rd Floor',
                'status' => 'Available',
                'amenities' => 'Polycom Video Conf, Dual 75" Screens',
            ],
        );

        Boardroom::firstOrCreate(
            ['name' => 'Innovation Hub B'],
            [
                'capacity' => 12,
                'location' => '2nd Floor',
                'status' => 'Available',
                'amenities' => 'Smart Whiteboard, Wireless Casting',
            ],
        );
    }
}
