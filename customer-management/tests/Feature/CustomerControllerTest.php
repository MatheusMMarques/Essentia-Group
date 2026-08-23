<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_home_redirects_to_the_customer_list(): void
    {
        $this->get(route('home'))->assertRedirect(route('customers.index'));
    }

    public function test_customer_show_route_is_not_exposed(): void
    {
        $this->assertFalse(Route::has('customers.show'));
    }

    public function test_customers_are_listed_with_the_most_recent_first(): void
    {
        $olderCustomer = Customer::factory()->create([
            'name' => 'Cliente antigo',
            'created_at' => now()->subDay(),
        ]);
        $newerCustomer = Customer::factory()->create([
            'name' => 'Cliente recente',
            'created_at' => now(),
        ]);

        $this->get(route('customers.index'))
            ->assertOk()
            ->assertSeeInOrder([$newerCustomer->name, $olderCustomer->name]);
    }

    public function test_a_customer_can_be_created_with_normalized_data_and_a_photo(): void
    {
        Storage::fake('public');

        $response = $this->post(route('customers.store'), [
            'name' => 'Maria Silva',
            'email' => '  MARIA@EXAMPLE.COM ',
            'phone' => '(48) 99999-1234',
            'photo' => $this->fakeImage(),
        ]);

        $response
            ->assertRedirect(route('customers.index'))
            ->assertSessionHas('success');

        $customer = Customer::sole();

        $this->assertSame('maria@example.com', $customer->email);
        $this->assertSame('48999991234', $customer->phone);
        Storage::disk('public')->assertExists($customer->photo_path);
    }

    public function test_duplicate_email_is_rejected_after_normalization(): void
    {
        Storage::fake('public');
        Customer::factory()->create(['email' => 'maria@example.com']);

        $this->post(route('customers.store'), [
            'name' => 'Outra Maria',
            'email' => '  MARIA@EXAMPLE.COM ',
            'phone' => '48999991234',
            'photo' => $this->fakeImage(),
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('customers', 1);
    }

    public function test_email_array_is_rejected_without_persisting_a_customer(): void
    {
        Storage::fake('public');
        $data = $this->validCustomerData(photo: $this->fakeImage());
        $data['email'] = ['unexpected'];

        $this->post(route('customers.store'), $data)
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('customers', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_phone_array_is_rejected_without_persisting_a_customer(): void
    {
        Storage::fake('public');
        $data = $this->validCustomerData(photo: $this->fakeImage());
        $data['phone'] = ['unexpected'];

        $this->post(route('customers.store'), $data)
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('customers', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_photo_is_required_when_creating_a_customer(): void
    {
        $this->post(route('customers.store'), $this->validCustomerData(photo: null))
            ->assertSessionHasErrors('photo');

        $this->assertDatabaseCount('customers', 0);
    }

    public function test_photo_must_be_a_valid_image(): void
    {
        Storage::fake('public');

        $this->post(route('customers.store'), $this->validCustomerData(
            photo: UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ))->assertSessionHasErrors('photo');

        $this->assertDatabaseCount('customers', 0);
    }

    public function test_failed_photo_upload_has_a_friendly_message_when_creating(): void
    {
        $this->post(route('customers.store'), $this->validCustomerData(
            photo: $this->failedUpload(),
        ))->assertSessionHasErrors([
            'photo' => 'Não foi possível enviar a foto. Verifique se o arquivo possui no máximo 2 MB e tente novamente.',
        ]);

        $this->assertDatabaseCount('customers', 0);
    }

    public function test_customer_edit_page_is_displayed(): void
    {
        $customer = Customer::factory()->create();

        $this->get(route('customers.edit', $customer))
            ->assertOk()
            ->assertSee($customer->name)
            ->assertSee($customer->email);
    }

    public function test_update_without_a_new_photo_preserves_the_existing_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('customers/original.jpg', 'photo');
        $customer = Customer::factory()->create(['photo_path' => 'customers/original.jpg']);

        $this->put(route('customers.update', $customer), [
            'name' => 'Nome atualizado',
            'email' => $customer->email,
            'phone' => '(48) 98888-7777',
        ])->assertRedirect(route('customers.index'));

        $this->assertSame('customers/original.jpg', $customer->fresh()->photo_path);
        $this->assertSame('48988887777', $customer->fresh()->phone);
        Storage::disk('public')->assertExists('customers/original.jpg');
    }

    public function test_update_with_a_new_photo_replaces_and_removes_the_old_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('customers/original.jpg', 'photo');
        $customer = Customer::factory()->create(['photo_path' => 'customers/original.jpg']);

        $this->put(route('customers.update', $customer), [
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'photo' => $this->fakeImage(),
        ])->assertRedirect(route('customers.index'));

        $newPhotoPath = $customer->fresh()->photo_path;

        $this->assertNotSame('customers/original.jpg', $newPhotoPath);
        Storage::disk('public')->assertExists($newPhotoPath);
        Storage::disk('public')->assertMissing('customers/original.jpg');
    }

    public function test_failed_photo_upload_has_a_friendly_message_when_updating(): void
    {
        $customer = Customer::factory()->create();

        $this->put(route('customers.update', $customer), [
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'photo' => $this->failedUpload(),
        ])->assertSessionHasErrors([
            'photo' => 'Não foi possível enviar a foto. Verifique se o arquivo possui no máximo 2 MB e tente novamente.',
        ]);
    }

    public function test_customer_can_keep_their_own_email_during_update(): void
    {
        $customer = Customer::factory()->create(['email' => 'maria@example.com']);

        $this->put(route('customers.update', $customer), [
            'name' => $customer->name,
            'email' => '  MARIA@EXAMPLE.COM ',
            'phone' => $customer->phone,
        ])->assertSessionHasNoErrors();

        $this->assertSame('maria@example.com', $customer->fresh()->email);
    }

    public function test_customer_cannot_use_another_customers_email(): void
    {
        $customer = Customer::factory()->create(['email' => 'first@example.com']);
        Customer::factory()->create(['email' => 'second@example.com']);

        $this->from(route('customers.edit', $customer))
            ->put(route('customers.update', $customer), [
                'name' => $customer->name,
                'email' => ' SECOND@EXAMPLE.COM ',
                'phone' => $customer->phone,
            ])
            ->assertRedirect(route('customers.edit', $customer))
            ->assertSessionHasErrors('email');

        $this->assertSame('first@example.com', $customer->fresh()->email);
    }

    public function test_email_array_is_rejected_without_updating_the_customer(): void
    {
        $customer = Customer::factory()->create(['email' => 'original@example.com']);

        $this->put(route('customers.update', $customer), [
            'name' => 'Nome alterado',
            'email' => ['unexpected'],
            'phone' => $customer->phone,
        ])->assertSessionHasErrors('email');

        $customer->refresh();
        $this->assertSame('original@example.com', $customer->email);
        $this->assertNotSame('Nome alterado', $customer->name);
    }

    public function test_phone_array_is_rejected_without_updating_the_customer(): void
    {
        $customer = Customer::factory()->create(['phone' => '48999991234']);

        $this->put(route('customers.update', $customer), [
            'name' => 'Nome alterado',
            'email' => $customer->email,
            'phone' => ['unexpected'],
        ])->assertSessionHasErrors('phone');

        $customer->refresh();
        $this->assertSame('48999991234', $customer->phone);
        $this->assertNotSame('Nome alterado', $customer->name);
    }

    public function test_destroy_removes_the_customer_and_their_photo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('customers/customer.jpg', 'photo');
        $customer = Customer::factory()->create(['photo_path' => 'customers/customer.jpg']);

        $this->delete(route('customers.destroy', $customer))
            ->assertRedirect(route('customers.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($customer);
        Storage::disk('public')->assertMissing('customers/customer.jpg');
    }

    public function test_destroy_succeeds_when_the_photo_no_longer_exists(): void
    {
        Storage::fake('public');
        $customer = Customer::factory()->create(['photo_path' => 'customers/missing.jpg']);

        $this->delete(route('customers.destroy', $customer))
            ->assertRedirect(route('customers.index'));

        $this->assertModelMissing($customer);
    }

    public function test_nonexistent_customer_returns_not_found(): void
    {
        $this->get(route('customers.edit', 999))->assertNotFound();
        $this->delete(route('customers.destroy', 999))->assertNotFound();
    }

    public function test_customer_model_normalizes_email_and_phone(): void
    {
        $customer = new Customer([
            'email' => '  CUSTOMER@EXAMPLE.COM ',
            'phone' => '+55 (48) 99999-1234',
        ]);

        $this->assertSame('customer@example.com', $customer->email);
        $this->assertSame('5548999991234', $customer->phone);
    }

    private function validCustomerData(?UploadedFile $photo): array
    {
        return [
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'phone' => '48999991234',
            'photo' => $photo,
        ];
    }

    private function fakeImage(): UploadedFile
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true,
        );

        return UploadedFile::fake()->createWithContent('photo.png', $png);
    }

    private function failedUpload(): UploadedFile
    {
        return new UploadedFile(
            'failed-upload',
            'photo.jpg',
            'image/jpeg',
            UPLOAD_ERR_INI_SIZE,
            true,
        );
    }
}
