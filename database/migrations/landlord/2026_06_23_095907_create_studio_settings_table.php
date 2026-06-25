<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studio_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_id')->constrained()->onDelete('cascade')->unique();

            // Identity
            $table->string('studio_name');
            $table->string('tagline')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('favicon_url')->nullable();
            $table->string('hero_image_url')->nullable();

            // Colors
            $table->string('color_primary')->default('#E50914');
            $table->string('color_secondary')->default('#1F2833');
            $table->string('color_accent')->default('#C9A84C');
            $table->string('color_text_dark')->default('#0B0C10');
            $table->string('color_text_light')->default('#F8F9FA');
            $table->string('color_background')->default('#FFFFFF');

            // Typography (Google Fonts)
            $table->string('font_heading')->default('Plus Jakarta Sans');
            $table->string('font_body')->default('Inter');

            // Contact
            $table->string('phone_primary');
            $table->string('phone_secondary')->nullable();
            $table->string('email')->nullable();
            $table->string('instagram_handle')->nullable();
            $table->text('address');
            $table->string('city');
            $table->string('whatsapp_number');

            // Business rules (per studio — all configurable)
            $table->integer('late_arrival_fee')->default(5000);
            $table->integer('rescheduling_fee')->default(5000);
            $table->integer('extra_edit_fee')->default(5000);
            $table->integer('sla_working_days')->default(5);
            $table->integer('max_sessions_per_day')->default(3);
            $table->json('slot_times')->nullable();
            $table->integer('slot_grace_card')->default(10);
            $table->integer('slot_grace_bank_transfer')->default(25);
            $table->integer('slot_grace_ussd')->default(15);

            // Bank details (shown at checkout)
            $table->string('bank_name');
            $table->string('account_number');
            $table->string('account_name');

            // Paystack — studio's OWN keys, money goes directly to them
            $table->string('paystack_public_key')->nullable();
            $table->string('paystack_secret_key')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_settings');
    }
};