<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role', 20)->default('requestor')->index();
            $t->boolean('is_active')->default(true);
            $t->string('program')->nullable();
            $t->string('contact_number', 40)->nullable();
        });
        Schema::create('document_types', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('faculty', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('department')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('drawers', function (Blueprint $t) {
            $t->id();
            $t->unsignedSmallInteger('number')->unique();
            $t->string('label');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('requestor_id')->constrained('users');
            $t->foreignId('created_by')->constrained('users');
            $t->string('tracking_reference', 40)->unique();
            $t->text('purpose')->nullable();
            $t->text('notes')->nullable();
            $t->date('needed_date')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
        });
        Schema::create('request_documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('request_id')->constrained('requests');
            $t->foreignId('document_type_id')->constrained();
            $t->foreignId('drawer_id')->nullable()->constrained();
            $t->string('status', 30)->default('awaiting_dropoff')->index();
            $t->timestamp('received_at')->nullable();
            $t->timestamp('ready_at')->nullable();
            $t->timestamp('claimed_at')->nullable();
            $t->timestamps();
        });
        Schema::create('faculty_assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('request_document_id')->constrained();
            $t->foreignId('faculty_id')->constrained('faculty');
            $t->string('status', 20)->default('pending');
            $t->timestamp('routed_at')->nullable();
            $t->timestamp('returned_at')->nullable();
            $t->timestamps();
            $t->unique(['request_document_id', 'faculty_id']);
        });
        Schema::create('attachments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('request_id')->constrained('requests');
            $t->foreignId('uploaded_by')->constrained('users');
            $t->string('original_name');
            $t->string('path')->unique();
            $t->string('mime_type');
            $t->unsignedBigInteger('size');
            $t->timestamps();
        });
        Schema::create('claim_stubs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('request_id')->unique()->constrained('requests');
            $t->uuid('reference')->unique();
            $t->timestamps();
        });
        Schema::create('releases', function (Blueprint $t) {
            $t->id();
            $t->foreignId('request_id')->constrained('requests');
            $t->foreignId('released_by')->constrained('users');
            $t->string('claimant_name');
            $t->string('claimant_identifier')->nullable();
            $t->boolean('is_representative')->default(false);
            $t->string('authorization_reference')->nullable();
            $t->timestamp('acknowledged_at');
            $t->timestamps();
        });
        Schema::create('release_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('release_id')->constrained();
            $t->foreignId('request_document_id')->unique()->constrained();
            $t->foreignId('drawer_id')->nullable()->constrained();
        });
        Schema::create('document_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('request_document_id')->constrained();
            $t->foreignId('actor_id')->constrained('users');
            $t->string('action');
            $t->string('from_status')->nullable();
            $t->string('to_status');
            $t->foreignId('drawer_id')->nullable()->constrained();
            $t->text('notes')->nullable();
            $t->timestamp('created_at');
        });
        Schema::create('feedback', function (Blueprint $t) {
            $t->id();
            $t->foreignId('request_id')->unique()->constrained('requests');
            $t->unsignedTinyInteger('service_rating');
            $t->unsignedTinyInteger('processing_rating');
            $t->string('comments', 300)->nullable();
            $t->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('actor_id')->constrained('users');
            $t->string('actor_role', 20);
            $t->foreignId('request_id')->nullable()->constrained('requests');
            $t->string('action');
            $t->json('details')->nullable();
            $t->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        foreach (['audit_logs', 'feedback', 'document_events', 'release_items', 'releases', 'claim_stubs', 'attachments', 'faculty_assignments', 'request_documents', 'requests', 'drawers', 'faculty', 'document_types'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['role', 'is_active', 'program', 'contact_number']));
    }
};
