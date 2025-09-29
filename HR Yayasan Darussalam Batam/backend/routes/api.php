<?php

use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Route;
use App\Models\User;

Route::get('/users', function () {
    return UserResource::collection(User::all());
});