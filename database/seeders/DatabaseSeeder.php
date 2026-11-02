<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Appointment;
use App\Models\CovidTest;
use App\Models\Hospital;
use App\Models\News;
use App\Models\Patient;
use App\Models\Vaccination;
use App\Models\Vaccine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // =========================================================================
        // =========================================================================
        // CREATE ADMIN
        // =========================================================================
        Admin::create([
            'username' => 'admin',
            'email' => 'admin@health.pk',
            'password' => Hash::make('admin123'),
        ]);

        $this->command->info('Admin created: username=admin, password=admin123');

        // =========================================================================
        // CREATE VACCINES
        // =========================================================================
        $vaccines = [
            [
                'name' => 'Sinopharm',
                'manufacturer' => 'China National Pharmaceutical Group',
                'doses_required' => 2,
                'available' => true,
            ],
            [
                'name' => 'Pfizer-BioNTech',
                'manufacturer' => 'Pfizer Inc.',
                'doses_required' => 2,
                'available' => true,
            ],
            [
                'name' => 'CanSino',
                'manufacturer' => 'CanSino Biologics',
                'doses_required' => 1,
                'available' => true,
            ],
            [
                'name' => 'AstraZeneca',
                'manufacturer' => 'Oxford University',
                'doses_required' => 2,
                'available' => true,
            ],
        ];

        foreach ($vaccines as $vaccine) {
            Vaccine::create($vaccine);
        }

        $this->command->info('Vaccines seeded: 4 vaccines created');

        // =========================================================================
        // CREATE HOSPITALS
        // =========================================================================
        $hospitals = [
            [
                'name' => 'Aga Khan University Hospital',
                'email' => 'akuh@hospital.pk',
                'password' => 'hospital123',
                'phone' => '021-34861000',
                'address' => 'Stadium Road',
                'city' => 'Karachi',
                'status' => 'Approved',
            ],
            [
                'name' => 'Shifa International Hospital',
                'email' => 'shifa@hospital.pk',
                'password' => 'hospital123',
                'phone' => '051-8463000',
                'address' => 'H-8/4',
                'city' => 'Islamabad',
                'status' => 'Approved',
            ],
            [
                'name' => 'Shaukat Khanum Memorial Hospital',
                'email' => 'skmch@hospital.pk',
                'password' => 'hospital123',
                'phone' => '042-35905000',
                'address' => '7A Block R-3, Johar Town',
                'city' => 'Lahore',
                'status' => 'Approved',
            ],
            [
                'name' => 'Jinnah Postgraduate Medical Centre',
                'email' => 'jpmc@hospital.pk',
                'password' => 'hospital123',
                'phone' => '021-99201300',
                'address' => 'Rafiqui Shaheed Road',
                'city' => 'Karachi',
                'status' => 'Pending',
            ],
            [
                'name' => 'Lady Reading Hospital',
                'email' => 'lrh@hospital.pk',
                'password' => 'hospital123',
                'phone' => '091-9211430',
                'address' => 'Soekarno Road',
                'city' => 'Peshawar',
                'status' => 'Pending',
            ],
        ];

        foreach ($hospitals as $hospital) {
            Hospital::create($hospital);
        }

        $this->command->info('Hospitals seeded: 5 hospitals created (password=hospital123)');

        // =========================================================================
        // CREATE PATIENTS
        // =========================================================================
        $patients = [
            [
                'name' => 'Muhammad Ali',
                'email' => 'ali@user.pk',
                'password' => 'user123',
                'mobile' => '03001234567',
                'dob' => '1990-05-15',
                'gender' => 'Male',
                'address' => 'House 123, Street 5, F-10',
                'city' => 'Islamabad',
            ],
            [
                'name' => 'Fatima Ahmed',
                'email' => 'fatima@user.pk',
                'password' => 'user123',
                'mobile' => '03217654321',
                'dob' => '1995-08-22',
                'gender' => 'Female',
                'address' => 'Flat 402, Clifton Gardens',
                'city' => 'Karachi',
            ],
            [
                'name' => 'Zain Malik',
                'email' => 'zain@user.pk',
                'password' => 'user123',
                'mobile' => '03335555555',
                'dob' => '1988-03-10',
                'gender' => 'Male',
                'address' => '14-B, Gulberg III',
                'city' => 'Lahore',
            ],
        ];

        foreach ($patients as $patient) {
            Patient::create($patient);
        }

        $this->command->info('Patients seeded: 3 patients created (password=user123)');

        // =========================================================================
        // CREATE APPOINTMENTS
        // =========================================================================
        $appointments = [
            [
                'patient_id' => 1,
                'hospital_id' => 1,
                'purpose' => 'Covid Test',
                'date' => '2024-02-15',
                'time' => '10:00',
                'status' => 'Approved',
            ],
            [
                'patient_id' => 1,
                'hospital_id' => 1,
                'purpose' => 'Vaccination',
                'date' => '2024-02-20',
                'time' => '11:00',
                'status' => 'Approved',
            ],
            [
                'patient_id' => 2,
                'hospital_id' => 2,
                'purpose' => 'Vaccination',
                'date' => '2024-02-18',
                'time' => '14:00',
                'status' => 'Pending',
            ],
            [
                'patient_id' => 3,
                'hospital_id' => 3,
                'purpose' => 'Covid Test',
                'date' => '2024-02-22',
                'time' => '09:00',
                'status' => 'Rejected',
            ],
        ];

        foreach ($appointments as $appointment) {
            Appointment::create($appointment);
        }

        $this->command->info('Appointments seeded: 4 appointments created');

        // =========================================================================
        // CREATE COVID TESTS
        // =========================================================================
        CovidTest::create([
            'appointment_id' => 1,
            'patient_id' => 1,
            'hospital_id' => 1,
            'result' => 'Negative',
            'test_date' => '2024-02-15',
            'remarks' => 'RT-PCR test conducted. Results negative.',
        ]);

        $this->command->info('Covid tests seeded: 1 test created');

        // =========================================================================
        // CREATE VACCINATIONS
        // =========================================================================
        Vaccination::create([
            'appointment_id' => 2,
            'patient_id' => 1,
            'hospital_id' => 1,
            'vaccine_id' => 1,
            'dose_number' => 1,
            'status' => 'Completed',
            'vaccination_date' => '2024-02-20',
        ]);

        $this->command->info('Vaccinations seeded: 1 vaccination created');

        // =========================================================================
        // CREATE NEWS ARTICLES
        // =========================================================================
        $newsArticles = [
            [
                'title' => 'New COVID-19 Variant: What You Need to Know',
                'excerpt' => 'Health experts are monitoring a new variant that has emerged. Here is everything you need to know about symptoms and prevention.',
                'content' => '<p>Health experts around the world are closely monitoring a new COVID-19 variant that has recently emerged. This article provides comprehensive information about the new variant, including symptoms to watch for, prevention strategies, and what you should do if you suspect you may be infected.</p><h2>Key Symptoms</h2><p>The new variant may present with symptoms similar to previous strains, including fever, cough, fatigue, and loss of taste or smell. However, some patients have reported additional symptoms such as sore throat and nasal congestion.</p><h2>Prevention Tips</h2><ul><li>Continue wearing masks in crowded areas</li><li>Practice regular hand washing</li><li>Stay up to date with vaccinations</li><li>Maintain social distancing when possible</li></ul>',
                'image' => 'https://images.unsplash.com/photo-1584483766114-2cea6facdf57?w=400',
                'category' => 'Health Alert',
                'published' => true,
            ],
            [
                'title' => 'Vaccination Drive Reaches 1 Million Milestone',
                'excerpt' => 'Our vaccination program has successfully administered over 1 million doses, marking a significant achievement.',
                'content' => '<p>We are thrilled to announce that our vaccination program has reached a historic milestone - over 1 million doses have been successfully administered across the country.</p><h2>A Collective Achievement</h2><p>This achievement would not have been possible without the dedication of our healthcare workers, the support of our partner hospitals, and the trust of millions of citizens who chose to get vaccinated.</p><h2>Looking Forward</h2><p>We remain committed to making vaccines accessible to all citizens. Our next goal is to ensure that every eligible person has access to booster shots as recommended by health authorities.</p>',
                'image' => 'https://images.unsplash.com/photo-1615631648086-325025c9e51e?w=400',
                'category' => 'Achievement',
                'published' => true,
            ],
            [
                'title' => 'Understanding Booster Shots: Complete Guide',
                'excerpt' => 'Everything you need to know about COVID-19 booster shots, eligibility, and benefits for enhanced protection.',
                'content' => '<p>Booster shots have become an essential part of our ongoing fight against COVID-19. This comprehensive guide will help you understand what booster shots are, who is eligible, and why they are important.</p><h2>What Are Booster Shots?</h2><p>Booster shots are additional doses of the COVID-19 vaccine given after the initial vaccination series. They help restore the protection that may have decreased over time.</p><h2>Who Should Get a Booster?</h2><ul><li>Adults aged 18 and older</li><li>Those who completed their initial vaccination more than 6 months ago</li><li>Immunocompromised individuals (as recommended by their doctor)</li></ul><h2>Benefits of Booster Shots</h2><p>Studies show that booster shots significantly increase protection against severe illness, hospitalization, and death from COVID-19, including newer variants.</p>',
                'image' => 'https://images.unsplash.com/photo-1609601546183-5d8dd0dd8848?w=400',
                'category' => 'Guide',
                'published' => true,
            ],
        ];

        foreach ($newsArticles as $article) {
            News::create($article);
        }

        $this->command->info('News seeded: 3 articles created');

        $this->command->info('');
        $this->command->info('=== Demo Credentials ===');
        $this->command->info('Patient: rahul@demo.com / demo123');
        $this->command->info('Hospital: city@hospital.com / demo123');
        $this->command->info('Admin: admin / admin123');
    }
}
