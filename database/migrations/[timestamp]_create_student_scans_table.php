<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_scans', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->string('qr_content');
            $table->timestamp('scanned_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_scans');
    }
}; 