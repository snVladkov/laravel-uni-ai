<?php

use App\Models\User;

it('shows the login page to guests', function () {
    $this->get('/login')->assertOk()->assertSee(__('auth.login'));
});

it('authenticates a user with valid credentials', function () {
    $user = User::factory()->create(['password' => 'secret-password']);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'secret-password',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create(['password' => 'secret-password']);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('logs an authenticated user out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect('/login');

    $this->assertGuest();
});

it('redirects guests from the dashboard to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

it('redirects authenticated users away from the login page', function () {
    $this->actingAs(User::factory()->create())
        ->get('/login')
        ->assertRedirect('/dashboard');
});

it('redirects the root path based on authentication state', function () {
    $this->get('/')->assertRedirect('/login');

    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertRedirect('/dashboard');
});
