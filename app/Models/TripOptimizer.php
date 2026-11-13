<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripOptimizer extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

    protected $casts = [
        'sales_orders' => 'array', 
        'truck_ids' => 'array', 
        'approver' => 'array', 
    ];

    protected $fillable = [
        'user_id',
        'company_id',
        'control_number',
        'sales_orders',
        'truck_ids',
        'total_quantity',
        'total_weight',
        'total_volume',
        'total_pallets',
        'approver',
        'date_submitted',
        'status',
        'ship_date',
        'warehouse',
        'ship_address',
        'total_price',
        'remarks',
    ];

    public function trip_optimizer_item() {
        return $this->hasMany('App\Models\TripOptimizerItem', 'trip_optimizer_id', 'id');
    }

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function company() {
        return $this->belongsTo('App\Models\Company');
    }

    public function approved() {
        return $this->belongsTo('App\Models\User', 'approver', 'id');
    }

    public function hasApprover($userId)
    {
        return in_array($userId, $this->approver ?? []);
    }
}
