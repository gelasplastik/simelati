<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\ParentModel;
use App\Models\ScheduleProfile;
use App\Models\SchoolClass;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentPermission;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        

        User::query()->updateOrCreate([
            'email' => 'admin@sdplusmelati.local',
        ], [
            'name' => 'Admin SIMELATI',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::query()->updateOrCreate([
            'email' => 'superadmin@sdplusmelati.local',
        ], [
            'name' => 'Superadmin SIMELATI',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
        ]);

        $classNames = ['1', '2A', '2B', '3A', '3B', '4', '5', '6A', '6B'];
        $classes = collect($classNames)->mapWithKeys(function ($name) {
            $class = SchoolClass::query()->firstOrCreate(['name' => $name]);
            $class->update(['is_active' => true]);

            return [$name => $class->fresh()];
        });

        $subjectNames = [
            'Matematika',
            'Bahasa Indonesia',
            'Bahasa Inggris',
            'IPAS',
            'AKM',
            'PLH',
            'Seni Budaya',
            'Seni Teater',
            'Pendidikan Pancasila',
            'PAI',
            'Tahfidz',
            'PJOK',
            'Informatika',
            'PjBL',
            'Calistung',
        ];

        $subjects = collect($subjectNames)->mapWithKeys(function ($name) {
            $subject = Subject::query()->firstOrCreate(['name' => $name]);

            return [$name => $subject];
        });

        $teacherDefinitions = [
            [
                'name' => 'Nurul Hana Hidayah, S.Pd',
                'email' => 'nurul-hana-hidayah@sdplusmelati.local',
                'assignments' => [
                    'Matematika' => ['1', '2A', '2B', '3A', '3B'],
                    'Tahfidz' => ['4'],
                    'PjBL' => ['3B'],
                ],
            ],
            [
                'name' => 'Samsiah, S.Pd',
                'email' => 'samsiah@sdplusmelati.local',
                'assignments' => [
                    'Bahasa Indonesia' => ['1', '2A', '2B'],
                    'Pendidikan Pancasila' => ['1', '4'],
                    'PjBL' => ['1'],
                    'Calistung' => ['1'],
                ],
            ],
            [
                'name' => 'Risa Nur Sofitri, S.Pd',
                'email' => 'risa-nur-sofitri@sdplusmelati.local',
                'assignments' => [
                    'Bahasa Inggris' => ['1', '2A', '2B', '3A', '3B', '4', '5'],
                    'PjBL' => ['2B'],
                    'Bahasa Indonesia' => ['4'],
                ],
            ],
            [
                'name' => 'Rohadatul Aisy, S.Pd',
                'email' => 'rohadatul-aisy@sdplusmelati.local',
                'assignments' => [
                    'Pendidikan Pancasila' => ['2A', '2B', '3A', '3B', '6A', '6B'],
                    'PjBL' => ['6B'],
                ],
            ],
            [
                'name' => 'Tyas Dwi Fitriyanti, S.Pd',
                'email' => 'tyas-dwi-fitriyanti@sdplusmelati.local',
                'assignments' => [
                    'IPAS' => ['1', '2A', '2B', '3A', '3B', '6A', '6B'],
                    'PjBL' => ['3A'],
                ],
            ],
            [
                'name' => 'Linda Herlianah, S.Pd',
                'email' => 'linda-herlianah@sdplusmelati.local',
                'assignments' => [
                    'PLH' => ['1', '2A', '2B', '3A', '3B', '4', '5', '6A', '6B'],
                    'Seni Budaya' => ['2A'],
                    'Bahasa Indonesia' => ['2A', '2B'],
                    'PjBL' => ['2A'],
                ],
            ],
            [
                'name' => 'Humairah H, S.Pd',
                'email' => 'humairah-h@sdplusmelati.local',
                'assignments' => [
                    'Bahasa Indonesia' => ['3A', '3B', '5', '6A', '6B'],
                    'AKM' => ['3A', '3B', '5', '6A', '6B'],
                    'Seni Budaya' => ['5'],
                    'Pendidikan Pancasila' => ['5'],
                    'PjBL' => ['5'],
                ],
            ],
            [
                'name' => 'Risma Pebriana, S.Pd',
                'email' => 'risma-pebriana@sdplusmelati.local',
                'assignments' => [
                    'Seni Budaya' => ['1', '2A', '2B', '3A', '3B', '4', '6A', '6B'],
                    'Seni Teater' => ['2B', '3A'],
                    'IPAS' => ['4', '5'],
                    'PjBL' => ['4'],
                ],
            ],
            [
                'name' => 'Dilla, S.Pd',
                'email' => 'dilla@sdplusmelati.local',
                'assignments' => [
                    'PAI' => ['1', '2A', '2B', '4', '6A', '6B'],
                    'Tahfidz' => ['1', '2A', '2B', '4', '6A', '6B'],
                    'PjBL' => ['6A'],
                ],
            ],
            [
                'name' => 'Nurlinda T. M., S.Pd',
                'email' => 'nurlinda-tm@sdplusmelati.local',
                'assignments' => [
                    'Matematika' => ['4', '5', '6A', '6B'],
                ],
            ],
            [
                'name' => 'Syaifuddin, S.Pd.I',
                'email' => 'syaifuddin@sdplusmelati.local',
                'assignments' => [
                    'PAI' => ['3A', '3B', '5'],
                ],
            ],
            [
                'name' => 'Hery Isriyadi, S.Pd',
                'email' => 'hery-isriyadi@sdplusmelati.local',
                'assignments' => [
                    'Bahasa Inggris' => ['6A', '6B'],
                ],
            ],
            [
                'name' => 'Nadia Anjelika, S.Pd',
                'email' => 'nadia-anjelika@sdplusmelati.local',
                'assignments' => [
                    'PJOK' => ['1', '2A', '2B', '3A', '3B', '4', '5', '6A', '6B'],
                ],
            ],
            [
                'name' => 'Miki Sandi, S.Pd',
                'email' => 'miki-sandi@sdplusmelati.local',
                'assignments' => [
                    'Informatika' => ['4', '5', '6A', '6B'],
                ],
            ],
        ];

        $legacyTeacherUsers = User::query()
            ->where('role', 'teacher')
            ->where('email', 'like', 'teacher%@sdplusmelati.local')
            ->orderBy('id')
            ->get()
            ->values();

        $realTeachers = collect($teacherDefinitions)->map(function (array $definition, int $index) use ($legacyTeacherUsers) {
            $legacy = $legacyTeacherUsers->get($index);
            $targetEmail = $definition['email'];

            $userByEmail = User::query()->where('email', $targetEmail)->first();

            if ($userByEmail) {
                $user = $userByEmail;
                $user->update([
                    'name' => $definition['name'],
                    'password' => Hash::make('password'),
                    'role' => 'teacher',
                ]);
            } elseif ($legacy) {
                $legacy->update([
                    'name' => $definition['name'],
                    'email' => $targetEmail,
                    'password' => Hash::make('password'),
                    'role' => 'teacher',
                ]);
                $user = $legacy->fresh();
            } else {
                $user = User::query()->create([
                    'name' => $definition['name'],
                    'email' => $targetEmail,
                    'password' => Hash::make('password'),
                    'role' => 'teacher',
                ]);
            }

            $employeeCode = sprintf('T%03d', $index + 1);
            $teacher = Teacher::query()->firstOrCreate(
                ['user_id' => $user->id],
                ['employee_code' => $employeeCode]
            );

            if ($teacher->employee_code !== $employeeCode && ! Teacher::query()->where('employee_code', $employeeCode)->where('id', '!=', $teacher->id)->exists()) {
                $teacher->update(['employee_code' => $employeeCode]);
            }

            return $teacher->fresh(['user']);
        });

        $realTeacherIds = $realTeachers->pluck('id')->all();

        Assignment::query()->whereNotIn('teacher_id', $realTeacherIds)->delete();
        Assignment::query()->whereIn('teacher_id', $realTeacherIds)->delete();

        foreach ($teacherDefinitions as $definition) {
            $teacher = $realTeachers->first(fn (Teacher $item) => $item->user->email === $definition['email']);
            if (! $teacher) {
                continue;
            }

            foreach ($definition['assignments'] as $subjectName => $classList) {
                $subject = $subjects->get($subjectName);
                if (! $subject) {
                    continue;
                }

                foreach ($classList as $className) {
                    $class = $classes->get($className);
                    if (! $class) {
                        continue;
                    }

                    Assignment::query()->firstOrCreate([
                        'teacher_id' => $teacher->id,
                        'subject_id' => $subject->id,
                        'class_id' => $class->id,
                    ]);
                }
            }
        }

        Teacher::query()->whereNotNull('homeroom_class_id')->update(['homeroom_class_id' => null]);

        $classes->values()->each(function ($class, $index) use ($realTeachers) {
            $homeroomTeacher = $realTeachers[$index % $realTeachers->count()] ?? null;
            $class->update([
                'is_active' => true,
                'homeroom_teacher_id' => $homeroomTeacher?->id,
            ]);
            if ($homeroomTeacher) {
                $homeroomTeacher->update(['homeroom_class_id' => $class->id]);
            }
        });

        $students = collect(range(1, 30))->map(function ($i) use ($classes) {
            return Student::query()->updateOrCreate([
                'nis' => sprintf('2026%04d', $i),
            ], [
                'nisn' => sprintf('0062026%04d', $i),
                'full_name' => "Siswa {$i}",
                'class_id' => $classes->values()[($i - 1) % $classes->count()]->id,
                'is_active' => true,
            ]);
        });

        $parentUser = User::query()->updateOrCreate([
            'email' => 'parent1@sdplusmelati.local',
        ], [
            'name' => 'Orang Tua Contoh',
            'password' => Hash::make('password'),
            'role' => 'parent',
        ]);

        $parent = ParentModel::query()->firstOrCreate([
            'user_id' => $parentUser->id,
        ], [
            'phone' => '081234567890',
        ]);

        $parent->students()->syncWithoutDetaching([$students->first()->id, $students->get(1)->id]);

        StudentPermission::query()->firstOrCreate([
            'student_id' => $students->first()->id,
            'parent_id' => $parent->id,
            'date_from' => now()->subDay()->toDateString(),
            'date_to' => now()->addDay()->toDateString(),
        ], [
            'submitter_name' => $parentUser->name,
            'submitter_phone' => $parent->phone,
            'submitter_relationship' => 'Orang Tua',
            'reason' => 'Sakit demam',
            'status' => 'approved',
        ]);

        Setting::active();
        $profiles = $this->seedScheduleProfiles();
        $this->seedTeachingSchedulesByProfile($profiles['normal'], $profiles['ramadhan']);
    }

    private function seedScheduleProfiles(): array
    {
        $normal = ScheduleProfile::query()->updateOrCreate(
            ['code' => 'normal'],
            [
                'name' => 'Normal',
                'description' => 'Jadwal reguler sekolah',
            ]
        );

        $ramadhan = ScheduleProfile::query()->updateOrCreate(
            ['code' => 'ramadhan'],
            [
                'name' => 'Ramadhan',
                'description' => 'Jadwal khusus bulan Ramadhan',
            ]
        );

        $activeCount = ScheduleProfile::query()->where('is_active', true)->count();
        if ($activeCount !== 1) {
            ScheduleProfile::query()->update(['is_active' => false]);
            $normal->update(['is_active' => true]);
        }

        return [
            'normal' => $normal->fresh(),
            'ramadhan' => $ramadhan->fresh(),
        ];
    }

    private function seedTeachingSchedulesByProfile(ScheduleProfile $normal, ScheduleProfile $ramadhan): void
    {
        $codeMappings = [
            3 => ['email' => 'nurul-hana-hidayah@sdplusmelati.local', 'subject' => 'Matematika'],
            4 => ['email' => 'samsiah@sdplusmelati.local', 'subject' => 'Bahasa Indonesia'],
            5 => ['email' => 'samsiah@sdplusmelati.local', 'subject' => 'Pendidikan Pancasila'],
            6 => ['email' => 'samsiah@sdplusmelati.local', 'subject' => 'Calistung'],
            7 => ['email' => 'risa-nur-sofitri@sdplusmelati.local', 'subject' => 'Bahasa Inggris'],
            8 => ['email' => 'risa-nur-sofitri@sdplusmelati.local', 'subject' => 'PjBL'],
            9 => ['email' => 'rohadatul-aisy@sdplusmelati.local', 'subject' => 'Pendidikan Pancasila'],
            10 => ['email' => 'rohadatul-aisy@sdplusmelati.local', 'subject' => 'PjBL'],
            11 => ['email' => 'tyas-dwi-fitriyanti@sdplusmelati.local', 'subject' => 'IPAS'],
            12 => ['email' => 'tyas-dwi-fitriyanti@sdplusmelati.local', 'subject' => 'PjBL'],
            13 => ['email' => 'linda-herlianah@sdplusmelati.local', 'subject' => 'PLH'],
            20 => ['email' => 'risma-pebriana@sdplusmelati.local', 'subject' => 'Seni Budaya'],
            21 => ['email' => 'risma-pebriana@sdplusmelati.local', 'subject' => 'IPAS'],
            22 => ['email' => 'risma-pebriana@sdplusmelati.local', 'subject' => 'PjBL'],
            23 => ['email' => 'dilla@sdplusmelati.local', 'subject' => 'PAI'],
            24 => ['email' => 'dilla@sdplusmelati.local', 'subject' => 'Tahfidz'],
            25 => ['email' => 'dilla@sdplusmelati.local', 'subject' => 'PjBL'],
            26 => ['email' => 'nurlinda-tm@sdplusmelati.local', 'subject' => 'Matematika'],
            27 => ['email' => 'syaifuddin@sdplusmelati.local', 'subject' => 'PAI'],
            29 => ['email' => 'hery-isriyadi@sdplusmelati.local', 'subject' => 'Bahasa Inggris'],
            30 => ['email' => 'nadia-anjelika@sdplusmelati.local', 'subject' => 'PJOK'],
            31 => ['email' => 'miki-sandi@sdplusmelati.local', 'subject' => 'Informatika'],
        ];

        $normalSlots = [
            1 => ['start' => '07:30:00', 'end' => '08:05:00'],
            2 => ['start' => '08:05:00', 'end' => '08:40:00'],
            3 => ['start' => '08:40:00', 'end' => '09:15:00'],
            4 => ['start' => '09:15:00', 'end' => '09:50:00'],
            5 => ['start' => '10:25:00', 'end' => '11:00:00'],
            6 => ['start' => '11:00:00', 'end' => '11:35:00'],
            7 => ['start' => '12:45:00', 'end' => '13:20:00'],
            8 => ['start' => '13:20:00', 'end' => '13:55:00'],
        ];

        $ramadhanSlots = [
            1 => ['start' => '08:30:00', 'end' => '09:00:00'],
            2 => ['start' => '09:00:00', 'end' => '09:30:00'],
            3 => ['start' => '09:30:00', 'end' => '10:00:00'],
            4 => ['start' => '10:00:00', 'end' => '10:30:00'],
            5 => ['start' => '11:00:00', 'end' => '11:30:00'],
            6 => ['start' => '11:30:00', 'end' => '12:00:00'],
        ];

        $normalGrid = [
            'monday' => [1 => [30, 21], 2 => [30, 21], 3 => [4, 11, 20], 4 => [4, 11, 20], 5 => [23, 11, 4, 30], 6 => [23, 11, 4, 30], 7 => [23, 11, 5, 7, 26, 9], 8 => [23, 11, 5, 7, 26, 9]],
            'tuesday' => [1 => [9, 22, 26], 2 => [9, 22, 26], 3 => [30, 23, 11], 4 => [30, 23, 11], 5 => [23, 30, 7, 20], 6 => [23, 30, 7, 20], 7 => [5, 23, 9, 12, 7, 26], 8 => [5, 23, 9, 12, 7, 26]],
            'wednesday' => [1 => [20, 23, 26, 9, 30], 2 => [20, 23, 26, 9, 30], 3 => [4, 9, 23, 21, 26, 11], 4 => [4, 9, 23, 21, 26, 11], 5 => [20, 7, 4, 9], 6 => [20, 7, 4, 9], 7 => [4, 20, 11, 8], 8 => [4, 20, 11, 8]],
            'thursday' => [1 => [7, 23, 30, 11], 2 => [7, 23, 30, 11], 3 => [5, 9, 7, 30, 20], 4 => [5, 9, 7, 30, 20], 5 => [11, 30, 9, 5], 6 => [11, 30, 9, 5], 7 => [6, 23, 20, 9, 26], 8 => [6, 23, 20, 9, 26]],
            'friday' => [1 => [], 2 => [], 3 => [], 4 => [], 5 => [7, 20, 9, 11, 23], 6 => [7, 20, 9, 11, 23], 7 => [], 8 => []],
        ];

        $ramadhanGrid = [
            'monday' => [1 => [7, 30, 21, 26, 9], 2 => [7, 30, 21, 26, 9], 3 => [4, 11, 20, 26], 4 => [4, 11, 20, 26], 5 => [23, 11, 4, 20, 30], 6 => [23, 11, 4, 20, 30]],
            'tuesday' => [1 => [30, 11, 7, 26], 2 => [30, 11, 7, 26], 3 => [30, 23, 11], 4 => [30, 23, 11], 5 => [23, 4, 30, 20], 6 => [23, 4, 30, 20]],
            'wednesday' => [1 => [20, 23, 26, 9, 30], 2 => [20, 23, 26, 9, 30], 3 => [4, 23, 21, 26], 4 => [4, 23, 21, 26], 5 => [20, 7, 4, 11], 6 => [20, 7, 4, 11]],
            'thursday' => [1 => [7, 23, 30, 26], 2 => [7, 23, 30, 26], 3 => [5, 9, 7, 26, 20], 4 => [5, 9, 7, 26, 20], 5 => [30, 9, 7], 6 => [30, 9, 7]],
            'friday' => [1 => [11, 23, 20, 9, 8], 2 => [11, 23, 20, 9, 8], 3 => [], 4 => [7, 20, 9, 5, 11, 23], 5 => [7, 20, 9, 5, 11, 23], 6 => []],
        ];

        $normalExists = TeachingSchedule::query()
            ->where('schedule_profile_id', $normal->id)
            ->exists();

        if (! $normalExists) {
            $normalRows = $this->buildRowsFromGrid($normal->id, $normalGrid, $normalSlots, $codeMappings);
            if ($normalRows !== []) {
                TeachingSchedule::query()->insert($normalRows);
            }
        }

        $ramadhanExists = TeachingSchedule::query()
            ->where('schedule_profile_id', $ramadhan->id)
            ->exists();

        if (! $ramadhanExists) {
            $ramadhanRows = $this->buildRowsFromGrid($ramadhan->id, $ramadhanGrid, $ramadhanSlots, $codeMappings);
            if ($ramadhanRows !== []) {
                TeachingSchedule::query()->insert($ramadhanRows);
            }
        }

        $this->applyExactRamadhanClasses($ramadhan);
    }

    private function buildRowsFromGrid(int $profileId, array $grid, array $slotTimes, array $codeMappings): array
    {
        $allAssignments = Assignment::query()
            ->with(['teacher.user', 'class', 'subject'])
            ->get();

        $assignmentLookup = [];
        foreach ($codeMappings as $code => $mapping) {
            $key = $mapping['email'].'|'.$mapping['subject'];
            $matches = $allAssignments
                ->filter(fn (Assignment $assignment) => $assignment->teacher->user->email === $mapping['email'] && $assignment->subject->name === $mapping['subject'])
                ->sortBy(fn (Assignment $assignment) => $assignment->class->name)
                ->values();

            if ($matches->isNotEmpty()) {
                $assignmentLookup[$code] = $matches->all();
            }
        }

        $rows = [];
        $cursorByCode = [];
        $busyTeacherSlot = [];
        $busyClassSlot = [];
        $uniqueRows = [];

        foreach ($grid as $dayOfWeek => $slots) {
            foreach ($slots as $jamKe => $codes) {
                foreach ($codes as $code) {
                    if (! isset($assignmentLookup[$code])) {
                        continue;
                    }

                    $pool = $assignmentLookup[$code];
                    $poolCount = count($pool);
                    if ($poolCount === 0) {
                        continue;
                    }

                    $startIndex = $cursorByCode[$code] ?? 0;
                    $picked = null;
                    $pickedIndex = null;

                    for ($i = 0; $i < $poolCount; $i++) {
                        $idx = ($startIndex + $i) % $poolCount;
                        $assignment = $pool[$idx];

                        $teacherSlotKey = $profileId.'|'.$dayOfWeek.'|'.$jamKe.'|'.$assignment->teacher_id;
                        $classSlotKey = $profileId.'|'.$dayOfWeek.'|'.$jamKe.'|'.$assignment->class_id;
                        $uniqueKey = implode('|', [
                            $profileId,
                            $assignment->teacher_id,
                            $assignment->class_id,
                            $assignment->subject_id,
                            $dayOfWeek,
                            $jamKe,
                        ]);

                        if (isset($busyTeacherSlot[$teacherSlotKey]) || isset($busyClassSlot[$classSlotKey]) || isset($uniqueRows[$uniqueKey])) {
                            continue;
                        }

                        $picked = $assignment;
                        $pickedIndex = $idx;
                        $busyTeacherSlot[$teacherSlotKey] = true;
                        $busyClassSlot[$classSlotKey] = true;
                        $uniqueRows[$uniqueKey] = true;
                        break;
                    }

                    if (! $picked) {
                        continue;
                    }

                    $cursorByCode[$code] = ($pickedIndex + 1) % $poolCount;
                    $slotTime = $slotTimes[$jamKe] ?? ['start' => null, 'end' => null];

                    $rows[] = [
                        'schedule_profile_id' => $profileId,
                        'teacher_id' => $picked->teacher_id,
                        'class_id' => $picked->class_id,
                        'subject_id' => $picked->subject_id,
                        'day_of_week' => $dayOfWeek,
                        'jam_ke' => $jamKe,
                        'start_time' => $slotTime['start'],
                        'end_time' => $slotTime['end'],
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        return $rows;
    }

    private function applyExactRamadhanClasses(ScheduleProfile $ramadhan): void
    {
        $seniTeater = Subject::query()->firstOrCreate(['name' => 'Seni Teater']);

        $classMap = SchoolClass::query()
            ->whereIn('name', ['4', '5', '6A', '6B'])
            ->get()
            ->keyBy('name');

        $targetClassIds = $classMap->pluck('id')->values()->all();

        TeachingSchedule::query()
            ->where('schedule_profile_id', $ramadhan->id)
            ->whereIn('class_id', $targetClassIds)
            ->delete();

        $rows = [
            ['class' => '6B', 'day' => 'monday', 'jam_ke' => 1, 'subject' => 'Pendidikan Pancasila'],
            ['class' => '6B', 'day' => 'monday', 'jam_ke' => 2, 'subject' => 'Matematika'],
            ['class' => '6B', 'day' => 'tuesday', 'jam_ke' => 1, 'subject' => 'Matematika'],
            ['class' => '6B', 'day' => 'tuesday', 'jam_ke' => 2, 'subject' => 'IPAS'],
            ['class' => '6B', 'day' => 'wednesday', 'jam_ke' => 1, 'subject' => 'PJOK'],
            ['class' => '6B', 'day' => 'wednesday', 'jam_ke' => 2, 'subject' => 'Bahasa Indonesia'],
            ['class' => '6B', 'day' => 'thursday', 'jam_ke' => 1, 'subject' => 'Matematika'],
            ['class' => '6B', 'day' => 'thursday', 'jam_ke' => 2, 'subject' => 'Seni Teater'],
            ['class' => '6B', 'day' => 'friday', 'jam_ke' => 1, 'subject' => 'Bahasa Indonesia'],
            ['class' => '6B', 'day' => 'friday', 'jam_ke' => 2, 'subject' => 'PAI'],

            ['class' => '6A', 'day' => 'monday', 'jam_ke' => 1, 'subject' => 'Matematika'],
            ['class' => '6A', 'day' => 'monday', 'jam_ke' => 2, 'subject' => 'Seni Budaya'],
            ['class' => '6A', 'day' => 'tuesday', 'jam_ke' => 1, 'subject' => 'Bahasa Indonesia'],
            ['class' => '6A', 'day' => 'tuesday', 'jam_ke' => 2, 'subject' => 'PAI'],
            ['class' => '6A', 'day' => 'wednesday', 'jam_ke' => 1, 'subject' => 'Pendidikan Pancasila'],
            ['class' => '6A', 'day' => 'wednesday', 'jam_ke' => 2, 'subject' => 'Matematika'],
            ['class' => '6A', 'day' => 'thursday', 'jam_ke' => 1, 'subject' => 'Bahasa Indonesia'],
            ['class' => '6A', 'day' => 'thursday', 'jam_ke' => 2, 'subject' => 'Matematika'],
            ['class' => '6A', 'day' => 'friday', 'jam_ke' => 1, 'subject' => 'PJOK'],
            ['class' => '6A', 'day' => 'friday', 'jam_ke' => 2, 'subject' => 'IPAS'],

            ['class' => '5', 'day' => 'monday', 'jam_ke' => 1, 'subject' => 'Bahasa Indonesia'],
            ['class' => '5', 'day' => 'monday', 'jam_ke' => 2, 'subject' => 'Seni Teater'],
            ['class' => '5', 'day' => 'tuesday', 'jam_ke' => 1, 'subject' => 'Bahasa Inggris'],
            ['class' => '5', 'day' => 'tuesday', 'jam_ke' => 2, 'subject' => 'Bahasa Indonesia'],
            ['class' => '5', 'day' => 'wednesday', 'jam_ke' => 1, 'subject' => 'Matematika'],
            ['class' => '5', 'day' => 'wednesday', 'jam_ke' => 2, 'subject' => 'IPAS'],
            ['class' => '5', 'day' => 'thursday', 'jam_ke' => 1, 'subject' => 'PJOK'],
            ['class' => '5', 'day' => 'thursday', 'jam_ke' => 2, 'subject' => 'Pendidikan Pancasila'],
            ['class' => '5', 'day' => 'friday', 'jam_ke' => 1, 'subject' => 'PAI'],
            ['class' => '5', 'day' => 'friday', 'jam_ke' => 2, 'subject' => 'PLH'],

            ['class' => '4', 'day' => 'monday', 'jam_ke' => 1, 'subject' => 'IPAS'],
            ['class' => '4', 'day' => 'monday', 'jam_ke' => 2, 'subject' => 'PJOK'],
            ['class' => '4', 'day' => 'tuesday', 'jam_ke' => 1, 'subject' => 'PLH'],
            ['class' => '4', 'day' => 'tuesday', 'jam_ke' => 2, 'subject' => 'Seni Teater'],
            ['class' => '4', 'day' => 'wednesday', 'jam_ke' => 1, 'subject' => 'PAI'],
            ['class' => '4', 'day' => 'wednesday', 'jam_ke' => 2, 'subject' => 'Matematika'],
            ['class' => '4', 'day' => 'thursday', 'jam_ke' => 1, 'subject' => 'PAI'],
            ['class' => '4', 'day' => 'thursday', 'jam_ke' => 2, 'subject' => 'Bahasa Inggris'],
            ['class' => '4', 'day' => 'friday', 'jam_ke' => 1, 'subject' => 'Bahasa Indonesia'],
            ['class' => '4', 'day' => 'friday', 'jam_ke' => 2, 'subject' => 'Pendidikan Pancasila'],
        ];

        $timeByJam = [
            1 => ['start' => '08:30:00', 'end' => '09:30:00'],
            2 => ['start' => '11:00:00', 'end' => '12:00:00'],
        ];

        $preferredByClassSubject = [
            '6B|Pendidikan Pancasila' => ['rohadatul-aisy@sdplusmelati.local'],
            '6B|Matematika' => ['nurlinda-tm@sdplusmelati.local', 'nurul-hana-hidayah@sdplusmelati.local'],
            '6B|IPAS' => ['tyas-dwi-fitriyanti@sdplusmelati.local'],
            '6B|PJOK' => ['nadia-anjelika@sdplusmelati.local'],
            '6B|Bahasa Indonesia' => ['humairah-h@sdplusmelati.local'],
            '6B|PAI' => ['dilla@sdplusmelati.local'],

            '6A|Matematika' => ['nurlinda-tm@sdplusmelati.local', 'nurul-hana-hidayah@sdplusmelati.local'],
            '6A|Seni Budaya' => ['risma-pebriana@sdplusmelati.local'],
            '6A|Bahasa Indonesia' => ['humairah-h@sdplusmelati.local'],
            '6A|PAI' => ['dilla@sdplusmelati.local'],
            '6A|Pendidikan Pancasila' => ['rohadatul-aisy@sdplusmelati.local'],
            '6A|PJOK' => ['nadia-anjelika@sdplusmelati.local'],
            '6A|IPAS' => ['tyas-dwi-fitriyanti@sdplusmelati.local'],

            '5|Bahasa Indonesia' => ['humairah-h@sdplusmelati.local'],
            '5|Bahasa Inggris' => ['risa-nur-sofitri@sdplusmelati.local'],
            '5|Matematika' => ['nurlinda-tm@sdplusmelati.local'],
            '5|IPAS' => ['risma-pebriana@sdplusmelati.local'],
            '5|PJOK' => ['nadia-anjelika@sdplusmelati.local'],
            '5|Pendidikan Pancasila' => ['humairah-h@sdplusmelati.local'],
            '5|PAI' => ['syaifuddin@sdplusmelati.local'],
            '5|PLH' => ['linda-herlianah@sdplusmelati.local'],

            '4|IPAS' => ['risma-pebriana@sdplusmelati.local'],
            '4|PJOK' => ['nadia-anjelika@sdplusmelati.local'],
            '4|PLH' => ['linda-herlianah@sdplusmelati.local'],
            '4|PAI' => ['dilla@sdplusmelati.local'],
            '4|Matematika' => ['nurlinda-tm@sdplusmelati.local', 'nurul-hana-hidayah@sdplusmelati.local'],
            '4|Bahasa Inggris' => ['risa-nur-sofitri@sdplusmelati.local'],
            '4|Bahasa Indonesia' => ['risa-nur-sofitri@sdplusmelati.local'],
            '4|Pendidikan Pancasila' => ['samsiah@sdplusmelati.local'],
        ];

        $insertRows = [];

        foreach ($rows as $row) {
            $class = $classMap->get($row['class']);
            if (! $class) {
                continue;
            }

            $subject = Subject::query()->firstWhere('name', $row['subject']);
            if (! $subject) {
                continue;
            }

            $assignments = Assignment::query()
                ->with('teacher.user')
                ->where('class_id', $class->id)
                ->where('subject_id', $subject->id)
                ->get();

            $preferred = $preferredByClassSubject[$row['class'].'|'.$row['subject']] ?? [];
            $assignment = $assignments
                ->sortBy(function (Assignment $assignment) use ($preferred) {
                    $email = $assignment->teacher->user->email ?? '';
                    $idx = array_search($email, $preferred, true);

                    return $idx === false ? 999 : $idx;
                })
                ->first();
            if (! $assignment) {
                continue;
            }

            $slot = $timeByJam[$row['jam_ke']] ?? ['start' => null, 'end' => null];
            $insertRows[] = [
                'schedule_profile_id' => $ramadhan->id,
                'teacher_id' => $assignment->teacher_id,
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'day_of_week' => $row['day'],
                'jam_ke' => $row['jam_ke'],
                'start_time' => $slot['start'],
                'end_time' => $slot['end'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($insertRows !== []) {
            TeachingSchedule::query()->insert($insertRows);
        }
    }
}




