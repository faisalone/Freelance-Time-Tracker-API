<?php

namespace App\Models;

use App\Events\TimeLogUpdated;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeLog extends Model
{
    /** @use HasFactory<\Database\Factories\TimeLogFactory> */
    use HasFactory;

    protected $fillable = [
        'project_id',
        'start_time',
        'end_time',
        'description',
        'hours',
        'is_billable',
        'tags',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'hours' => 'decimal:2',
        'is_billable' => 'boolean',
        'tags' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the project that owns the time log.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Calculate and set hours when end_time is set.
     */
    public function calculateHours(): void
    {
        if ($this->start_time && $this->end_time) {
            $start = Carbon::parse($this->start_time);
            $end = Carbon::parse($this->end_time);
            $this->hours = $end->diffInMinutes($start) / 60;
        }
    }

    /**
     * Stop the time log and calculate hours.
     */
    public function stop(): void
    {
        $this->end_time = now();
        $this->calculateHours();
        $this->save();
    }

    /**
     * Check if the time log is currently running.
     */
    public function isRunning(): bool
    {
        return $this->start_time && !$this->end_time;
    }

    /**
     * Scope a query to only include running time logs.
     */
    public function scopeRunning($query)
    {
        return $query->whereNotNull('start_time')->whereNull('end_time');
    }

    /**
     * Scope a query to only include billable time logs.
     */
    public function scopeBillable($query)
    {
        return $query->where('is_billable', true);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('start_time', [$from, $to]);
    }

    protected static function booted(): void
    {
        static::saved(function (TimeLog $timeLog) {
            // Only dispatch event if the time log has hours calculated
            if ($timeLog->hours && $timeLog->wasChanged('hours')) {
                TimeLogUpdated::dispatch($timeLog);
            }
        });
    }
}
