<?php

namespace Database\Seeders;

use App\Models\Cow;
use App\Models\LivestockTag;
use App\Models\SiteSetting;
use App\Services\LivestockTagService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
            'instagram_url' => SiteSetting::get('instagram_url', 'https://www.instagram.com/dedyjayafarm/'),
            'google_maps_url' => SiteSetting::get('google_maps_url', 'https://www.google.com/maps/place/Dedy+Jaya+Farm/data=!4m2!3m1!1s0x0:0xc51157099675347b?sa=X&ved=1t:2428&ictx=111'),
        ]);

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
            ['DJF-1011', 'Mahesa', 'Simental', 'jantan', 25, 493, 37200000, 'Postur seimbang dengan perkembangan bobot yang tercatat rutin.', 'images/cattle/pasture-01.svg'],
            ['DJF-1012', 'Kirana', 'Limousin', 'betina', 19, 391, 30900000, 'Kondisi tubuh terjaga dan terbiasa dengan pemeriksaan berkala.', 'images/cattle/pasture-02.svg'],
            ['DJF-1013', 'Guntur', 'Brahman', 'jantan', 30, 517, 38100000, 'Aktif, adaptif, dan memiliki catatan perawatan yang teratur.', 'images/cattle/pasture-03.svg'],
            ['DJF-1014', 'Melati', 'Peranakan Ongole', 'betina', 23, 405, 30400000, 'Temperamen tenang dengan pemantauan kesehatan rutin.', 'images/cattle/pasture-02.svg'],
            ['DJF-1015', 'Pradana', 'Limousin', 'jantan', 28, 488, 36500000, 'Pertumbuhan terpantau melalui penimbangan dan perawatan berkala.', 'images/cattle/pasture-01.svg'],
            ['DJF-1016', 'Nirmala', 'Simental', 'betina', 20, 382, 31500000, 'Kondisi sehat dan riwayat vaksinasi terdokumentasi.', 'images/cattle/pasture-03.svg'],
            ['DJF-1017', 'Wibowo', 'Peranakan Ongole', 'jantan', 34, 526, 35900000, 'Sapi lokal tangguh dengan kondisi tubuh terjaga.', 'images/cattle/pasture-01.svg'],
            ['DJF-1018', 'Pertiwi', 'Brahman', 'betina', 26, 431, 32700000, 'Dirawat dengan pemantauan rutin dan catatan bobot berkala.', 'images/cattle/pasture-02.svg'],
            ['DJF-1019', 'Satria', 'Simental', 'jantan', 22, 457, 35100000, 'Aktif dan memiliki pertumbuhan yang dipantau secara berkala.', 'images/cattle/pasture-03.svg'],
            ['DJF-1020', 'Dewi', 'Limousin', 'betina', 31, 448, 33900000, 'Kondisi terawat dengan informasi kesehatan yang tercatat.', 'images/cattle/pasture-01.svg'],
            ['DJF-1021', 'Raksaka', 'Brahman', 'jantan', 27, 502, 37400000, 'Postur kokoh dengan riwayat penimbangan yang konsisten.', 'images/cattle/pasture-02.svg'],
            ['DJF-1022', 'Anjani', 'Peranakan Ongole', 'betina', 18, 369, 29200000, 'Adaptif dan dipantau melalui pemeriksaan rutin di kandang.', 'images/cattle/pasture-03.svg'],
            ['DJF-1023', 'Yudhistira', 'Limousin', 'jantan', 35, 538, 39800000, 'Bobot dan kondisi kesehatan tercatat pada setiap pemeriksaan.', 'images/cattle/pasture-01.svg'],
            ['DJF-1024', 'Saraswati', 'Simental', 'betina', 25, 416, 32900000, 'Temperamen jinak dengan perawatan dan vaksinasi berkala.', 'images/cattle/pasture-02.svg'],
            ['DJF-1025', 'Wiratama', 'Peranakan Ongole', 'jantan', 24, 439, 32100000, 'Sapi lokal sehat dengan perkembangan bobot terpantau.', 'images/cattle/pasture-03.svg'],
            ['DJF-1026', 'Amara', 'Brahman', 'betina', 29, 452, 34100000, 'Kondisi tubuh terjaga dan catatan perawatan tersedia.', 'images/cattle/pasture-01.svg'],
            ['DJF-1027', 'Cakra', 'Simental', 'jantan', 19, 421, 33600000, 'Aktif dan menjalani penimbangan rutin sejak tiba di peternakan.', 'images/cattle/pasture-02.svg'],
            ['DJF-1028', 'Lestari', 'Limousin', 'betina', 33, 477, 35800000, 'Riwayat kesehatan terpantau dan perawatan dilakukan konsisten.', 'images/cattle/pasture-03.svg'],
            ['DJF-1029', 'Damar', 'Peranakan Ongole', 'jantan', 21, 397, 29800000, 'Adaptif terhadap lingkungan kandang dan aktif saat pemeriksaan.', 'images/cattle/pasture-01.svg'],
            ['DJF-1030', 'Puspaningrum', 'Simental', 'betina', 28, 463, 35200000, 'Pertumbuhan dan kondisi kesehatannya tercatat dengan baik.', 'images/cattle/pasture-02.svg'],
            ['DJF-1031', 'Adikara', 'Brahman', 'jantan', 32, 531, 38900000, 'Sehat, aktif, dan memiliki catatan pemeriksaan berkala.', 'images/cattle/pasture-03.svg'],
            ['DJF-1032', 'Ratih', 'Limousin', 'betina', 17, 358, 28600000, 'Kondisi terawat dengan perkembangan bobot yang dipantau.', 'images/cattle/pasture-01.svg'],
            ['DJF-1033', 'Danendra', 'Peranakan Ongole', 'jantan', 26, 481, 34600000, 'Sapi lokal tangguh dengan riwayat perawatan rutin.', 'images/cattle/pasture-02.svg'],
            ['DJF-1034', 'Sekar Arum', 'Simental', 'betina', 22, 402, 31800000, 'Temperamen tenang dengan catatan kesehatan yang terjaga.', 'images/cattle/pasture-03.svg'],
            ['DJF-1035', 'Baskara', 'Limousin', 'jantan', 29, 509, 38200000, 'Pertumbuhan bobot tercatat dan kondisi tubuh terpantau.', 'images/cattle/pasture-01.svg'],
            ['DJF-1036', 'Nadira', 'Brahman', 'betina', 24, 424, 32500000, 'Dirawat rutin dan menjalani pemeriksaan kesehatan berkala.', 'images/cattle/pasture-02.svg'],
            ['DJF-1037', 'Rangga Wijaya', 'Simental', 'jantan', 31, 522, 39100000, 'Postur kuat dan memiliki riwayat penimbangan teratur.', 'images/cattle/pasture-03.svg'],
            ['DJF-1038', 'Kusuma', 'Peranakan Ongole', 'betina', 20, 376, 29400000, 'Kondisi sehat dan adaptif dengan lingkungan peternakan.', 'images/cattle/pasture-01.svg'],
            ['DJF-1039', 'Aryasatya', 'Brahman', 'jantan', 23, 468, 35400000, 'Aktif dengan catatan pertumbuhan dan perawatan yang rutin.', 'images/cattle/pasture-02.svg'],
            ['DJF-1040', 'Cempaka', 'Limousin', 'betina', 27, 439, 33700000, 'Kondisi tubuh terjaga melalui pemantauan dan perawatan berkala.', 'images/cattle/pasture-03.svg'],
        ];

        foreach ($cows as [$tagNumber, $name, $breed, $gender, $ageMonths, $weight, $price, $description, $photo]) {
            $qrCode = 'QR-'.$tagNumber;

            if (Cow::query()->where('tag_number', $tagNumber)->exists()) {
                continue;
            }

            $arrivedAt = now()->subMonths(5)->subDays(random_int(1, 25))->toDateString();
            DB::transaction(function () use ($tagNumber, $name, $breed, $gender, $ageMonths, $weight, $price, $description, $photo, $arrivedAt, $qrCode): void {
                $qrTag = LivestockTag::query()
                    ->where('code', $qrCode)
                    ->lockForUpdate()
                    ->first();

                if ($qrTag?->cow_id) {
                    return;
                }

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

                $qrTag ??= LivestockTag::query()->create([
                    'code' => $qrCode,
                    'token' => bin2hex(random_bytes(20)),
                    'slug' => Str::slug($tagNumber),
                ]);
                app(LivestockTagService::class)->assign($qrTag, $cow);

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
            });
        }
    }
}
