<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller {
    protected $storeValidationRules = [
        'author' => 'required|max:255',
        'text' => 'required|max:255',
    ];

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param int $product_id
     * @return Response
     */
    public function store(Request $request, $product_id) {
        $product = Product::find($product_id);

        if (!$product)
            return response(['message' => 'Product not found.'])
                ->setStatusCode(404, 'Product not found');

        $validator = Validator::make(
            $request->only(array_keys($this->storeValidationRules)),
            $this->storeValidationRules
        );

        if ($validator->fails()) {
            return response(['status' => false, 'message' => $validator->errors()])
                ->setStatusCode(400, 'Creating error');
        }

        $data = $validator->getData();

        $product->comments()->create($data);

        return response(['status' => true])
            ->setStatusCode(201, 'Successful creating');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $product_id
     * @param int $id
     * @return Response
     */
    public function destroy($product_id, $id) {
        $product = Product::find($product_id);

        if (!$product)
            return response(['message' => 'Product not found.'])
                ->setStatusCode(404, 'Product not found');

        $comment = Comment::find($id);

        if (!$comment)
            return response(['message' => 'Comment not found.'])
                ->setStatusCode(404, 'Comment not found');

        if ($product->id !== $comment->product_id) {
            return response(['message' => 'This comment belongs to another product.'])
                ->setStatusCode(400, 'Comment not found');
        }

        $comment->delete();

        return response(['status' => true])
            ->setStatusCode(200, 'Successful deleting');
    }
}
