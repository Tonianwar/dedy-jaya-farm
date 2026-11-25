<?php

namespace Database\Seeders;

use App\Models\Cow;
use App\Models\LivestockTag;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleFarmSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::setMany([
            'farm_name' => SiteSetting::get('farm_name', 'Dedy Jaya Farm'),
            'hero_eyebrow' => SiteSetting::get('hero_eyebrow', 'PETERNAKAN SAPI · BANYUMAS'),
            'hero_title' => SiteSetting::get('hero_title', 'Ternak terpilih. Dirawat dengan perhatian.'),
            'hero_description' => SiteSetting::get(
                'hero_description',
                'Kenali sapi pilihan Dedy Jaya Farm melalui profil dan riwayat perawatan yang tercatat.',
            ),
            'about_title' => SiteSetting::get('about_title', 'Peternakan sapi potong di Banyumas.'),
            'about_description' => SiteSetting::get(
                'about_description',
                'Kami merawat dan memantau sapi secara rutin. Setiap bobot, pemeriksaan, dan perawatan dicatat agar pelanggan mengenal kondisi ternak yang dipilih.',
            ),
            'address' => SiteSetting::get('address', 'Purbadana, Kembaran, Banyumas, Jawa Tengah'),
            'opening_hours' => SiteSetting::get('opening_hours', 'Senin–Sabtu, 08.00–16.00 WIB'),
        ]);

        if (Cow::query()->exists()) {
            return;
        }

        $cows = [
            ['DJF-1001', 'Bima', 'Simental', 'jantan', 21, 462, 34750000, 'Tenang saat ditangani, aktif dan memiliki pertumbuhan bobot yang konsisten.', 'images/cattle/pasture-01.svg'],
            ['DJF-1002', 'Sekar', 'Limousin', 'betina', 27, 419, 32600000, 'Kondisi tubuh terjaga dengan catatan vaksinasi rutin.', 'images/cattle/pasture-02.svg'],
            ['DJF-1003', 'Rangga', 'Peranakan Ongole', 'jantan', 18, 386, 29500000, 'Adaptif terhadap lingkungan kandang dan responsif terhadap pakan.', 'images/cattle/pasture-03.svg'],
            ['DJF-1004', 'Arjuna', 'Simental', 'jantan', 32, 508, 38900000, 'Postur kokoh dengan riwayat penimbangan berkala.', 'images/cattle/pasture-01.svg'],
            ['DJF-1005', 'Laras', 'Brahman', 'betina', 24, 398, 31250000, 'Temperamen jinak dan catatan kesehatan terpantau.', 'images/cattle/pasture-02.svg'],
            ['DJF-1006', 'Bagas', 'Limousin', 'jantan', 20, 443, 35800000, 'Perkembangan bobot baik, dirawat dalam pemantauan rutin.', 'images/cattle/pasture-03.svg'],
            ['DJF-1007', 'Jatmika', 'Peranakan Ongole', 'jantan', 29, 471, 33400000, 'Sapi lokal tangguh dengan riwayat perawatan terdokumentasi.', 'images/cattle/pasture-01.svg'],
            ['DJF-1008', 'Puspa', 'Simental', 'betina', 17, 374, 29900000, 'Kondisi sehat dan pertumbuhan terpantau sejak awal masuk.', 'images/cattle/pasture-02.svg'],
            ['DJF-1009', 'Wira', 'Brahman', 'jantan', 26, 486, 36900000, 'Sehat, aktif, dan terbiasa dengan pemeriksaan kandang.', 'images/cattle/pasture-03.svg'],
            ['DJF-1010', 'Citra', 'Limousin', 'betina', 22, 407, 31800000, 'Profil kesehatan terjaga dengan catatan penimbangan lengkap.', 'images/cattle/pasture-02.svg'],
        ];

        foreach ($cows as [$tagNumber, $name, $breed, $gender, $ageMonths, $weight, $price, $description, $photo]) {
            $arrivedAt = now()->subMonths(5)->subDays(random_int(1, 25))->toDateString();
            $cow = Cow::query()->create([
                'tag_number' => $tagNumber,
                'slug' => Str::slug($name.'-'.$tagNumber),
                'name' => $name,
                'breed' => $breed,
                'gender' => $gender,
                'birth_date' => now()->subMonths($ageMonths)->toDateString(),
                'arrived_at' => $arrivedAt,
                'price' => $price,
                'initial_weight_kg' => $weight - random_int(22, 47),
                'description' => $description,
                'photo_path' => $photo,
                'status' => 'available',
            ]);

            $qrTag = LivestockTag::query()->create([
                'code' => 'QR-'.$tagNumber,
                'token' => bin2hex(random_bytes(20)),
                'slug' => Str::slug($tagNumber),
                'cow_id' => $cow->id,
            ]);
            $qrTag->assignments()->create([
                'cow_id' => $cow->id,
                'assigned_at' => now(),
            ]);

            $cow->weightLogs()->create([
                'weighed_at' => $arrivedAt,
                'weight_kg' => $cow->initial_weight_kg,
                'note' => 'Berat awal saat sapi diterima.',
            ]);
            $cow->weightLogs()->create([
                'weighed_at' => now()->subDays(random_int(1, 19))->toDateString(),
                'weight_kg' => $weight,
                'note' => 'Pemeriksaan dan penimbangan rutin.',
            ]);

            if ($cow->id % 2 === 0) {
                $cow->healthRecords()->create([
                    'recorded_at' => now()->subDays(random_int(24, 90))->toDateString(),
                    'category' => 'vaccination',
                    'title' => 'Vaksinasi dan pemeriksaan rutin',
                    'description' => 'Pemeriksaan kondisi umum dan perawatan preventif oleh petugas kandang.',
                ]);
            }
        }
    }
}
