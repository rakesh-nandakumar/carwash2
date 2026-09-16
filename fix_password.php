<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\CurrentContext;

$context = $app->make(CurrentContext::class);

// List all users first
echo "Listing all users:\n";
$users = $context->runWithoutTenant(function () {
    return User::all(['id', 'email', 'name']);
});

foreach ($users as $user) {
    echo "ID: {$user->id}, Email: {$user->email}, Name: {$user->name}\n";
}

echo "\n";

// Find the user by email (you can change this to match your email)
$email = readline("Enter the email of the user to update password for: ");

$user = $context->runWithoutTenant(function () use ($email) {
    return User::where('email', $email)->first();
});

if (!$user) {
    echo "User not found with email: {$email}\n";
    exit(1);
}

// Hash the password with Bcrypt
$hashedPassword = Hash::make('password');

// Update the user's password
$context->runWithoutTenant(function () use ($user, $hashedPassword) {
    $user->password = $hashedPassword;
    $user->save();
});

echo "Password has been updated and hashed for user: {$user->email}\n";
echo "You can now login with email: {$user->email} and password: password\n";
