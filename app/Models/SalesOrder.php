<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class SalesOrder extends Model
{
    protected $connection = 'mysql2';
    protected $table='sales_orders';

    protected $fillable = [
        'account_login_id',
        'shipping_address_id',
        'control_number',
        'po_number',
        'paf_number',
        'reference',
        'upload_status',
        'sales_order',
        'order_date',
        'ship_date',
        'shipping_instruction',
        'ship_to_name',
        'ship_to_building',
        'ship_to_street',
        'ship_to_city',
        'ship_to_postal',
        'status',
        'total_quantity',
        'total_sales',
        'grand_total',
        'po_value'
    ];

    public function order_products() {
        return $this->hasMany('App\Models\SalesOrderProduct');
    }
}
