<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/books', function () {
    return view('books.index');
})->name('books.index');

Route::get('/books/{id}', function ($id) {
    return view('books.show', ['id' => $id]);
})->name('books.show');

Route::get('/transactions', function () {
    return view('transactions.index');
})->name('transactions.index');

Route::get('/profile', function () {
    return view('profile.edit');
})->name('profile');
