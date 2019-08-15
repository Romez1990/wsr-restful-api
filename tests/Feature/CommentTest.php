<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test storing a product
     *
     * @return void
     */
    public function testStoring()
    {
        $product = factory(Product::class)->create();
        $comment = factory(Comment::class)->make();

        $response = $this->post('/api/product/'.$product->id.'/comment', [
            'author' => $comment->author,
            'text' => $comment->text,
        ]);

        $response->assertStatus(201);
        $response->assertExactJson(['status' => true]);

        $this->assertDatabaseHas('comments', [
            'product_id' => $product->id,
            'author' => $comment->author,
            'text' => $comment->text,
        ]);
    }

    /**
     * Test storing a product: not found
     *
     * @return void
     */
    public function testStoringNotFound()
    {
        factory(Product::class)->create();
        $comment = factory(Comment::class)->make();

        $response = $this->post('/api/product/999/comment', [
            'author' => $comment->author,
            'text' => $comment->text,
        ]);

        $response->assertStatus(404);
        $response->assertExactJson(['message' => 'Product not found.']);
    }

    /**
     * Test storing a product validation: required
     *
     * @return void
     */
    public function testStoringValidationRequired()
    {
        $product = factory(Product::class)->create();

        $response = $this->post('/api/product/'.$product->id.'/comment');

        $response->assertStatus(400);
        $response->assertExactJson([
            'status' => false,
            'message' => [
                'author' => [
                    'The author field is required.',
                ],
                'text' => [
                    'The text field is required.',
                ],
            ],
        ]);
    }

    /**
     * Test storing a product validation: max
     *
     * @return void
     */
    public function testStoringValidationMax()
    {
        $product = factory(Product::class)->create();

        $response = $this->post('/api/product/'.$product->id.'/comment', [
            'author' => Str::random(256),
            'text' => Str::random(256),
        ]);

        $response->assertStatus(400);
        $response->assertExactJson([
            'status' => false,
            'message' => [
                'author' => [
                    'The author may not be greater than 255 characters.',
                ],
                'text' => [
                    'The text may not be greater than 255 characters.',
                ],
            ],
        ]);
    }

    /**
     * Test deleting a product
     *
     * @return void
     */
    public function testDeleting()
    {
        $product = factory(Product::class)->create();
        $comment = factory(Comment::class)->create();
        $comment->update(['product_id' => $product->id]);

        $response = $this->delete('/api/product/'.$product->id.'/comment/'.$comment->id);

        $response->assertStatus(200);
        $response->assertExactJson(['status' => true]);

        $this->assertDatabaseMissing('comments', [
            'product_id' => $product->id,
            'author' => $comment->author,
            'text' => $comment->text,
        ]);
    }

    /**
     * Test storing a product: product not found
     *
     * @return void
     */
    public function testDeletingProductNotFound()
    {
        factory(Product::class)->create();
        $comment = factory(Comment::class)->create();

        $response = $this->delete('/api/product/999/comment/'.$comment->id);

        $response->assertStatus(404);
        $response->assertExactJson(['message' => 'Product not found.']);
    }

    /**
     * Test storing a product: comment not found
     *
     * @return void
     */
    public function testDeletingCommentNotFound()
    {
        $product = factory(Product::class)->create();

        $response = $this->delete('/api/product/'.$product->id.'/comment/999');

        $response->assertStatus(404);
        $response->assertExactJson(['message' => 'Comment not found.']);
    }

    /**
     * Test storing a product: another product
     *
     * @return void
     */
    public function testDeletingAnotherProduct()
    {
        $product1 = factory(Product::class)->create();
        $product2 = factory(Product::class)->create();
        $comment = factory(Comment::class)->create();
        $comment->update(['product_id' => $product2->id]);

        $response = $this->delete('/api/product/'.$product1->id.'/comment/'.$comment->id);

        $response->assertStatus(400);
        $response->assertExactJson(['message' => 'This comment belongs to another product.']);
    }
}
