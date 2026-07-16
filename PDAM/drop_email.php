<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('users', function (Blueprint $table) {
    if (Schema::hasColumn('users', 'email')) {
        $table->dropColumn('email');
    }
    if (Schema::hasColumn('users', 'email_verified_at')) {
        $table->dropColumn('email_verified_at');
    }
});
echo "Dropped email columns\n";
