<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFAAH/q842iQAAAABJRU5ErkJggg==';

    public function test_user_can_view_their_own_profile(): void
    {
        $user = User::factory()->create(['name' => 'Ada Tesfaye', 'phone' => '0912345678']);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk()
            ->assertSee('Ada Tesfaye')
            ->assertSee('0912345678')
            ->assertSee('Verified')
            ->assertSee('Active');
    }

    public function test_user_can_update_their_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', ['name' => 'Updated Name'])
            ->assertSessionHasNoErrors();

        $this->assertSame('Updated Name', $user->fresh()->name);
    }

    public function test_protected_account_fields_cannot_be_changed(): void
    {
        $user = User::factory()->create(['name' => 'Original Name', 'phone' => '0912345678']);

        $this->actingAs($user)->patch('/profile', [
            'name' => 'Changed Name',
            'phone' => '0999999999',
            'status' => User::STATUS_BANNED,
            'role' => 'admin',
            'phone_verified_at' => now()->toDateTimeString(),
        ])->assertSessionHasErrors(['phone', 'status', 'role', 'phone_verified_at']);

        $fresh = $user->fresh();
        $this->assertSame('Original Name', $fresh->name);
        $this->assertSame('0912345678', $fresh->phone);
        $this->assertSame(User::STATUS_ACTIVE, $fresh->status);
        $this->assertNotNull($fresh->phone_verified_at);
        $this->assertSame(User::ROLE_USER, $fresh->role);
    }

    public function test_user_can_upload_and_replace_an_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'avatar' => $this->image(),
        ])->assertSessionHasNoErrors();

        $path = $user->fresh()->avatar;
        $this->assertNotNull($path);
        $this->assertStringStartsWith('avatars/', $path);
        Storage::disk('public')->assertExists($path);

        $oldPath = $path;
        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'avatar' => $this->image(),
        ])->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($user->fresh()->avatar);
    }

    public function test_avatar_must_be_a_supported_image_type(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'avatar' => UploadedFile::fake()->createWithContent('avatar.txt', 'not an image'),
        ])->assertSessionHasErrors('avatar');

        $this->assertNull($user->fresh()->avatar);
    }

    public function test_avatar_file_size_is_limited(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'avatar' => UploadedFile::fake()->create('large.png', 3000, 'image/png'),
        ])->assertSessionHasErrors('avatar');

        $this->assertNull($user->fresh()->avatar);
    }

    private function image(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('avatar.png', base64_decode(self::PNG));
    }
}
