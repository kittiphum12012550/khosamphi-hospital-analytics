<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'service_type_id',
        'service_date',
        'service_hour',
        'visit_count',
        'source_type',
        'batch_id',
        'source_row_number',
        'created_by',
        'updated_by',
        'notes',
    ];

    protected $casts = [
        'service_date' => 'date',
        'service_hour' => 'integer',
        'visit_count' => 'integer',
        'source_row_number' => 'integer',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'batch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getFormattedHourAttribute(): string
    {
        $start = sprintf('%02d:00', $this->service_hour);
        $end = sprintf('%02d:59', $this->service_hour);
        return "{$start} - {$end} น.";
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source_type) {
            'manual' => 'บันทึกด้วยมือ',
            'excel_import' => 'นำเข้า Excel',
            'csv_import' => 'นำเข้า CSV',
            'api' => 'HIS API',
            default => $this->source_type,
        };
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (!empty($filters['start_date'])) {
            $query->whereDate('service_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('service_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['service_type_id'])) {
            $query->where('service_type_id', $filters['service_type_id']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('service_date', $filters['year']);
        }

        if (!empty($filters['month'])) {
            $query->whereMonth('service_date', $filters['month']);
        }

        return $query;
    }
}

