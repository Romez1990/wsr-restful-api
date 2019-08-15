<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductWithCommentsResource;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        return response(ProductResource::collection(Product::all()))
            ->setStatusCode(200, 'List products');
    }

    protected $storeValidationRules = [
        'title' => 'required|unique:products|max:255',
        'manufacturer' => 'required|max:255',
        'text' => 'required',
        'image' => 'required|mimes:jpeg,png|max:2048',
        'tags' => '',
    ];

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request) {
        $validator = Validator::make(
            $request->only(array_keys($this->storeValidationRules)),
            $this->storeValidationRules
        );

        if ($validator->fails()) {
            return response(['status' => false, 'message' => $validator->errors()])
                ->setStatusCode(400, 'Creating error');
        }

        $data = $validator->getData();

        $imageFileName =
            $data['title'].'.'.$data['image']->getClientOriginalExtension();
        $data['image'] =
            $data['image']->storeAs('product_images', $imageFileName, 'public');

        $product = Product::create($data);

        if (Arr::has($data, 'tags')) {
            $tags = [];
            foreach (explode(',', $data['tags']) as $tag) {
                $trimmed_tag = trim($tag);
                if (!empty($trimmed_tag))
                    $tags[] = ['name' => $trimmed_tag];
            }
            $product->tags()->createMany($tags);
        }

        return response(['status' => true, 'product_id' => $product->id])
            ->setStatusCode(201, 'Successful creating');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id) {
        $product = Product::find($id);

        if (!$product)
            return response(['message' => 'Product not found.'])
                ->setStatusCode(404, 'Product not found');

        return response(new ProductWithCommentsResource($product))
            ->setStatusCode(200, 'View product');
    }

    protected $updateValidationRules = [
        'title' => 'unique:products|max:255',
        'manufacturer' => 'max:255',
        'text' => '',
        'image' => 'mimes:jpeg,png|max:2048',
        'tags' => '',
    ];

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id) {
        $product = Product::find($id);

        if (!$product)
            return response(['message' => 'Product not found.'])
                ->setStatusCode(404, 'Product not found');

        $validator = Validator::make(
            $request->only(array_keys($this->updateValidationRules)),
            $this->updateValidationRules
        );

        if ($validator->fails()) {
            return response(['status' => false, 'message' => $validator->errors()])
                ->setStatusCode(400, 'Editing error');
        }

        $data = $validator->getData();

        if (Arr::has($data, 'title') && !Arr::has($data, 'image')) {
            $imageFileName =
                'product_images/'.
                $data['title'].
                '.'.
                pathinfo(public_path($product->image), PATHINFO_EXTENSION);
            if (Storage::disk('public')->move($product->image, $imageFileName))
                $data['image'] = $imageFileName;
            else
                return abort(500);
        } else if (Arr::has($data, 'image')) {
            Storage::disk('public')->delete($product->image);
            $title = Arr::has($data, 'title') ? $data['title'] : $product->title;
            $imageFileName =
                $title.'.'.$data['image']->getClientOriginalExtension();
            $data['image'] =
                $data['image']->storeAs('product_images', $imageFileName, 'public');
        }

        $product->update($data);

        if (Arr::has($data, 'tags')) {
            $tags = [];
            foreach (explode(',', $data['tags']) as $tag) {
                $trimmed_tag = trim($tag);
                if (!empty($trimmed_tag))
                    $tags[] = ['name' => $trimmed_tag];
            }
            $product->tags()->delete();
            $product->tags()->createMany($tags);
        }

        return response(['status' => true, 'product' => new ProductResource($product)])
            ->setStatusCode(200, 'Successful editing');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id) {
        $product = Product::find($id);

        if (!$product)
            return response(['message' => 'Product not found.'])
                ->setStatusCode(404, 'Product not found');

        Storage::delete($product->image);
        $product->delete();

        return response(['status' => true])
            ->setStatusCode(200, 'Successful deleting');
    }

    public function searchByTag($tagName) {
        $product_ids = Tag::whereName($tagName)->get('product_id');
        $products = Product::find($product_ids);
        return response(ProductResource::collection($products))
            ->setStatusCode(200, 'Found product');
    }
}
