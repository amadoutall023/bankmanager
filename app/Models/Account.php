<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['client_id','account_number','type','balance','status','blocked_at','blocking_expires_at','blocking_reason','is_archived','archived_at'];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
            if (!$model->account_number) {
                $model->account_number = 'ACC'.mt_rand(10000000, 99999999);
            }
        });
    }

    // Relations
    public function client() { return $this->belongsTo(Client::class); }
    public function transactions() { return $this->hasMany(Transaction::class); }

    // Scopes
    public function scopeActive($query) {
        return $query->where('status', 'active');
    }

    public function scopeNotDeleted($query) {
        return $query->whereNull('deleted_at');
    }

    public function scopeByNumber($query, $number) {
        return $query->where('account_number', $number);
    }

    public function scopeByClient($query, $clientId) {
        return $query->where('client_id', $clientId);
    }

    public function scopeBlocked($query) {
        return $query->where('status', 'inactive');
    }

    public function scopeExpiredBlocking($query) {
        return $query->where('blocking_expires_at', '<=', now())
                    ->whereNotNull('blocking_expires_at');
    }

    public function scopeNotArchived($query) {
        return $query->where('is_archived', false);
    }
}
