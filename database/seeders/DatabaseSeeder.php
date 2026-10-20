<?php

namespace Database\Seeders;

use App\Models\BlackoutDate;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\FacilityType;
use App\Models\OperatingHour;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("SELECT set_config('app.user_id', '1', false);");

        $adminRole = Role::where('name', 'app_admin')->first();
        $userRole  = Role::where('name', 'app_user')->first();
        $reportRole = Role::where('name', 'report_viewer')->first();

        $admin = User::create([
            'first_name' => 'Admin',
            'last_name'  => 'User',
            'email'      => 'admin@pup.edu.ph',
            'password'   => Hash::make('admin123'),
            'role_id'    => $adminRole->id,
        ]);

        $reportViewer = User::create([
            'first_name' => 'Report',
            'last_name'  => 'Viewer',
            'email'      => 'report@pup.edu.ph',
            'password'   => Hash::make('report123'),
            'role_id'    => $reportRole->id,
        ]);

        $users = collect();
        $names = [
            ['Juan', 'Dela Cruz', 'juan.delacruz@pup.edu.ph'],
            ['Maria', 'Santos', 'maria.santos@pup.edu.ph'],
            ['Ana', 'Reyes', 'ana.reyes@pup.edu.ph'],
            ['Carlo', 'Fernandez', 'carlo.fernandez@pup.edu.ph'],
            ['Liza', 'Ramos', 'liza.ramos@pup.edu.ph'],
            ['Miguel', 'Torres', 'miguel.torres@pup.edu.ph'],
        ];

        foreach ($names as [$fn, $ln, $email]) {
            $users->push(User::create([
                'first_name' => $fn,
                'last_name'  => $ln,
                'email'      => $email,
                'password'   => Hash::make('password123'),
                'role_id'    => $userRole->id,
            ]));
        }

        $facilityTypeIds = FacilityType::pluck('id', 'name');

        $facilities = [
            ['name' => 'Meeting Room A101', 'type' => 'Meeting Room', 'location' => 'Admin Building 1st Floor', 'capacity' => 12],
            ['name' => 'Meeting Room B205', 'type' => 'Meeting Room', 'location' => 'Academic Building 2nd Floor', 'capacity' => 8],
            ['name' => 'Lecture Hall 301',  'type' => 'Lecture Hall', 'location' => 'Academic Building 3rd Floor', 'capacity' => 150],
            ['name' => 'Lecture Hall 401',  'type' => 'Lecture Hall', 'location' => 'Academic Building 4th Floor', 'capacity' => 120],
            ['name' => 'Computer Lab 1',    'type' => 'Laboratory',   'location' => 'IT Building 1st Floor',      'capacity' => 40],
            ['name' => 'Computer Lab 2',    'type' => 'Laboratory',   'location' => 'IT Building 2nd Floor',      'capacity' => 35],
            ['name' => 'Physics Laboratory','type' => 'Laboratory',   'location' => 'Science Building 1st Floor', 'capacity' => 30],
            ['name' => 'Main Gymnasium',    'type' => 'Gymnasium',    'location' => 'PE Complex',                 'capacity' => 500],
            ['name' => 'Mini Gym',          'type' => 'Gymnasium',    'location' => 'Student Center',             'capacity' => 150],
            ['name' => 'University Auditorium', 'type' => 'Auditorium', 'location' => 'Cultural Building',       'capacity' => 800],
        ];

        $facilityRecords = collect();
        foreach ($facilities as $f) {
            $facilityRecords->push(Facility::create([
                'name'             => $f['name'],
                'facility_type_id' => $facilityTypeIds[$f['type']],
                'location'         => $f['location'],
                'capacity'         => $f['capacity'],
                'is_active'        => true,
                'notes'            => "Standard {$f['type']} facility.",
            ]));
        }

        foreach ($facilityRecords as $facility) {
            for ($dow = 1; $dow <= 5; $dow++) {
                OperatingHour::create([
                    'facility_id' => $facility->id,
                    'day_of_week' => $dow,
                    'open_time'   => '07:00:00',
                    'close_time'  => '20:00:00',
                ]);
            }
            OperatingHour::create([
                'facility_id' => $facility->id,
                'day_of_week' => 6,
                'open_time'   => '08:00:00',
                'close_time'  => '17:00:00',
            ]);
        }

        BlackoutDate::create([
            'blackout_date' => Carbon::today()->addDays(30),
            'is_global'     => true,
            'reason'        => 'University Holiday',
        ]);
        BlackoutDate::create([
            'facility_id'   => $facilityRecords[7]->id,
            'blackout_date' => Carbon::today()->addDays(7),
            'is_global'     => false,
            'reason'        => 'Annual Gym Maintenance',
        ]);

        $bookingCount = 0;
        $now = Carbon::now();

        for ($i = 0; $i < 30; $i++) {
            $user = $users->random();
            $facility = $facilityRecords->random();
            $dayOffset = random_int(0, 14);
            $startHour = random_int(8, 16);

            $start = (clone $now)->addDays($dayOffset)->hour($startHour)->minute(0)->second(0);
            $end   = (clone $start)->addHours(random_int(1, 3));

            $status = match (random_int(0, 3)) {
                0 => Booking::STATUS_PENDING,
                1 => Booking::STATUS_APPROVED,
                2 => Booking::STATUS_REJECTED,
                default => Booking::STATUS_CANCELLED,
            };

            DB::statement("SELECT set_config('app.user_id', ?, false)", [(string) $user->id]);

            try {
                Booking::create([
                    'user_id'     => $user->id,
                    'facility_id' => $facility->id,
                    'start_time'  => $start,
                    'end_time'    => $end,
                    'purpose'     => [
                        'Group meeting for research project',
                        'Class lecture make-up',
                        'Lab session for CS students',
                        'Organization event',
                        'Review session for finals',
                        'Thesis proposal defense',
                    ][random_int(0, 5)],
                    'status' => $status,
                    'admin_remarks' => $status === Booking::STATUS_REJECTED
                        ? 'Schedule conflicts with prior reservation.'
                        : null,
                ]);
                $bookingCount++;
            } catch (\Exception $e) {
                // Skip conflicts from exclusion constraint
                continue;
            }
        }

        DB::statement("RESET app.user_id;");

        $this->command->info("Seeded:");
        $this->command->info("  - Users: " . User::count());
        $this->command->info("  - Facilities: " . Facility::count());
        $this->command->info("  - Operating hours: " . OperatingHour::count());
        $this->command->info("  - Blackout dates: " . BlackoutDate::count());
        $this->command->info("  - Bookings (no conflicts): {$bookingCount}");
        $this->command->info("  - Status logs: " . \App\Models\BookingStatusLog::count());
    }
}
