<?php

use App\Models\Organization;
use App\Models\PipelineTemplate;
use App\Models\Team;

test('manager registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('sales registration csrf page can be rendered', function () {
    $response = $this->get('/register/sales');

    $response->assertStatus(200);
});

test('manager can register and is redirected to integrations setup', function () {
    PipelineTemplate::query()->create([
        'name' => 'Default Pipeline',
        'organization_id' => null,
    ]);

    $response = $this->post('/register', [
        'name' => 'Manager User',
        'email' => 'manager@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'org_name' => 'Acme Clinic',
        'org_size' => '1-10',
        'org_description' => null,
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();
    $user = auth()->user();
    expect($user->role)->toBe('manager');
    expect($user->organization_id)->not->toBeNull();
    expect($user->team_id)->not->toBeNull();
    $response->assertRedirect(route('integrations.n8n.setup', absolute: false));
});

test('sales can register via register sales with invite code', function () {
    PipelineTemplate::query()->create([
        'name' => 'Default Pipeline',
        'organization_id' => null,
    ]);
    $template = PipelineTemplate::query()->where('name', 'Default Pipeline')->first();

    $org = Organization::query()->create([
        'name' => 'Demo Org',
        'slug' => 'demo-org',
        'size' => '1-10',
        'description' => null,
        'invite_code' => 'INV-SEED01',
    ]);

    Team::query()->create([
        'organization_id' => $org->id,
        'name' => 'Sales Team',
        'template_id' => $template->id,
    ]);

    $response = $this->post('/register/sales', [
        'name' => 'Sales Member',
        'email' => 'sales-new@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'invite_token' => 'INV-SEED01',
    ]);

    $this->assertAuthenticated();
    expect(auth()->user()->role)->toBe('sales');
    $response->assertRedirect(route('dashboard.index', absolute: false));
});
