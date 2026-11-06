<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_image_is_stored_on_the_public_disk(): void
    {
        Storage::fake('public');
        $category = Category::create([
            'name' => 'Minuman',
            'slug' => 'minuman',
        ]);

        $response = $this->post(route('products.store'), [
            'category_id' => $category->id,
            'name' => 'Air Mineral',
            'sku' => 'AIR-001',
            'price' => 5000,
            'stock' => 10,
            'image' => UploadedFile::fake()->image('air-mineral.jpg'),
        ]);

        $response->assertRedirect(route('products.index'));

        $product = Product::where('sku', 'AIR-001')->firstOrFail();
        $this->assertNotEmpty($product->image);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_product_creation_form_supports_image_uploads(): void
    {
        $response = $this->get(route('products.create'));

        $response->assertOk();
        $response->assertSee('enctype="multipart/form-data"', false);
        $response->assertSee('name="image"', false);
    }

    public function test_standalone_image_upload_is_stored_on_the_public_disk(): void
    {
        Storage::fake('public');

        $response = $this->withoutMiddleware()->post(route('file.upload'), [
            'file' => UploadedFile::fake()->image('upload.jpg'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Gambar berhasil diunggah.');
        $response->assertSessionHas('path', function (string $path): bool {
            Storage::disk('public')->assertExists($path);

            return true;
        });
    }
}
