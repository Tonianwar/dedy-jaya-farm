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

    public function test_public_catalog_uses_the_assigned_qr_short_slug(): void
    {
        $cow = $this->createCow(['name' => 'Sapi QR']);
        $tag = $this->createTag($cow);

        $this->get(route('catalog'))
            ->assertOk()
            ->assertSee('href="'.route('cows.show', $tag->slug).'"', false);
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
            ->assertSee('https://www.youtube-nocookie.com/embed/abcdefghijk', false);
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
                'about_title' => 'Dirawat dengan cermat.',
                'about_description' => 'Peternakan yang terbuka.',
                'address' => 'Banyumas',
                'contact_phone' => '08123456789',
                'contact_email' => 'halo@dedijaya.test',
                'opening_hours' => 'Senin sampai Sabtu',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('site_settings', ['key' => 'farm_name', 'value' => 'Dedy Jaya Farm']);
        $this->assertDatabaseHas('site_settings', ['key' => 'public_url', 'value' => 'https://dedijayafarm.id']);
        $this->assertDatabaseHas('site_settings', ['key' => 'whatsapp', 'value' => '6281234567890']);
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
            'about_title' => 'Dirawat dengan baik',
            'about_description' => 'Sejarah dan cerita peternakan kami.',
            'address' => 'Banyumas',
            'contact_phone' => '08123456789',
            'contact_email' => 'halo@dedijaya.test',
            'opening_hours' => 'Senin sampai Sabtu',
            'hero_eyebrow' => 'TERNAK BANYUMAS',
            'hero_description' => 'Profil dan riwayat sapi.',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Peternakan Uji')
            ->assertSee('Sapi pilihan Banyumas')
            ->assertSee('Dirawat dengan baik')
            ->assertSee('halo@dedijaya.test')
            ->assertDontSee(route('admin.login'));
    }

    public function test_sample_farm_seeder_creates_ten_cows_only_once(): void
    {
        $this->seed(SampleFarmSeeder::class);
        $this->seed(SampleFarmSeeder::class);

        $this->assertSame(10, Cow::query()->count());
        $this->assertSame(10, LivestockTag::query()->count());
        $this->assertSame(10, LivestockTagAssignment::query()->whereNull('released_at')->count());
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
