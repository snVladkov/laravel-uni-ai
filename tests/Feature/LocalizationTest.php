<?php

use App\Models\User;

beforeEach(function () {
    seedRoles();
});

it('uses bulgarian as the default locale', function () {
    expect(app()->getLocale())->toBe('bg');

    $this->get('/login')
        ->assertOk()
        ->assertSee('Вход')
        ->assertSee('Имейл');
});

it('switches the locale to english and keeps it in the session', function () {
    $this->get('/language/en')->assertRedirect();

    expect(session('locale'))->toBe('en');

    $this->get('/login')
        ->assertOk()
        ->assertSee('Log in')
        ->assertSee('Email');
});

it('rejects unsupported locales', function () {
    $this->get('/language/de')->assertNotFound();
});

it('localizes validation messages in bulgarian', function () {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->post('/courses', [])
        ->assertSessionHasErrors([
            'title' => 'Полето заглавие е задължително.',
            'code' => 'Полето код е задължително.',
        ]);
});

it('localizes validation messages in english after switching locale', function () {
    $teacher = User::factory()->teacher()->create();

    $this->get('/language/en');

    $this->actingAs($teacher)
        ->post('/courses', [])
        ->assertSessionHasErrors([
            'title' => 'The title field is required.',
            'code' => 'The code field is required.',
        ]);
});
