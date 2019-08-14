<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase {
    use RefreshDatabase;

    /**
     * Test product index
     *
     * @return void
     */
    public function testIndex() {
        $response = $this->get('/api/product');

        $response->assertStatus(200);
        $response->assertJsonStructure([]);
    }

    /**
     * Test storing a product
     *
     * @return void
     */
    public function testStoring() {
        $product = factory(Product::class)->make();

        $response = $this->post('/api/product', [
            'title' => $product->title,
            'manufacturer' => $product->manufacturer,
            'text' => $product->text,
            'tags' => 'tag1, tag2,,,,',
            'image' => UploadedFile::fake()->image('image.jpg', 100, 100),
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['status', 'product_id']);
        $response->assertJson(['status' => true]);

        $this->assertDatabaseHas('products', [
            'title' => $product->title,
            'manufacturer' => $product->manufacturer,
            'text' => $product->text,
        ]);

        $product_id = $response->json('product_id');
        $this->assertDatabaseHas('tags', [
            'product_id' => $product_id,
            'name' => 'tag1',
        ]);
        $this->assertDatabaseHas('tags', [
            'product_id' => $product_id,
            'name' => 'tag2',
        ]);
        $this->assertDatabaseMissing('tags', [
            'product_id' => $product_id,
            'name' => '',
        ]);

        Storage::disk('public')->assertExists('product_images/'.$product->title.'.jpg');
    }

    /**
     * Test storing validation: required
     *
     * @return void
     */
    public function testStoringValidationRequired() {
        $response = $this->post('/api/product');

        $response->assertStatus(400);
        $response->assertExactJson([
            'status' => false,
            'message' => [
                'title' => [
                    'The title field is required.',
                ],
                'manufacturer' => [
                    'The manufacturer field is required.',
                ],
                'text' => [
                    'The text field is required.',
                ],
                'image' => [
                    'The image field is required.',
                ],
            ],
        ]);
    }

    /**
     * Test storing validation: unique
     *
     * @return void
     */
    public function testStoringValidationUnique() {
        $product = factory(Product::class)->create();

        $response = $this->post('/api/product', [
            'title' => $product->title,
            'manufacturer' => $product->manufacturer,
            'text' => $product->text,
            'image' => UploadedFile::fake()->image('image.jpg', 100, 100),
        ]);

        $response->assertStatus(400);
        $response->assertExactJson([
            'status' => false,
            'message' => [
                'title' => [
                    'The title has already been taken.',
                ],
            ],
        ]);
    }

    /**
     * Test storing validation: max
     *
     * @return void
     */
    public function testStoringValidationMax() {
        $response = $this->post('/api/product', [
            'title' => Str::random(256),
            'manufacturer' => Str::random(256),
            'text' => Str::random(256),
            'image' => UploadedFile::fake()->create('image.jpg', 2049),
        ]);

        $response->assertStatus(400);
        $response->assertExactJson([
            'status' => false,
            'message' => [
                'title' => [
                    'The title may not be greater than 255 characters.',
                ],
                'manufacturer' => [
                    'The manufacturer may not be greater than 255 characters.',
                ],
                'image' => [
                    'The image may not be greater than 2048 kilobytes.',
                ],
            ],
        ]);
    }

    /**
     * Test storing validation: mimes
     *
     * @return void
     */
    public function testStoringValidationMimes() {
        $product = factory(Product::class)->make();

        $response = $this->post('/api/product', [
            'title' => $product->title,
            'manufacturer' => $product->manufacturer,
            'text' => $product->text,
            'image' => UploadedFile::fake()->create('another-file.ext'),
        ]);

        $response->assertStatus(400);
        $response->assertExactJson([
            'status' => false,
            'message' => [
                'image' => [
                    'The image must be a file of type: jpeg, png.',
                ],
            ],
        ]);
    }

    /**
     * Test showing a product
     *
     * @return void
     */
    public function testShowing() {
        $product = factory(Product::class)->create();

        $response = $this->get('/api/product/'.$product->id);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'title',
            'datetime',
            'manufacturer',
            'text',
            'image',
        ]);
    }

    /**
     * Test showing a product: not found
     *
     * @return void
     */
    public function testShowingNotFound() {
        $response = $this->get('/api/product/999');

        $response->assertStatus(404);
        $response->assertExactJson(['message' => 'Product not found.']);
    }

    /**
     * Test editing a product
     *
     * @return void
     */
    public function testEditing() {
        $product = factory(Product::class)->create();

        $tags = $product->tags;

        $response = $this->post('/api/product/'.$product->id, [
            'title' => $product->title.'2',
            'manufacturer' => $product->manufacturer.'2',
            'text' => $product->text.'2',
            'tags' => 'tag3, tag4,,,,',
            'image' => UploadedFile::fake()->image('image.jpg', 100, 100),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'product' => [
                'title' => $product->title.'2',
                'manufacturer' => $product->manufacturer.'2',
                'text' => $product->text.'2',
                'image' => url(Storage::url('product_images/'.$product->title.'2.jpg')),
            ],
        ]);

        foreach ($tags as $tag) {
            $this->assertDatabaseHas('tags', [
                'product_id' => $product->id,
                'name' => $tag->name,
            ]);
        }
        $this->assertDatabaseHas('tags', [
            'product_id' => $product->id,
            'name' => 'tag3',
        ]);
        $this->assertDatabaseHas('tags', [
            'product_id' => $product->id,
            'name' => 'tag4',
        ]);
        $this->assertDatabaseMissing('tags', [
            'product_id' => $product->id,
            'name' => '',
        ]);
    }

    /**
     * Test editing a product: title
     *
     * @return void
     */
    public function testEditingTitle() {
        $product = factory(Product::class)->create();

        $response = $this->post('/api/product/'.$product->id, [
            'title' => $product->title.'3',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'product' => [
                'title' => $product->title.'3',
                'manufacturer' => $product->manufacturer,
                'text' => $product->text,
                'image' => url(Storage::url('product_images/'.$product->title.'3.jpg')),
            ],
        ]);
    }

    /**
     * Test editing a product: image
     *
     * @return void
     */
    public function testEditingImage() {
        $product = factory(Product::class)->create();

        $response = $this->post('/api/product/'.$product->id, [
            'image' => UploadedFile::fake()->image('image.jpg', 100, 100),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'product' => [
                'title' => $product->title,
                'manufacturer' => $product->manufacturer,
                'text' => $product->text,
                'image' => url(Storage::url('product_images/'.$product->title.'.jpg')),
            ],
        ]);
    }

    /**
     * Test editing a product: not found
     *
     * @return void
     */
    public function testEditingNotFound() {
        $response = $this->post('/api/product/999', [
            'image' => UploadedFile::fake()->image('image.jpg', 100, 100),
        ]);

        $response->assertStatus(404);
        $response->assertExactJson(['message' => 'Product not found.']);
    }

    /**
     * Test editing validation: unique
     *
     * @return void
     */
    public function testEditingValidationUnique() {
        $product = factory(Product::class)->create();

        $response = $this->post('/api/product/'.$product->id, [
            'title' => $product->title,
        ]);

        $response->assertStatus(400);
        $response->assertExactJson([
            'status' => false,
            'message' => [
                'title' => [
                    'The title has already been taken.',
                ],
            ],
        ]);
    }

    /**
     * Test editing validation: max
     *
     * @return void
     */
    public function testEditingValidationMax() {
        $product = factory(Product::class)->create();

        $response = $this->post('/api/product/'.$product->id, [
            'title' => Str::random(256),
            'manufacturer' => Str::random(256),
            'text' => Str::random(256),
            'image' => UploadedFile::fake()->create('image.jpg', 2049),
        ]);

        $response->assertStatus(400);
        $response->assertExactJson([
            'status' => false,
            'message' => [
                'title' => [
                    'The title may not be greater than 255 characters.',
                ],
                'manufacturer' => [
                    'The manufacturer may not be greater than 255 characters.',
                ],
                'image' => [
                    'The image may not be greater than 2048 kilobytes.',
                ],
            ],
        ]);
    }

    /**
     * Test editing validation: mimes
     *
     * @return void
     */
    public function testEditingValidationMimes() {
        $product = factory(Product::class)->create();

        $response = $this->post('/api/product/'.$product->id, [
            'image' => UploadedFile::fake()->create('another-file.ext'),
        ]);

        $response->assertStatus(400);
        $response->assertExactJson([
            'status' => false,
            'message' => [
                'image' => [
                    'The image must be a file of type: jpeg, png.',
                ],
            ],
        ]);
    }

    /**
     * Test destroying a product
     *
     * @return void
     */
    public function testDestroying() {
        $product = factory(Product::class)->create();

        $response = $this->delete('/api/product/'.$product->id);

        $response->assertStatus(200);
        $response->assertExactJson(['status' => true]);
    }

    /**
     * Test destroying a product: not found
     *
     * @return void
     */
    public function testDestroyingNotFound() {
        $response = $this->delete('/api/product/999');

        $response->assertStatus(404);
        $response->assertExactJson(['message' => 'Product not found.']);
    }

    /**
     * Test searching by a tag
     *
     * @return void
     */
    public function testSearchByTag() {
        $product1 = factory(Product::class)->create();
        $tag1 = Tag::create([
            'product_id' => $product1->id,
            'name' => 'sometag',
        ]);
        $tag1->update(['product_id' => $product1->id]);
        $product2 = factory(Product::class)->create();
        $tag2 = Tag::create([
            'product_id' => $product2->id,
            'name' => 'sometag',
        ]);

        $response = $this->get('/api/product/tag/sometag');

        $response->assertStatus(200);
        $response->assertJson([
            [
                'title' => $product1->title,
                'manufacturer' => $product1->manufacturer,
                'text' => $product1->text,
            ],
            [
                'title' => $product2->title,
                'manufacturer' => $product2->manufacturer,
                'text' => $product2->text,
            ],
        ]);
    }
}
