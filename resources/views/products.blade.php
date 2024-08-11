
@extends('layouts.app')

@section('title', 'Home Page')

@section('content')
    <a href="#" class="btn download-report" download>Բեռնել Հաշվետվությունը</a>
    <div class= "table-section">
        <table id="products-table" class="table table-bordered table-striped" style="width:100%">
            <thead>
                <tr>
                    <th>Հ/Հ</th>
                    <th>ԱՆՎԱՆՈՒՄ</th>
                    <th>ԱՐՏԱԴՐՎԱԾ ՔԱՆԱԿ</th>
                    <th>ՎԱՃԱՌՎԱԾ ՔԱՆԱԿ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr class="product-row" data-key="{{ $product->id }}">
                        <td>{{ $product->id }}</td>
                        <td>{{ $product->name }}</td>
                        <td><input type="number" class="products-input" name="manufactured_quantity" default = "0"></td>
                        <td><input type="number" class="products-input" name="sold_quantity" default = "0"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class = "errors">

        </div>
        <button class="btn btn-get-report">Ստանալ Հաշվետվություն</button>
    </div>
    <div class="warning-section">
        <p>
            Խնդրում ենք լրացնել բոլոր բաց թողնված դաշտերը
        </p>
    </div>
@endsection
