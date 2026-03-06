<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class EvidenceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'appeal_id', 'dispute_id', 'requested_from', 'requested_by',
        'user_id', 'party_type', 'message', 'request_message',
        'status', 'deadline', 'responded_at', 'submitted_at',
        'required_evidence_types', 'response_notes', 'admin_notes',
        'submitted_evidence'
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'responded_at' => 'datetime',
        'submitted_at' => 'datetime',
        'required_evidence_types' => 'array',
        'submitted_evidence' => 'array',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_RESPONDED = 'responded';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_REVIEWED = 'reviewed';
    const STATUS_CLOSED = 'closed';

    protected static array $columnCache = [];

    // Relationships
    public function appeal(): BelongsTo
    {
        return $this->belongsTo(Appeal::class);
    }

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class);
    }

    public function requestedFrom(): BelongsTo
    {
        return $this->belongsTo(User::class, static::recipientColumn());
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, static::requesterColumn() ?? static::recipientColumn());
    }

    // Scopes
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING)
                    ->where('deadline', '<', now());
    }

    public function scopeForRecipient(Builder $query, int $userId): Builder
    {
        return $query->where(static::recipientColumn(), $userId);
    }

    public function scopeForDispute(Builder $query, int $disputeId): Builder
    {
        if (static::hasColumn('dispute_id')) {
            return $query->where('dispute_id', $disputeId);
        }

        return $query->whereHas('appeal', function (Builder $appealQuery) use ($disputeId) {
            $appealQuery->where('dispute_id', $disputeId);
        });
    }

    // Methods
    public function isOverdue(): bool
    {
        return $this->deadline && $this->deadline->isPast();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSubmitted(): bool
    {
        return in_array((string) $this->status, [self::STATUS_SUBMITTED, self::STATUS_RESPONDED], true);
    }

    public function isDeadlineExpired(): bool
    {
        return $this->isOverdue();
    }

    public function markAsResponded(): void
    {
        $this->update([
            'status' => static::submissionStatusValue(),
            static::submittedAtColumn() => now(),
        ]);
    }

    public function markAsSubmitted(string $description, array $files, ?string $additionalNotes = null): void
    {
        $submittedEvidence = [
            'description' => $description,
            'files' => $files,
        ];

        if ($additionalNotes !== null && trim($additionalNotes) !== '') {
            $submittedEvidence['additional_notes'] = $additionalNotes;
        }

        $payload = [
            'status' => static::submissionStatusValue(),
            'submitted_evidence' => $submittedEvidence,
            static::submittedAtColumn() => now(),
        ];

        if (static::hasColumn('response_notes')) {
            $payload['response_notes'] = $description;
        }

        $this->update($payload);
    }

    public function markAsOverdue(): void
    {
        $this->update(['status' => self::STATUS_OVERDUE]);
    }

    public function getDeadlineDaysLeft(): int
    {
        if (!$this->deadline) {
            return 0;
        }
        return max(0, Carbon::now()->diffInDays($this->deadline, false));
    }

    public function getDaysUntilDeadline(): int
    {
        return $this->getDeadlineDaysLeft();
    }

    public function getRequiredEvidenceTypesList(): array
    {
        $types = $this->required_evidence_types ?? [];

        if (!is_array($types) || empty($types)) {
            return [];
        }

        return collect($types)
            ->filter(fn ($type) => is_string($type) && trim($type) !== '')
            ->map(fn (string $type) => ucwords(str_replace('_', ' ', trim($type))))
            ->values()
            ->all();
    }

    public function getPartyTypeLabel(): string
    {
        $partyType = trim((string) ($this->attributes['party_type'] ?? ''));
        if ($partyType !== '') {
            return ucfirst($partyType);
        }

        $dispute = $this->appeal?->dispute;
        $recipientId = $this->recipientUserId();
        if ($dispute && $recipientId !== null) {
            if ((int) $dispute->buyer_id === $recipientId) {
                return 'Buyer';
            }

            if ((int) $dispute->seller_id === $recipientId) {
                return 'Seller';
            }
        }

        return 'Party';
    }

    public function getStatusLabel(): string
    {
        return match ((string) $this->status) {
            self::STATUS_SUBMITTED, self::STATUS_RESPONDED => 'Submitted',
            self::STATUS_OVERDUE => 'Overdue',
            self::STATUS_REVIEWED => 'Reviewed',
            self::STATUS_CLOSED => 'Closed',
            default => 'Pending',
        };
    }

    public function recipientUserId(): ?int
    {
        $value = $this->attributes['requested_from'] ?? $this->attributes['user_id'] ?? null;

        if ($value === null) {
            return null;
        }

        return (int) $value;
    }

    public function isOwnedBy(?int $userId): bool
    {
        return $userId !== null && $this->recipientUserId() === (int) $userId;
    }

    public function resolveDisputeId(): ?int
    {
        $value = $this->attributes['dispute_id'] ?? $this->appeal?->dispute_id ?? null;

        if ($value === null) {
            return null;
        }

        return (int) $value;
    }

    public function getStatusBadgeClass(): string
    {
        return match((string) $this->status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_SUBMITTED, self::STATUS_RESPONDED => 'badge-success',
            self::STATUS_OVERDUE => 'badge-danger',
            self::STATUS_REVIEWED, self::STATUS_CLOSED => 'badge-secondary',
            default => 'badge-light'
        };
    }

    public function getRequestMessageAttribute($value): string
    {
        return (string) ($value ?? $this->attributes['message'] ?? '');
    }

    public function getMessageAttribute($value): string
    {
        return (string) ($value ?? $this->attributes['request_message'] ?? '');
    }

    public function getRequestedFromAttribute($value): ?int
    {
        $resolved = $value ?? $this->attributes['user_id'] ?? null;

        return $resolved === null ? null : (int) $resolved;
    }

    public function getUserIdAttribute($value): ?int
    {
        $resolved = $value ?? $this->attributes['requested_from'] ?? null;

        return $resolved === null ? null : (int) $resolved;
    }

    public function getSubmittedAtAttribute($value)
    {
        $resolved = $value ?? $this->attributes['responded_at'] ?? null;

        return $resolved ? $this->asDateTime($resolved) : null;
    }

    public function getRespondedAtAttribute($value)
    {
        $resolved = $value ?? $this->attributes['submitted_at'] ?? null;

        return $resolved ? $this->asDateTime($resolved) : null;
    }

    public static function hasColumn(string $column): bool
    {
        if (array_key_exists($column, static::$columnCache)) {
            return static::$columnCache[$column];
        }

        try {
            static::$columnCache[$column] = Schema::hasTable('evidence_requests')
                && Schema::hasColumn('evidence_requests', $column);
        } catch (\Throwable $e) {
            static::$columnCache[$column] = false;
        }

        return static::$columnCache[$column];
    }

    public static function usesLegacySchema(): bool
    {
        return static::hasColumn('user_id') && static::hasColumn('request_message');
    }

    public static function recipientColumn(): string
    {
        return static::hasColumn('requested_from') ? 'requested_from' : 'user_id';
    }

    public static function requesterColumn(): ?string
    {
        return static::hasColumn('requested_by') ? 'requested_by' : null;
    }

    public static function submittedAtColumn(): string
    {
        return static::hasColumn('responded_at') ? 'responded_at' : 'submitted_at';
    }

    public static function submissionStatusValue(): string
    {
        return static::usesLegacySchema() ? static::STATUS_SUBMITTED : static::STATUS_RESPONDED;
    }

    public static function createForRecipient(array $attributes): self
    {
        $payload = [
            'appeal_id' => $attributes['appeal_id'] ?? null,
            'status' => $attributes['status'] ?? self::STATUS_PENDING,
            'deadline' => $attributes['deadline'] ?? null,
            'required_evidence_types' => $attributes['required_evidence_types'] ?? null,
            'submitted_evidence' => $attributes['submitted_evidence'] ?? null,
        ];

        if (static::hasColumn('dispute_id') && array_key_exists('dispute_id', $attributes)) {
            $payload['dispute_id'] = $attributes['dispute_id'];
        }

        $recipientId = $attributes['recipient_id'] ?? $attributes['requested_from'] ?? $attributes['user_id'] ?? null;
        if (static::hasColumn('requested_from')) {
            $payload['requested_from'] = $recipientId;
        } else {
            $payload['user_id'] = $recipientId;
        }

        if (static::hasColumn('requested_by')) {
            $payload['requested_by'] = $attributes['requester_id'] ?? $attributes['requested_by'] ?? null;
        }

        if (static::hasColumn('party_type')) {
            $payload['party_type'] = $attributes['party_type'] ?? null;
        }

        $message = $attributes['message'] ?? $attributes['request_message'] ?? '';
        if (static::hasColumn('message')) {
            $payload['message'] = $message;
        } else {
            $payload['request_message'] = $message;
        }

        if (static::hasColumn('response_notes') && array_key_exists('response_notes', $attributes)) {
            $payload['response_notes'] = $attributes['response_notes'];
        }

        if (array_key_exists('submitted_at', $attributes) || array_key_exists('responded_at', $attributes)) {
            $payload[static::submittedAtColumn()] = $attributes['submitted_at'] ?? $attributes['responded_at'];
        }

        return static::create($payload);
    }
}
