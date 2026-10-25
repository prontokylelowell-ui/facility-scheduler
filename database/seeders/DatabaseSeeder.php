<?php

namespace Database\Seeders;

use App\Models\BlackoutDate;
use App\Models\Booking;
use App\Models\BookingStatusLog;
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
        $userRole = Role::where('name', 'app_user')->first();
        $reportRole = Role::where('name', 'report_viewer')->first();

        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@pup.edu.ph',
            'password' => Hash::make('admin123'),
            'role_id' => $adminRole->id,
        ]);

        $reportViewer = User::create([
            'first_name' => 'Report',
            'last_name' => 'Viewer',
            'email' => 'report@pup.edu.ph',
            'password' => Hash::make('report123'),
            'role_id' => $reportRole->id,
        ]);

        $users = collect();
        $names = [
            ['Juan', 'Dela Cruz', 'juan.delacruz@gmail.com'],
            ['Maria', 'Santos', 'maria.santos@gmail.com'],
            ['Ana', 'Reyes', 'ana.reyes@gmail.com'],
            ['Carlo', 'Fernandez', 'carlo.fernandez@gmail.com'],
            ['Liza', 'Ramos', 'liza.ramos@gmail.com'],
            ['Miguel', 'Torres', 'miguel.torres@gmail.com'],
        ];

        foreach ($names as [$fn, $ln, $email]) {
            $users->push(User::create([
                'first_name' => $fn,
                'last_name' => $ln,
                'email' => $email,
                'password' => Hash::make('password123'),
                'role_id' => $userRole->id,
            ]));
        }

        $facilityTypeIds = FacilityType::pluck('id', 'name');

        $facilities = [
            ['name' => 'Main Swimming Pool',        'type' => 'Swimming Pool',   'location' => 'Resort Grounds',       'capacity' => 120],
            ['name' => 'Kiddie Pool',               'type' => 'Swimming Pool',   'location' => 'Resort Grounds',       'capacity' => 40],
            ['name' => 'Grand Ballroom',            'type' => 'Function Hall',   'location' => 'Convention Center',    'capacity' => 300],
            ['name' => 'Garden Pavilion',           'type' => 'Pavilion',        'location' => 'Garden Area',          'capacity' => 150],
            ['name' => 'Beachfront Cottage A',      'type' => 'Cottage',         'location' => 'Beachfront',           'capacity' => 6],
            ['name' => 'Beachfront Cottage B',      'type' => 'Cottage',         'location' => 'Beachfront',           'capacity' => 6],
            ['name' => 'Hillside Cottage',          'type' => 'Cottage',         'location' => 'Hillside',             'capacity' => 4],
            ['name' => 'Tennis Court',              'type' => 'Sports Court',    'location' => 'Sports Complex',       'capacity' => 4],
            ['name' => 'Basketball Court',          'type' => 'Sports Court',    'location' => 'Sports Complex',       'capacity' => 10],
            ['name' => 'Executive Conference Room', 'type' => 'Conference Room', 'location' => 'Convention Center',    'capacity' => 20],
        ];

        $facilityRecords = collect();
        foreach ($facilities as $f) {
            $facilityRecords->push(Facility::create([
                'name' => $f['name'],
                'facility_type_id' => $facilityTypeIds[$f['type']],
                'location' => $f['location'],
                'capacity' => $f['capacity'],
                'is_active' => true,
                'notes' => "Standard {$f['type']} facility.",
            ]));
        }

        foreach ($facilityRecords as $facility) {
            for ($dow = 1; $dow <= 7; $dow++) {
                OperatingHour::create([
                    'facility_id' => $facility->id,
                    'day_of_week' => $dow,
                    'open_time' => '06:00:00',
                    'close_time' => '22:00:00',
                ]);
            }
        }

        BlackoutDate::create([
            'blackout_date' => Carbon::today()->addDays(30),
            'is_global' => true,
            'reason' => 'Resort-wide Annual Maintenance Day',
        ]);
        BlackoutDate::create([
            'facility_id' => $facilityRecords[4]->id,
            'blackout_date' => Carbon::today()->addDays(7),
            'is_global' => false,
            'reason' => 'Beachfront Cottage A Deep Cleaning',
        ]);

        $bookingCount = 0;
        $now = Carbon::now();

        for ($i = 0; $i < 30; $i++) {
            $user = $users->random();
            $facility = $facilityRecords->random();
            $dayOffset = random_int(0, 14);
            $startHour = random_int(8, 16);

            $start = (clone $now)->addDays($dayOffset)->hour($startHour)->minute(0)->second(0);
            $end = (clone $start)->addHours(random_int(1, 3));

            $status = match (random_int(0, 3)) {
                0 => Booking::STATUS_PENDING,
                1 => Booking::STATUS_APPROVED,
                2 => Booking::STATUS_REJECTED,
                default => Booking::STATUS_CANCELLED,
            };

            DB::statement("SELECT set_config('app.user_id', ?, false)", [(string) $user->id]);

            try {
                Booking::create([
                    'user_id' => $user->id,
                    'facility_id' => $facility->id,
                    'start_time' => $start,
                    'end_time' => $end,
                    'purpose' => [
                        'Pool party for birthday',
                        'Beach wedding reception',
                        'Corporate team building',
                        'Family reunion',
                        'Company seminar and workshop',
                        'Anniversary celebration',
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

        DB::statement('RESET app.user_id;');

        $this->command->info('Seeded:');
        $this->command->info('  - Users: '.User::count());
        $this->command->info('  - Facilities: '.Facility::count());
        $this->command->info('  - Operating hours: '.OperatingHour::count());
        $this->command->info('  - Blackout dates: '.BlackoutDate::count());
        $this->command->info("  - Bookings (no conflicts): {$bookingCount}");
        $this->command->info('  - Status logs: '.BookingStatusLog::count());
    }
}
