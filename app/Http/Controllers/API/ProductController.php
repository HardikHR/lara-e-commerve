<?php
namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as ControllersBaseController;
use App\Models\Product;
use Validator;
use App\Http\Resources\Product as ProductResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductController extends ControllersBaseController
{
    public function index()
    {
        $products = Product::where('deleted', '0')->get();
        return $this->sendResponse(ProductResource::collection($products), 'Products retrieved successfully.');
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => ['required',Rule::unique('products', 'name')],
            'detail' => 'required'
        ],
        [
            'name.required' => 'Product field is required!',
            'detail.required' => 'detail field is required!',
        ]);
        if($validator->fails()){
            return response()->json(['message' => $validator->errors()->all()]);
        }
        $input['user_id'] = Auth::id();
        $product = Product::create($input);
        return response()->json(['data' => $product,'message' => 'Product created successfully.']);
    }

    public function show($id)
    {
        $product = Product::find($id);
        if (is_null($product)) {
            return $this->sendError('Product not found.');
            
        }
        return $this->sendResponse(new ProductResource($product), 'Product retrieved successfully.');
    }

    public function update(Request $request, Product $product)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => ['required', Rule::unique('products', 'name')->ignore($request->product->id)],
            'detail' => 'required'
        ]);
        if($validator->fails()){
            return response()->json(['message' => $validator->errors()->all()]);
        }
        $product->name = $input['name'];
        $product->detail = $input['detail'];
        $product->save();
        return $this->sendResponse(new ProductResource($product), 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->deleted = '1';
        $product->save();
        return response()->json(['message' => "$product->name product deleted successfully."]);
    }
}
