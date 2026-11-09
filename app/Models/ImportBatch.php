<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_code',
        'original_filename',
        'file_path',
        'file_hash',
        'file_type',
        'file_size_bytes',
        'total_rows',
        'success_rows',
        'error_rows',
        'status',
        'error_summary',
        'imported_by',
        'rolled_back_by',
        'rolled_back_at',
        'notes',
    ];

    protected $casts = [
        'error_summary' => 'array',
        'rolled_back_at' => 'datetime',
        'total_rows' => 'integer',
        'success_rows' => 'integer',
        'error_rows' => 'integer',
        'file_size_bytes' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function rollbackUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rolled_back_by');
    }

    public function visitStatistics(): HasMany
    {
        return $this->hasMany(VisitStatistic::class, 'batch_id');
    }

    public function canRollback(): bool
    {
        return $this->status === 'completed' && is_null($this->rolled_back_at);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'รอดำเนินการ (Pending)',
            'processing' => 'กำลังประมวลผล (Processing)',
            'completed' => 'สำเร็จสมบูรณ์ (Completed)',
            'failed' => 'เกิดข้อผิดพลาด (Failed)',
            'rolled_back' => 'ยกเลิก/ย้อนกลับแล้ว (Rolled Back)',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-warning text-dark',
            'processing' => 'bg-info text-dark',
            'completed' => 'bg-success',
            'failed' => 'bg-danger',
            'rolled_back' => 'bg-secondary',
            default => 'bg-light text-dark',
        };
    }
}

