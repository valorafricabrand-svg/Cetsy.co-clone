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
            $this->rebuildDisputesTableForSqlite(
                ['pending', 'under_review', 'resolved', 'appealed', 'appeal_under_review', 'final', 'mutually_resolved'],
                ['buyer_wins', 'seller_wins', 'partial_refund', 'no_action', 'mutual_agreement']
            );

            return;
        }

        // MySQL / MariaDB alter statements
        DB::statement("ALTER TABLE disputes MODIFY COLUMN status ENUM('pending', 'under_review', 'resolved', 'appealed', 'appeal_under_review', 'final', 'mutually_resolved')");
        DB::statement("ALTER TABLE disputes MODIFY COLUMN decision ENUM('buyer_wins', 'seller_wins', 'partial_refund', 'no_action', 'mutual_agreement')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->rebuildDisputesTableForSqlite(
                ['pending', 'under_review', 'resolved', 'appealed', 'appeal_under_review', 'final'],
                ['buyer_wins', 'seller_wins', 'partial_refund', 'no_action']
            );

            return;
        }

        DB::statement("ALTER TABLE disputes MODIFY COLUMN status ENUM('pending', 'under_review', 'resolved', 'appealed', 'appeal_under_review', 'final')");
        DB::statement("ALTER TABLE disputes MODIFY COLUMN decision ENUM('buyer_wins', 'seller_wins', 'partial_refund', 'no_action')");
    }

    private function rebuildDisputesTableForSqlite(array $statusValues, array $decisionValues): void
    {
        $statusList = "'" . implode("','", $statusValues) . "'";
        $decisionList = "'" . implode("','", $decisionValues) . "'";

        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement(<<<SQL
CREATE TABLE disputes_temp (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    buyer_id INTEGER NOT NULL,
    seller_id INTEGER NOT NULL,
    type TEXT CHECK (type IN ('customs_fees', 'item_misrepresentation', 'shipping_issues', 'quality_issues', 'payment_issues', 'other')) NOT NULL,
    status TEXT CHECK (status IN ({$statusList})) NOT NULL,
    description TEXT NOT NULL,
    evidence TEXT,
    resolution TEXT,
    resolved_by INTEGER,
    resolved_at DATETIME,
    appeal_deadline DATETIME,
    can_appeal INTEGER NOT NULL DEFAULT 1,
    decision TEXT CHECK (decision IN ({$decisionList}) OR decision IS NULL),
    refund_amount NUMERIC(10, 2),
    admin_notes TEXT,
    mutual_resolution_terms TEXT,
    buyer_agreed_at DATETIME,
    seller_agreed_at DATETIME,
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY(buyer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY(seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY(resolved_by) REFERENCES users(id) ON DELETE SET NULL
);
SQL
        );

        DB::statement(<<<SQL
INSERT INTO disputes_temp (
    id,
    order_id,
    buyer_id,
    seller_id,
    type,
    status,
    description,
    evidence,
    resolution,
    resolved_by,
    resolved_at,
    appeal_deadline,
    can_appeal,
    decision,
    refund_amount,
    admin_notes,
    mutual_resolution_terms,
    buyer_agreed_at,
    seller_agreed_at,
    created_at,
    updated_at
)
SELECT
    id,
    order_id,
    buyer_id,
    seller_id,
    type,
    status,
    description,
    evidence,
    resolution,
    resolved_by,
    resolved_at,
    appeal_deadline,
    can_appeal,
    decision,
    refund_amount,
    admin_notes,
    COALESCE(mutual_resolution_terms, NULL),
    buyer_agreed_at,
    seller_agreed_at,
    created_at,
    updated_at
FROM disputes;
SQL
        );

        DB::statement('DROP TABLE disputes');
        DB::statement('ALTER TABLE disputes_temp RENAME TO disputes');

        DB::statement('CREATE INDEX disputes_order_id_status_index ON disputes (order_id, status)');
        DB::statement('CREATE INDEX disputes_buyer_id_status_index ON disputes (buyer_id, status)');
        DB::statement('CREATE INDEX disputes_seller_id_status_index ON disputes (seller_id, status)');
        DB::statement('CREATE INDEX disputes_appeal_deadline_index ON disputes (appeal_deadline)');

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
