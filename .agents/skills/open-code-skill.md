---
name: laravel-rbac-security
description: Enforce Laravel Policies, Gates, role-based menus, and Pest 403 authorization tests.
Load when implementing user roles, permissions, or access control.
---
# Laravel RBAC & Authorization Standards
1. Never scatter `if ($user->role === ...)` conditionals across controllers or Blade templates.
Encapsulate every model action inside a dedicated `app/Policies/*Policy.php` class.
2. Enforce authorization at both the route/controller layer (`Gate::authorize()` or `can:`
middleware) and the UI layer (`@can` / `@cannot` directives).
3. For every new feature or endpoint, generate Pest feature tests that explicitly verify:
 - Authorized roles receive `200` / `201` / `302`.
 - Unauthorized authenticated roles receive `403 Forbidden` (never `404` or `500`).
 - Guests are redirected to `/login`.
4. Ensure navigation menus dynamically render items strictly based on `Gate::allows()` checks.