<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->rebuildAppealsTableForSqlite(['pending', 'evidence_requested', 'approved', 'rejected']);
            return;
        }

        DB::statement("ALTER TABLE appeals MODIFY COLUMN status ENUM('pending', 'evidence_requested', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->rebuildAppealsTableForSqlite(['pending', 'under_review', 'approved', 'rejected']);
            return;
        }

        DB::statement("ALTER TABLE appeals MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }

    private function rebuildAppealsTableForSqlite(array $statusValues): void
    {
        $statusList = "'" . implode("','", $statusValues) . "'";

        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement(<<<SQL
CREATE TABLE appeals_temp (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    dispute_id INTEGER NOT NULL,
    appealed_by INTEGER NOT NULL,
    reason TEXT NOT NULL,
    new_evidence TEXT,
    status TEXT CHECK (status IN ({$statusList})) NOT NULL DEFAULT 'pending',
    reviewed_by INTEGER,
    reviewed_at DATETIME,
    decision TEXT CHECK (decision IN ('approved', 'rejected') OR decision IS NULL),
    review_notes TEXT,
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY(dispute_id) REFERENCES disputes(id) ON DELETE CASCADE,
    FOREIGN KEY(appealed_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY(reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);
SQL
        );

        DB::statement(<<<SQL
INSERT INTO appeals_temp (
    id,
    dispute_id,
    appealed_by,
    reason,
    new_evidence,
    status,
    reviewed_by,
    reviewed_at,
    decision,
    review_notes,
    created_at,
    updated_at
)
SELECT
    id,
    dispute_id,
    appealed_by,
    reason,
    new_evidence,
    CASE
        WHEN status = 'under_review' AND 'under_review' NOT IN ({$statusList}) THEN 'evidence_requested'
        WHEN status = 'evidence_requested' AND 'evidence_requested' NOT IN ({$statusList}) THEN 'under_review'
        ELSE status
    END AS status,
    reviewed_by,
    reviewed_at,
    decision,
    review_notes,
    created_at,
    updated_at
FROM appeals;
SQL
        );

        DB::statement('DROP TABLE appeals');
        DB::statement('ALTER TABLE appeals_temp RENAME TO appeals');

        DB::statement('CREATE INDEX appeals_dispute_id_status_index ON appeals (dispute_id, status)');
        DB::statement('CREATE INDEX appeals_appealed_by_status_index ON appeals (appealed_by, status)');
        DB::statement('CREATE INDEX appeals_status_index ON appeals (status)');

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
