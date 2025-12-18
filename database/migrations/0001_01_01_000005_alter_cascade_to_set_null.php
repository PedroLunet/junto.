<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE messages DROP CONSTRAINT messages_senderid_fkey');
        DB::statement('ALTER TABLE messages DROP CONSTRAINT messages_receiverid_fkey');
        DB::statement('ALTER TABLE messages ADD CONSTRAINT messages_senderid_fkey FOREIGN KEY (senderId) REFERENCES users(id) ON DELETE SET NULL');
        DB::statement('ALTER TABLE messages ADD CONSTRAINT messages_receiverid_fkey FOREIGN KEY (receiverId) REFERENCES users(id) ON DELETE SET NULL');

        DB::statement('ALTER TABLE notification DROP CONSTRAINT notification_receiverid_fkey');
        DB::statement('ALTER TABLE notification ADD CONSTRAINT notification_receiverid_fkey FOREIGN KEY (receiverId) REFERENCES users(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE messages DROP CONSTRAINT messages_senderid_fkey');
        DB::statement('ALTER TABLE messages DROP CONSTRAINT messages_receiverid_fkey');
        DB::statement('ALTER TABLE messages ADD CONSTRAINT messages_senderid_fkey FOREIGN KEY (senderId) REFERENCES users(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE messages ADD CONSTRAINT messages_receiverid_fkey FOREIGN KEY (receiverId) REFERENCES users(id) ON DELETE CASCADE');

        DB::statement('ALTER TABLE notification DROP CONSTRAINT notification_receiverid_fkey');
        DB::statement('ALTER TABLE notification ADD CONSTRAINT notification_receiverid_fkey FOREIGN KEY (receiverId) REFERENCES users(id) ON DELETE CASCADE');
    }
};
