<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'module',
        'record_id',
        'description',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'login' => 'เข้าสู่ระบบ (Login)',
            'logout' => 'ออกจากระบบ (Logout)',
            'create' => 'เพิ่มข้อมูล (Create)',
            'update' => 'แก้ไขข้อมูล (Update)',
            'delete' => 'ลบข้อมูล (Delete)',
            'import' => 'นำเข้าไฟล์ (Import)',
            'export' => 'ส่งออกข้อมูล (Export)',
            'rollback' => 'ยกเลิกชุดข้อมูล (Rollback)',
            default => $this->action,
        };
    }

    public function getActionBadgeClassAttribute(): string
    {
        return match ($this->action) {
            'login' => 'bg-info text-dark',
            'logout' => 'bg-secondary',
            'create' => 'bg-success',
            'update' => 'bg-warning text-dark',
            'delete' => 'bg-danger',
            'import' => 'bg-primary',
            'export' => 'bg-dark',
            'rollback' => 'bg-danger',
            default => 'bg-light text-dark',
        };
    }

    public function getModuleLabelAttribute(): string
    {
        return match ($this->module) {
            'auth' => 'ระบบเข้าสู่ระบบ',
            'departments' => 'จัดการแผนก',
            'service_types' => 'ประเภทบริการ',
            'visit_statistics' => 'ข้อมูลสถิติการรับบริการ',
            'imports' => 'การนำเข้าไฟล์',
            'reports' => 'รายงาน',
            'users' => 'จัดการผู้ใช้งาน',
            'profile' => 'โปรไฟล์',
            default => $this->module,
        };
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['module'])) {
            $query->where('module', $filters['module']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}

