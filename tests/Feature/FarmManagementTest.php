<?php

namespace Tests\Feature;

use App\Models\Cow;
use App\Models\HealthRecord;
use App\Models\LivestockTag;
use App\Models\LivestockTagAssignment;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\WeightLog;
use App\Support\CowPublicUrl;
use Database\Seeders\DemoAdminSeeder;
use Database\Seeders\SampleFarmSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_only_shows_cows_available_for_sale(): void
    {
        $this->createCow(['name' => 'Sapi Tersedia']);
        $this->createCow(['name' => 'Sapi Terjual', 'status' => 'sold']);

        $this->get(route('catalog'))
            ->assertOk()
            ->assertSee('Sapi Tersedia')
            ->assertDontSee('Sapi Terjual');
    }

    public function test_public_catalog_uses_database_pagination_and_keeps_filters_on_next_page(): void
    {
        foreach (range(1, 25) as $number) {
            $this->createCow([
                'name' => 'Katalog '.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'breed' => 'Limousin',
            ]);
        }

        $this->get(route('catalog', ['breed' => 'Limousin']))
            ->assertOk()
            ->assertSee('Katalog 25')
            ->assertSee('Katalog 17')
            ->assertDontSee('Katalog 16')
            ->assertSee('Menampilkan 1–9 dari 25 sapi tersedia')
            ->assertSee('page=2', false)
            ->assertSee('page=3', false)
            ->assertSee('breed=Limousin', false);

        $this->get(route('catalog', ['breed' => 'Limousin', 'page' => 2]))
            ->assertOk()
            ->assertSee('Katalog 16')
            ->assertSee('Katalog 08')
            ->assertDontSee('Katalog 07')
            ->assertSee('Menampilkan 10–18 dari 25 sapi tersedia');

        $this->get(route('catalog', ['breed' => 'Limousin', 'per_page' => 6, 'page' => 2]))
            ->assertOk()
            ->assertSee('Katalog 19')
            ->assertSee('Katalog 14')
            ->assertDontSee('Katalog 13')
            ->assertSee('Menampilkan 7–12 dari 25 sapi tersedia')
            ->assertSee('per_page=6', false);
    }

    public function test_admin_cow_list_paginates_database_results_and_supports_larger_pages(): void
    {
        foreach (range(1, 32) as $number) {
            $this->createCow([
                'name' => 'Inventaris '.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
            ]);
        }

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.cows.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Inventaris 17')
            ->assertDontSee('Inventaris 32')
            ->assertSee('Menampilkan 16–30 dari 32 data sapi')
            ->assertSee('name="per_page"', false);

        $this->get(route('admin.cows.index', ['page' => 2, 'per_page' => 30]))
            ->assertOk()
            ->assertSee('Inventaris 02')
            ->assertDontSee('Inventaris 32')
            ->assertSee('Menampilkan 31–32 dari 32 data sapi');
    }

    public function test_public_catalog_uses_the_assigned_qr_short_slug(): void
    {
        $cow = $this->createCow(['name' => 'Sapi QR']);
        $tag = $this->createTag($cow);

        $this->get(route('catalog'))
            ->assertOk()
            ->assertSee('href="'.route('cows.show', $tag->slug).'"', false);
    }

    public function test_public_catalog_filters_by_breed_and_current_weight_range(): void
    {
        $matchingCow = $this->createCow([
            'name' => 'Bobot Sesuai',
            'breed' => 'Limousin',
            'initial_weight_kg' => 300,
        ]);
        $matchingCow->weightLogs()->create([
            'weighed_at' => '2026-11-10',
            'weight_kg' => 425.5,
        ]);

        $outOfRangeCow = $this->createCow([
            'name' => 'Bobot Di Luar',
            'breed' => 'Limousin',
            'initial_weight_kg' => 500,
        ]);
        $outOfRangeCow->weightLogs()->create([
            'weighed_at' => '2026-11-10',
            'weight_kg' => 260.5,
        ]);

        $this->createCow(['name' => 'Jenis Berbeda', 'breed' => 'Simental', 'initial_weight_kg' => 425]);

        $this->get(route('catalog', [
            'breed' => 'Limousin',
            'weight_min' => 400.5,
            'weight_max' => 450.5,
        ]))
            ->assertOk()
            ->assertSee('Bobot Sesuai')
            ->assertDontSee('Bobot Di Luar')
            ->assertDontSee('Jenis Berbeda')
            ->assertSee('name="weight_min"', false)
            ->assertSee('value="400.5"', false);
    }

    public function test_public_catalog_sorts_cows_by_price_and_places_unpriced_cows_last(): void
    {
        $this->createCow(['name' => 'Harga Murah', 'price' => 10000000]);
        $this->createCow(['name' => 'Harga Mahal', 'price' => 40000000]);
        $this->createCow(['name' => 'Harga Hubungi', 'price' => null]);

        $this->get(route('catalog', ['price_sort' => 'price_asc']))
            ->assertOk()
            ->assertSeeInOrder(['Harga Murah', 'Harga Mahal', 'Harga Hubungi']);

        $this->get(route('catalog', ['price_sort' => 'price_desc']))
            ->assertOk()
            ->assertSeeInOrder(['Harga Mahal', 'Harga Murah', 'Harga Hubungi']);
    }

    public function test_public_profile_shows_history_and_embeds_valid_youtube_video(): void
    {
        $cow = $this->createCow();
        $cow->healthRecords()->create([
            'recorded_at' => '2026-10-10',
            'category' => 'vaccination',
            'title' => 'Vaksinasi',
            'description' => 'Vaksin rutin.',
            'youtube_url' => 'https://youtu.be/abcdefghijk',
        ]);
        $cow->weightLogs()->create([
            'weighed_at' => '2026-10-12',
            'weight_kg' => 415.5,
            'note' => 'Penimbangan bulanan.',
        ]);

        $this->get(route('cows.show', $cow->slug))
            ->assertOk()
            ->assertSee('Vaksinasi')
            ->assertSee('415,5 kg')
            ->assertSee('Grafik riwayat bobot', false)
            ->assertSee('weight-chart-svg', false)
            ->assertSee('https://www.youtube-nocookie.com/embed/abcdefghijk', false);
    }

    public function test_public_weight_chart_plots_records_in_chronological_order(): void
    {
        $cow = $this->createCow(['name' => 'Sapi Bergrafik']);
        $cow->weightLogs()->createMany([
            ['weighed_at' => '2026-10-12', 'weight_kg' => 415.5],
            ['weighed_at' => '2026-08-12', 'weight_kg' => 390],
            ['weighed_at' => '2026-09-12', 'weight_kg' => 402.5],
        ]);

        $this->get(route('cows.show', $cow->slug))
            ->assertOk()
            ->assertSeeInOrder([
                '12/08/2026: 390,0 kg',
                '12/09/2026: 402,5 kg',
                '12/10/2026: 415,5 kg',
            ]);
    }

    public function test_public_profile_displays_age_in_readable_years_and_months(): void
    {
        $cow = $this->createCow([
            'birth_date' => now()->subMonths(17)->toDateString(),
        ]);

        $this->get(route('cows.show', $cow->slug))
            ->assertOk()
            ->assertSee('1 tahun 5 bulan');
    }

    public function test_admin_routes_and_qr_generation_require_an_admin_login(): void
    {
        $cow = $this->createCow();

        $tag = $this->createTag($cow);

        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.tags.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.tags.qr.svg', $tag->code))->assertRedirect(route('admin.login'));
        $this->get(route('admin.tags.qr.png', $tag->code))->assertRedirect(route('admin.login'));
    }

    public function test_only_admins_can_open_the_management_dashboard(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_sign_in_to_the_management_panel(): void
    {
        $admin = User::factory()->create([
            'email' => 'operator@dedijayafarm.test',
            'password' => 'password',
            'is_admin' => true,
        ]);

        $this->post(route('admin.login'), [
            'email' => 'operator@dedijayafarm.test',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_local_database_seeder_creates_a_demo_admin_that_can_sign_in(): void
    {
        config(['app.env' => 'local']);

        $this->seed(DemoAdminSeeder::class);
        $this->seed(DemoAdminSeeder::class);

        $this->assertDatabaseHas('users', [
            'name' => 'Admin Demo',
            'email' => 'admin@dedijayafarm.test',
            'is_admin' => true,
        ]);
        $this->assertSame(1, User::query()->where('email', 'admin@dedijayafarm.test')->count());

        $this->post(route('admin.login'), [
            'email' => 'admin@dedijayafarm.test',
            'password' => 'DedyJayaDemo!2026',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_database_seeder_does_not_create_demo_admin_outside_local_environment(): void
    {
        config(['app.env' => 'production']);

        $this->seed(DemoAdminSeeder::class);

        $this->assertDatabaseMissing('users', ['email' => 'admin@dedijayafarm.test']);
    }

    public function test_admin_can_download_svg_and_png_qr_codes(): void
    {
        $cow = $this->createCow();
        $tag = $this->createTag($cow);
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->get(route('admin.tags.qr.svg', $tag->code))
            ->assertOk()
            ->assertHeader('content-type', 'image/svg+xml; charset=UTF-8')
            ->assertHeader('content-disposition', 'attachment; filename="'.$tag->code.'.svg"')
            ->assertSee('<svg', false);

        $png = $this->get(route('admin.tags.qr.png', $tag->code));
        $png->assertOk()
            ->assertHeader('content-type', 'image/png')
            ->assertHeader('content-disposition', 'attachment; filename="'.$tag->code.'.png"');
        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", $png->getContent());
    }

    public function test_admin_can_generate_a_short_qr_without_a_cow_and_assign_it_later(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)
            ->post(route('admin.tags.store'), ['code' => 'DJF-QR-NEW01'])
            ->assertRedirect(route('admin.tags.index'));

        $tag = LivestockTag::query()->where('code', 'DJF-QR-NEW01')->firstOrFail();
        $this->assertSame('djf-qr-new01', $tag->slug);
        $this->assertSame(config('app.url').'/sapi/djf-qr-new01', CowPublicUrl::forTag($tag));
        $this->get(route('cows.show', $tag->slug))
            ->assertOk()
            ->assertSee('Tag sedang disiapkan');

        $cow = $this->createCow(['name' => 'Sapi QR Baru']);
        $this->actingAs($admin)
            ->post(route('admin.cows.tag.assign', $cow), ['tag_id' => $tag->id])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->get(route('cows.show', $tag->slug))
            ->assertOk()
            ->assertSee('Sapi QR Baru');

        $this->get(route('admin.tags.index'))
            ->assertOk()
            ->assertSee('/sapi/djf-qr-new01');
    }

    public function test_qr_url_uses_domain_saved_in_database(): void
    {
        $cow = $this->createCow();
        $tag = $this->createTag($cow);
        SiteSetting::setMany(['public_url' => 'https://dedijayafarm.id']);

        $this->assertSame(
            'https://dedijayafarm.id/sapi/'.$tag->slug,
            CowPublicUrl::forTag($tag),
        );
    }

    public function test_sold_cow_remains_reachable_with_history_but_is_removed_from_catalog(): void
    {
        $cow = $this->createCow(['name' => 'Sapi Purna', 'status' => 'sold']);
        $cow->weightLogs()->create([
            'weighed_at' => '2026-09-10',
            'weight_kg' => 500,
        ]);

        $this->get(route('catalog'))->assertDontSee('Sapi Purna');
        $this->get(route('cows.show', $cow->slug))
            ->assertOk()
            ->assertSee('Sudah terjual')
            ->assertSee('500,0 kg')
            ->assertSee('Sapi Purna');

        $this->assertDatabaseHas('weight_logs', ['cow_id' => $cow->id, 'weight_kg' => 500]);
    }

    public function test_admin_can_create_a_new_cow_and_capture_initial_weight(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->post(route('admin.cows.store'), [
                'tag_number' => 'DJF-0009',
                'name' => 'Manggala',
                'breed' => 'Simental',
                'gender' => 'jantan',
                'arrived_at' => '2026-11-01',
                'initial_weight_kg' => '381.5',
                'price' => '28000000',
            ])
            ->assertRedirect();

        $cow = Cow::query()->where('tag_number', 'DJF-0009')->with('currentTag')->firstOrFail();
        $this->assertNull($cow->currentTag);
        $this->assertTrue(WeightLog::query()
            ->where('cow_id', $cow->id)
            ->whereDate('weighed_at', '2026-11-01')
            ->where('weight_kg', 381.5)
            ->exists());
    }

    public function test_admin_can_mark_a_cow_sold_without_deleting_its_record(): void
    {
        $cow = $this->createCow();
        $tag = $this->createTag($cow);
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->patch(route('admin.cows.sold', $cow))
            ->assertRedirect(route('admin.cows.show', $cow));

        $this->assertDatabaseHas('cows', ['id' => $cow->id, 'status' => 'sold']);
        $this->assertNotNull($cow->fresh()->sold_at);
        $this->assertSame($tag->slug.'-sold1', $cow->fresh()->slug);
        $this->assertDatabaseHas('livestock_tags', ['id' => $tag->id, 'cow_id' => null]);
        $this->assertNotNull(LivestockTagAssignment::query()
            ->where('livestock_tag_id', $tag->id)
            ->firstOrFail()
            ->released_at);
    }

    public function test_non_youtube_video_links_are_rejected(): void
    {
        $cow = $this->createCow();

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->post(route('admin.cows.health-records.store', $cow), [
                'recorded_at' => '2026-11-01',
                'category' => 'care',
                'title' => 'Perawatan',
                'youtube_url' => 'https://example.com/video.mp4',
            ])
            ->assertSessionHasErrors('youtube_url');

        $this->assertSame(0, HealthRecord::query()->count());
    }

    public function test_admin_settings_update_brand_domain_and_whatsapp(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->put(route('admin.settings.update'), [
                'farm_name' => 'Dedy Jaya Farm',
                'public_url' => 'https://dedijayafarm.id/',
                'whatsapp' => '6281234567890',
                'hero_eyebrow' => 'TERNAK BANYUMAS',
                'hero_title' => 'Dari kandang kami.',
                'hero_description' => 'Sapi dengan rekam jejak.',
                'home_show_hero' => '1',
                'home_show_traceability' => '0',
                'home_show_featured' => '1',
                'home_show_care_story' => '0',
                'home_show_contact' => '1',
                'address' => 'Banyumas',
                'contact_phone' => '08123456789',
                'contact_email' => 'halo@dedijaya.test',
                'opening_hours' => 'Senin sampai Sabtu',
                'instagram_url' => 'https://www.instagram.com/dedyjayafarm/',
                'google_maps_url' => 'https://www.google.com/maps/place/Dedy+Jaya+Farm/',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('site_settings', ['key' => 'farm_name', 'value' => 'Dedy Jaya Farm']);
        $this->assertDatabaseHas('site_settings', ['key' => 'public_url', 'value' => 'https://dedijayafarm.id']);
        $this->assertDatabaseHas('site_settings', ['key' => 'whatsapp', 'value' => '6281234567890']);
        $this->assertDatabaseHas('site_settings', ['key' => 'instagram_url', 'value' => 'https://www.instagram.com/dedyjayafarm/']);
        $this->assertDatabaseHas('site_settings', ['key' => 'google_maps_url', 'value' => 'https://www.google.com/maps/place/Dedy+Jaya+Farm/']);
        $this->assertDatabaseHas('site_settings', ['key' => 'home_show_traceability', 'value' => '0']);
        $this->assertDatabaseHas('site_settings', ['key' => 'home_show_contact', 'value' => '1']);
    }

    public function test_invalid_social_links_are_rejected(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->put(route('admin.settings.update'), [
                'farm_name' => 'Dedy Jaya Farm',
                'public_url' => 'https://dedijayafarm.id',
                'whatsapp' => '',
                'hero_eyebrow' => 'TERNAK BANYUMAS',
                'hero_title' => 'Dari kandang kami.',
                'hero_description' => 'Sapi dengan rekam jejak.',
                'address' => 'Banyumas',
                'instagram_url' => 'https://example.com/fake-instagram',
                'google_maps_url' => 'https://example.com/fake-map',
            ])
            ->assertSessionHasErrors(['instagram_url', 'google_maps_url']);
    }

    public function test_whatsapp_float_is_shown_when_number_is_configured(): void
    {
        SiteSetting::setMany(['whatsapp' => '6281234567890']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('class="whatsapp-float"', false)
            ->assertSee('https://wa.me/6281234567890', false);
    }

    public function test_domain_setting_rejects_paths_and_query_parameters(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->put(route('admin.settings.update'), [
                'farm_name' => 'Dedy Jaya Farm',
                'public_url' => 'https://dedijayafarm.id/catalog?source=tag',
                'whatsapp' => '',
            ])
            ->assertSessionHasErrors('public_url');
    }

    public function test_reusable_qr_uses_short_slug_and_archives_sold_cow_with_incremented_slug(): void
    {
        $oldCow = $this->createCow(['name' => 'Sapi Pertama']);
        $tag = $this->createTag($oldCow);
        $newCow = $this->createCow(['name' => 'Sapi Kedua']);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->patch(route('admin.cows.sold', $oldCow))
            ->assertRedirect();

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->post(route('admin.cows.tag.assign', $newCow), ['tag_id' => $tag->id])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->get(route('tags.show', $tag->token))
            ->assertRedirect(route('cows.show', $tag->slug));

        $this->get(route('cows.show', $tag->slug))
            ->assertOk()
            ->assertSee('Sapi Kedua');

        $archivedCow = $oldCow->fresh();
        $this->assertSame($tag->slug.'-sold1', $archivedCow->slug);
        $this->get(route('cows.show', $archivedCow->slug))
            ->assertOk()
            ->assertSee('Sudah terjual')
            ->assertSee('Sapi Pertama');

        $this->assertDatabaseHas('livestock_tags', [
            'id' => $tag->id,
            'cow_id' => $newCow->id,
        ]);
        $this->assertSame(2, LivestockTagAssignment::query()->where('livestock_tag_id', $tag->id)->count());

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->patch(route('admin.cows.sold', $newCow))
            ->assertRedirect();

        $this->assertSame($tag->slug.'-sold2', $newCow->fresh()->slug);
        $this->get(route('cows.show', $tag->slug))
            ->assertOk()
            ->assertSee('Tag sedang disiapkan');
    }

    public function test_admin_can_reuse_a_released_qr_when_registering_the_next_cow(): void
    {
        $oldCow = $this->createCow(['name' => 'Sapi Sebelumnya']);
        $tag = $this->createTag($oldCow);
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->patch(route('admin.cows.sold', $oldCow));

        $this->actingAs($admin)
            ->post(route('admin.cows.store'), [
                'tag_number' => 'DJF-BARU-1',
                'name' => 'Sapi Pengganti',
                'breed' => 'Simental',
                'gender' => 'jantan',
                'arrived_at' => '2026-11-01',
                'livestock_tag_id' => $tag->id,
            ])
            ->assertRedirect();

        $newCow = Cow::query()->where('tag_number', 'DJF-BARU-1')->firstOrFail();
        $this->assertDatabaseHas('livestock_tags', ['id' => $tag->id, 'cow_id' => $newCow->id]);
        $this->get(route('tags.show', $tag->token))
            ->assertRedirect(route('cows.show', $tag->slug));
        $this->get(route('cows.show', $tag->slug))->assertOk()->assertSee('Sapi Pengganti');
        $this->assertSame($tag->slug.'-sold1', $oldCow->fresh()->slug);
    }

    public function test_unassigned_qr_displays_a_reusable_tag_message(): void
    {
        $tag = LivestockTag::query()->create([
            'code' => 'QR-AVAILABLE',
            'token' => str_repeat('a', 40),
            'slug' => 'qr-available',
        ]);

        $this->get(route('tags.show', $tag->token))
            ->assertRedirect(route('cows.show', $tag->slug));

        $this->get(route('cows.show', $tag->slug))
            ->assertOk()
            ->assertSee('Tag sedang disiapkan')
            ->assertSee($tag->code);
    }

    public function test_home_page_has_dynamic_profile_and_contact_and_hides_admin_login(): void
    {
        SiteSetting::setMany([
            'farm_name' => 'Peternakan Uji',
            'hero_title' => 'Sapi pilihan Banyumas',
            'address' => 'Banyumas',
            'contact_phone' => '08123456789',
            'contact_email' => 'halo@dedijaya.test',
            'opening_hours' => 'Senin sampai Sabtu',
            'hero_eyebrow' => 'TERNAK BANYUMAS',
            'hero_description' => 'Profil dan riwayat sapi.',
            'instagram_url' => 'https://www.instagram.com/dedyjayafarm/',
            'google_maps_url' => 'https://www.google.com/maps/place/Dedy+Jaya+Farm/',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Peternakan Uji')
            ->assertSee('Sapi pilihan Banyumas')
            ->assertDontSee('TENTANG KAMI')
            ->assertDontSee('Tentang kami')
            ->assertSee('halo@dedijaya.test')
            ->assertSee('dedy-jaya-farm-logo.png')
            ->assertSee('Peta lokasi Peternakan Uji')
            ->assertSee('https://www.instagram.com/dedyjayafarm/')
            ->assertSee('https://www.google.com/maps/place/Dedy+Jaya+Farm/')
            ->assertDontSee(route('admin.login'));
    }

    public function test_home_page_sections_can_be_selected_in_admin_settings(): void
    {
        SiteSetting::setMany([
            'home_show_hero' => '0',
            'home_show_traceability' => '0',
            'home_show_featured' => '0',
            'home_show_care_story' => '0',
            'home_show_contact' => '0',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('TENTANG KAMI')
            ->assertDontSee('TERNAK TERPILIH · BANYUMAS')
            ->assertDontSee('SETIAP TERNAK PUNYA CERITA')
            ->assertDontSee('KATALOG PILIHAN')
            ->assertDontSee('Perjalanan setiap sapi, tercatat.')
            ->assertDontSee('MARI BERKUNJUNG');

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Bagian yang ditampilkan')
            ->assertSee('name="home_show_hero"', false);
    }

    public function test_sample_farm_seeder_creates_forty_cows_only_once(): void
    {
        $this->seed(SampleFarmSeeder::class);
        $this->seed(SampleFarmSeeder::class);

        $this->assertSame(40, Cow::query()->count());
        $this->assertSame(40, LivestockTag::query()->count());
        $this->assertSame(40, LivestockTagAssignment::query()->whereNull('released_at')->count());
        $this->assertSame(80, WeightLog::query()->count());
        $this->assertDatabaseHas('cows', ['tag_number' => 'DJF-1011', 'name' => 'Mahesa']);
        $this->assertDatabaseHas('cows', ['tag_number' => 'DJF-1040', 'name' => 'Cempaka']);
    }

    public function test_sample_farm_seeder_adds_missing_demo_cows_without_overwriting_existing_cows(): void
    {
        $existingCow = $this->createCow(['name' => 'Sapi Milik Peternakan']);

        $this->seed(SampleFarmSeeder::class);
        $this->seed(SampleFarmSeeder::class);

        $this->assertSame(41, Cow::query()->count());
        $this->assertSame('Sapi Milik Peternakan', $existingCow->fresh()->name);
        $this->assertSame(40, LivestockTag::query()->count());
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createCow(array $overrides = []): Cow
    {
        return Cow::query()->create(array_merge([
            'tag_number' => 'DJF-'.fake()->unique()->numerify('####'),
            'slug' => 'sapi-'.fake()->unique()->slug(),
            'name' => 'Sapi Uji',
            'breed' => 'Simental',
            'gender' => 'jantan',
            'arrived_at' => '2026-08-01',
            'initial_weight_kg' => 350,
            'price' => 25000000,
            'status' => 'available',
        ], $overrides));
    }

    private function createTag(Cow $cow): LivestockTag
    {
        $tag = LivestockTag::query()->create([
            'code' => 'QR-'.fake()->unique()->numerify('######'),
            'token' => bin2hex(random_bytes(20)),
            'slug' => 'qr-'.fake()->unique()->numerify('######'),
            'cow_id' => $cow->id,
        ]);
        $tag->assignments()->create([
            'cow_id' => $cow->id,
            'assigned_at' => now(),
        ]);

        return $tag;
    }
}
