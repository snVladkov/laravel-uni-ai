<?php

use App\Models\Course;
use App\Models\User;

beforeEach(function () {
    seedRoles();
});

/**
 * Route matrix: [method, uri, roles that may access, expected status].
 *
 * @return array<string, array{0: string, 1: string, 2: array<int, string>, 3: int}>
 */
function routeMatrix(): array
{
    return [
        'dashboard' => ['GET', '/dashboard', ['administrator', 'head_of_department', 'teacher', 'student'], 200],
        'course index' => ['GET', '/courses', ['administrator', 'head_of_department', 'teacher', 'student'], 200],
        'course create' => ['GET', '/courses/create', ['teacher'], 200],
        'pending approvals' => ['GET', '/hod/courses/pending', ['head_of_department'], 200],
        'teacher management' => ['GET', '/hod/teachers', ['head_of_department'], 200],
        'user management' => ['GET', '/admin/users', ['administrator'], 200],
        'user create' => ['GET', '/admin/users/create', ['administrator'], 200],
    ];
}

it('allows the authorized role and forbids every other authenticated role', function (string $method, string $uri, array $allowed, int $expected) {
    $actors = collect(['administrator', 'head_of_department', 'teacher', 'student'])
        ->mapWithKeys(fn (string $role): array => [$role => userWithRole($role)]);

    foreach ($actors as $role => $actor) {
        $response = $this->actingAs($actor)->call($method, $uri);

        if (in_array($role, $allowed, true)) {
            expect($response->getStatusCode())->toBe($expected, "{$role} should access {$method} {$uri}");
        } else {
            expect($response->getStatusCode())->toBe(403, "{$role} should be forbidden from {$method} {$uri}");
        }
    }
})->with(fn (): array => routeMatrix());

it('redirects guests from every protected route to login', function (string $method, string $uri) {
    $this->call($method, $uri)->assertRedirect('/login');
})->with(fn (): array => collect(routeMatrix())->map(fn (array $route): array => [$route[0], $route[1]])->all());

it('lets the owning teacher open the edit form but forbids other teachers', function () {
    $owner = User::factory()->teacher()->create();
    $other = User::factory()->teacher()->create();
    $course = Course::factory()->forTeacher($owner)->create();

    $this->actingAs($owner)->get("/courses/{$course->id}/edit")->assertOk();
    $this->actingAs($other)->get("/courses/{$course->id}/edit")->assertForbidden();
    $this->actingAs(User::factory()->administrator()->create())->get("/courses/{$course->id}/edit")->assertForbidden();
});
