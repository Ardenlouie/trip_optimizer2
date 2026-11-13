<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripOptimizerItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

    protected $fillable = [
        'trip_optimizer_id',
        'sales_order',
        'stock_code',
        'item_description',
        'uom',
        'quantity',
        'weight',   
        'volume',
        'pallet',
        'convert_factor',
    ];

    public function trip_optimizer() {
        return $this->belongsTo('App\Models\TripOptimizer', 'trip_optimizer_id', 'id');
    }
}
