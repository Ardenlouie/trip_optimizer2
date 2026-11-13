<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class SalesOrderProduct extends Model
{
    protected $connection = 'mysql2';
    protected $table='sales_order_products';

    protected $fillable = [
        'sales_order_id',
        'product_id',
        'part',
        'total_quantity',
        'total_sales',
    ];

    public function order_products() {
        return $this->hasMany('App\Models\SalesOrderProduct');
    }
}
