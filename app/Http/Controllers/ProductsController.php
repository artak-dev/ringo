<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Product;
use App\Models\Ingredient;
use App\Models\DoughIngredients;
use App\Models\ProductIngredients;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\ProductResource;
use App\Http\Exports\ProductReportExport;
use App\Http\Requests\ReportDataRequest;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index(){
        
        $products = Product::all();

        return view('products', compact('products'));
    }

    public function getReport(ReportDataRequest $request){
        $data = $request->data;
        $reportData = [];
        $summary = [
            'total_revenue' => 0,
            'total_manufactured' => 0,
            'total_sold' => 0,
            'total_unsold' => 0,
            'best_product' => null,
            'worst_product' => null,
        ];

        foreach ($data as $key => $item) {
            $product = Product::where("id",$item['id'])->first();
            $revenue = $product->price * $item['sold_quantity'];
            $unsold_quantity = $item['manufactured_quantity'] - $item['sold_quantity'];
            $loss_cost = $unsold_quantity * $product->price;
            $profit = $revenue - $loss_cost;

            $reportData[$key] = [
                'id' => $product->id,
                'product_name' => $product->name,
                'price' => $product->price,
                'manufactured_quantity' =>  $item['manufactured_quantity'],
                'sold_quantity' => $item['sold_quantity'],
                'unsold_quantity' => $unsold_quantity,
                'revenue' => $revenue,
                'loss_cost' => $loss_cost,
                'profit' => $profit,
                'productCost' => $this->calculateProductCost($product),
                'date' => date("d/m/Y"),
            ];

        }

        $fileName = "RINGO_REPORT_date_".date("d_m_Y_H_i_s").".xlsx";

        if( Excel::store(new ProductReportExport($reportData), "reports/".$fileName)){
            ob_end_clean();
            return response()->json([ 
                'status'=>'success',
                'route'=>  route('clientDownload', ['file' => $fileName]) ,
            ],200);
        }else{
            return response()->json(['status'=>'error','message'=>"Something went wrong"],500);
        }

    }

    public function calculateProductCost($product)
    {

        $productDetails = $this->getProductDetails($product->id);

        foreach ($productDetails['dough_ingredients'] as $key => $ingredient) {
            $product_dough_info[] = $this->getProductDoughCost($product, $ingredient,$key);
        }

        foreach ($productDetails['ingredients'] as $key => $ingredient) {
            $product_kernel_info[] =  $this->getProductKernelCost($product, $ingredient,$key);
            $product_core_info[]   =  $this->getProductCoreCost($product, $ingredient,$key);
        }
        return [
            'dough' => isset($product_dough_info) ? array_filter($product_dough_info) : [],
            'core' => isset($product_core_info) ? array_filter($product_core_info) : [] ,
            'kernel' => isset($product_kernel_info) ? array_filter($product_kernel_info) : [],
        ];
    }

    public function getProductDetails($productId)
    {
        $product = Product::with(['productIngredients', 'doughIngredients'])->find($productId);

        if (!$product) {
            return false;
        }

        return [
            'product' => $product,
            'ingredients' => $product->productIngredients,
            'dough_ingredients' => $product->doughIngredients,
        ];
    }

    public function calculateCost(float $quantity, float $totalCost): float
    {
        $cost = ($quantity / 1000 ) * $totalCost;
        return $cost;
    }

    public function calculateCostForPcs(float $quantity, float $totalCost): float
    {
        $cost = ($quantity * $totalCost) / 12;
        return $cost;
    }

    public function getProductKernelCost($product, $ingredient){
        $realCost = [];
        if($product_kernel_ingredient = ProductIngredients::where("ingredient_id",$ingredient->id)->where('product_id',$product->id)->where("type",Product::TYPE_KERNEL)->first()){
            switch ($ingredient->unit) {
                case "kg":
                    $quantityInGrams = $product_kernel_ingredient->quantity;
                    $totalCost = $ingredient->cost_per_unit; 
                    $kernelCost = $this->calculateCost($quantityInGrams,  $totalCost);
                    break;
                case "L":
                    $quantityInGrams = $ingredient->cost_per_unit; 
                    $totalCost = $product_kernel_ingredient->quantity;
                    $kernelCost = $this->calculateCost($quantityInGrams,  $totalCost);
                    break;
                case "pcs":
                    $quantityInGrams = $ingredient->cost_per_unit; 
                    $totalCost = $product->kernel;
                    $kernelCost = $this->calculateCostForPcs($quantityInGrams,  $totalCost);
                    break;
            }
            $realCost["type"]  = $product->type;
            $realCost["name"]  = $ingredient->name;
            $realCost["quantity"]  = $product_kernel_ingredient->quantity;
            $realCost["kernel_quantity"]  = $product->kernel;
            $realCost["kernel_cost"]  =  $kernelCost;
            $realCost["total_cost"]  =  $kernelCost;
            $realCost["pcs_cost"]  = ($product->kernel * $kernelCost) / 1000;

            return $realCost;
        }
    }

    public function getProductCoreCost($product,$ingredient){
        $realCost = [];
        $product_main_ingredient = ProductIngredients::where('product_id',$product->id)->where("ingredient_id",$ingredient->id)->where("type",Product::TYPE_MAIN)->first();
        if($product_main_ingredient){
            switch ($ingredient->unit) {
                    case "kg":
                        $quantityInGrams = $ingredient->cost_per_unit; 
                        $totalCost = $product_main_ingredient->quantity;
                        $coreCost = $this->calculateCost($quantityInGrams,  $totalCost);
                        break;
                    case "L":
                        $quantityInGrams = $ingredient->cost_per_unit; 
                        $totalCost = $product->kernel;
                        $coreCost = $this->calculateCost($quantityInGrams,  $totalCost);
                        break;
                    case "pcs":
                        $quantityInGrams = $ingredient->cost_per_unit; 
                        $totalCost = $product->kernel;
                        $coreCost = $this->calculateCostForPcs($quantityInGrams,  $totalCost);
                        break;
                }
                
                $realCost["type"]  = $product->type;
                $realCost["name"]  = $ingredient->name;
                $realCost["quantity"]  = $product_main_ingredient->quantity ?? 0;
                $realCost["core_cost"]  =  $coreCost;
                $realCost["pcs_cost"]  = ($product->kernel * $coreCost) / 1000;
    
                return $realCost;
        }
    }

    public function getProductDoughCost($product,$ingredient){
        $realCost = [];
        $product_dough_ingredient = DoughIngredients::where('product_id',$product->id)->where("ingredient_id",$ingredient->id)->first();
        switch ($ingredient->unit) {
                case "kg":
                    $_ost_per_kg = $ingredient->cost_per_unit; 
                    $dought_cost = $product_dough_ingredient->quantity;
                    $doughCost = $this->calculateCost($_ost_per_kg,  $dought_cost);
                    break;
                case "L":
                    $_ost_per_kg = $ingredient->cost_per_unit; 
                    $totalCost = $product_dough_ingredient->quantity;
                    $doughCost = $this->calculateCost($_ost_per_kg,  $totalCost);
                    break;
                case "pcs":
                    $_ost_per_kg = $ingredient->cost_per_unit; 
                    $totalCost = $product->kernel;
                    $doughCost = $this->calculateCostForPcs($_ost_per_kg,  $totalCost);
                    break;
            }
            $realCost["dought_quantity"]  = $product->dough;
            $realCost["type"]  = $product->type;
            $realCost["name"]  = $ingredient->name;
            $realCost["quantity"]  = $product_dough_ingredient->quantity;
            $realCost["dough_cost"] =  $doughCost;
            $realCost["pcs_cost"]  = ($product_dough_ingredient->quantity * $_ost_per_kg) / 1000;

            return $realCost;
    }
}
