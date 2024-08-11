<?php

namespace App\Http\Exports;

use Illuminate\Http\Request;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductReportExport implements FromArray, WithHeadings{

    protected $data;
    protected $resData;
    protected $exportData;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        foreach($this->data as $index => $product){

            $this->exportData = [];
            $individual_price = 0;
            $dough_price = 0;
            $kernel_price = 0;

            foreach($product['productCost']['dough'] as $key =>  $dough){
                $dough_price += $dough['dough_cost'];
            }
            $dough_price = ($dough_price * $dough['dought_quantity']) / 1000 ;

            foreach($product['productCost']['core'] as $core){
                $individual_price += $core['core_cost'];
            }

            foreach($product['productCost']['kernel'] as $kernel){
                $kernel_price += $kernel['kernel_cost'];
            }

            $kernel_price = isset($kernel) ?  ($kernel_price * $kernel['kernel_quantity']) / 1000  :  0;

            $individual_price = $individual_price + $kernel_price;
            $individual_price = $individual_price + $dough_price;

            $this->exportData[$index]['id'] = trim($product['id']);
            $this->exportData[$index]['name'] = trim($product['product_name']);
            $this->exportData[$index]['price'] = round($product['price'], 2) . " Դրամ";
            $this->exportData[$index]['individual_price'] = round($individual_price, 2) . " Դրամ";
            $this->exportData[$index]['manufactured_quantity'] = $product['manufactured_quantity'];
            $this->exportData[$index]['sold_quantity'] = $product['sold_quantity'];
            $this->exportData[$index]['unsold_quantity'] = $product['unsold_quantity'];
            $this->exportData[$index]['revenue'] = round($product['revenue'], 2) . " Դրամ";
            $this->exportData[$index]['loss_cost'] = round($product['loss_cost'], 2) . " Դրամ";
            $this->exportData[$index]['profit'] = round($product['profit'], 2) . " Դրամ";
            $this->exportData[$index]['date'] = $product['date'];
            $this->resData[] = $this->exportData;
        }
        return $this->resData;
    }

    public function headings(): array
    {
        return [
            'Հ/Հ',
            'ԱՆՎԱՆՈՒՄ',
            'ՎԱՃԱՌՔԻ ԳԻՆ',
            'ԻՆՔՆԱՐԺԵՔ',
            'ԱՐԴԱԴՐՎԱԾ ՔԱՆԱԿ',
            'ՎԱՃԱՌՎԱԾ ՔԱՆԱԿ',
            'ՉՎԱՃԱՌՎԱԾ ՔԱՆԱԿ',
            'ԵԿԱՄՈՒՏ',
            'ԿՈՐՈՒՍՏ',
            'Շահույթ',
            'ԱՄՍԱԹԻՎ',
        ];
    }
}
