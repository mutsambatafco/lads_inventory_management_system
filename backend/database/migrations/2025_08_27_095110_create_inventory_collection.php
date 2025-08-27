<?php

use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'mongodb';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migration for the 'activity_logs' collection
        Schema::create('activity_logs', function (Blueprint $collection) {
            // These lines define indexes for fields often used in queries
            $collection->index('user_id');
            $collection->index('action');
            $collection->index('model_type');
            $collection->index('model_id');
            
            // This index is crucial for sorting logs by creation time
            $collection->index('created_at');
        });

        // Migration for the 'inventory_audits' collection
        Schema::create('inventory_audits', function (Blueprint $collection) {
            // Define indexes for common search fields
            $collection->index('product_id');
            $collection->index('action');
            $collection->index('user_id');

            // Index for sorting and range queries on a numeric field
            $collection->index('change');
            
            // Index for sorting by creation time
            $collection->index('created_at');
        });

        // Migration for the 'system_logs' collection
        Schema::create('system_logs', function (Blueprint $collection) {
            // Define indexes for fields used in log queries
            $collection->index('level');
            $collection->index('channel');
            
            // Index for filtering by user if they are logged in
            $collection->index('user_id');

            // Index for sorting logs by creation time
            $collection->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('inventory_audits');
        Schema::dropIfExists('system_logs');
    }
};

