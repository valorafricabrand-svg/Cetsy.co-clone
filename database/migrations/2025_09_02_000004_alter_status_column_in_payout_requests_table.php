<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            $this->rebuildPayoutRequestsTableForSqlite(true, "TEXT NOT NULL DEFAULT 'pending'");
            return;
        }

        DB::statement("ALTER TABLE payout_requests MODIFY status VARCHAR(32) NOT NULL");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            $this->rebuildPayoutRequestsTableForSqlite(true, "TEXT CHECK (status IN ('pending','approved','rejected','paid')) NOT NULL DEFAULT 'pending'");
            return;
        }

        DB::statement("ALTER TABLE payout_requests MODIFY status VARCHAR(16) NOT NULL");
    }

    private function rebuildPayoutRequestsTableForSqlite(bool $walletNullable, string $statusColumnSql): void
    {
        $walletNullFragment = $walletNullable ? '' : ' NOT NULL';

        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement(<<<SQL
CREATE TABLE payout_requests_temp (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    wallet_id INTEGER{$walletNullFragment},
    user_id TEXT,
    amount NUMERIC(15, 2) NOT NULL,
    status {$statusColumnSql},
    meta TEXT,
    paid_at DATETIME,
    admin_reason TEXT,
    payment_method_id INTEGER,
    approved_by INTEGER,
    approved_at DATETIME,
    paid_by INTEGER,
    rejected_by INTEGER,
    rejected_at DATETIME,
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY(wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
    FOREIGN KEY(payment_method_id) REFERENCES payment_methods(id)
);
SQL
        );

        DB::statement(<<<SQL
INSERT INTO payout_requests_temp (
    id,
    wallet_id,
    user_id,
    amount,
    status,
    meta,
    paid_at,
    admin_reason,
    payment_method_id,
    approved_by,
    approved_at,
    paid_by,
    rejected_by,
    rejected_at,
    created_at,
    updated_at
)
SELECT
    id,
    wallet_id,
    user_id,
    amount,
    status,
    meta,
    paid_at,
    admin_reason,
    payment_method_id,
    approved_by,
    approved_at,
    paid_by,
    rejected_by,
    rejected_at,
    created_at,
    updated_at
FROM payout_requests;
SQL
        );

        DB::statement('DROP TABLE payout_requests');
        DB::statement('ALTER TABLE payout_requests_temp RENAME TO payout_requests');

        DB::statement('CREATE INDEX payout_requests_wallet_id_index ON payout_requests (wallet_id)');
        DB::statement('CREATE INDEX payout_requests_payment_method_id_index ON payout_requests (payment_method_id)');

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
