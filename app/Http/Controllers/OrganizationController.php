<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationIntegration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Organization::class);
        $organizations = Organization::all();
        return view('organizations.index', compact('organizations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Organization::class);
        return view('organizations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Organization::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:organizations,slug',
            'size' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'invite_code' => 'nullable|string|max:255|unique:organizations,invite_code',
        ]);

        Organization::create($validated);

        return redirect()->route('organizations.index')->with('success', 'Organization created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Organization $organization)
    {
        Gate::authorize('view', $organization);
        return view('organizations.show', compact('organization'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Organization $organization)
    {
        Gate::authorize('update', $organization);
        $integration = OrganizationIntegration::firstOrNew([
            'organization_id' => $organization->id,
        ]);

        $n8nBaseUrl = (string) config('services.n8n.base_url', '');
        $webhookPrefix = (string) config('services.n8n.webhook_prefix', '/webhook/');
        $webhookPrefix = '/' . ltrim($webhookPrefix, '/');
        $webhookUrl = ($n8nBaseUrl !== '' && $integration->line_webhook_path)
            ? rtrim($n8nBaseUrl, '/') . $webhookPrefix . ltrim($integration->line_webhook_path, '/')
            : '';

        return view('organizations.edit', compact('organization', 'integration', 'webhookUrl', 'n8nBaseUrl'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organization $organization)
    {
        Gate::authorize('update', $organization);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:organizations,slug,' . $organization->id,
            'size' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'invite_code' => 'nullable|string|max:255|unique:organizations,invite_code,' . $organization->id,
            // Integrations (admin)
            'integration_n8n_token_name' => 'nullable|string|max:255',
            'integration_line_webhook_path' => 'nullable|string|max:191|unique:organization_integrations,line_webhook_path,' . $organization->id . ',organization_id',
            'integration_regenerate_line_webhook' => 'nullable|boolean',
            'integration_line_channel_access_token' => 'nullable|string|max:5000',
            'integration_rotate_n8n_token' => 'nullable|boolean',
        ]);

        $orgOnly = collect($validated)->only(['name', 'slug', 'size', 'description', 'invite_code'])->toArray();
        $organization->update($orgOnly);

        // Upsert integration record if any integration field is present or if already exists
        $integration = OrganizationIntegration::firstOrNew([
            'organization_id' => $organization->id,
        ]);

        $touchIntegration = $integration->exists
            || $request->hasAny([
                'integration_n8n_token_name',
                'integration_line_webhook_path',
                'integration_regenerate_line_webhook',
                'integration_line_channel_access_token',
                'integration_rotate_n8n_token',
            ]);

        if ($touchIntegration) {
            if (!$integration->exists) {
                $secret = Str::random(40);
                $integration->line_webhook_secret = $secret;
                $integration->line_webhook_path = 'flowcrm-line-' . $organization->id . '-' . $secret;
                $integration->n8n_token_name = 'n8n-default';
            }

            if ($request->boolean('integration_regenerate_line_webhook')) {
                $secret = Str::random(40);
                $integration->line_webhook_secret = $secret;
                $integration->line_webhook_path = 'flowcrm-line-' . $organization->id . '-' . $secret;
            }

            $path = trim((string) $request->input('integration_line_webhook_path', ''));
            if ($path !== '') {
                $integration->line_webhook_path = $path;
            }

            $tokenName = trim((string) $request->input('integration_n8n_token_name', ''));
            if ($tokenName !== '') {
                $integration->n8n_token_name = $tokenName;
            }

            if ($request->has('integration_line_channel_access_token')) {
                $raw = trim((string) $request->input('integration_line_channel_access_token', ''));
                $integration->line_channel_access_token_encrypted = $raw === '' ? null : Crypt::encryptString($raw);
            }

            $integration->save();

            if ($request->boolean('integration_rotate_n8n_token')) {
                // Ensure service user exists
                $serviceUser = $integration->n8nServiceUser;
                if (!$serviceUser) {
                    $email = 'n8n+' . $organization->id . '@flowcrm.local';
                    $serviceUser = User::firstOrCreate(
                        ['email' => $email],
                        [
                            'name' => 'n8n Service (' . $organization->name . ')',
                            'password' => bcrypt(Str::random(32)),
                            'role' => 'manager',
                            'organization_id' => $organization->id,
                            'last_login' => now(),
                            'is_active' => false,
                        ]
                    );
                    $integration->n8n_service_user_id = $serviceUser->id;
                    $integration->save();
                }

                $serviceUser->tokens()->delete();
                $tokenName = $integration->n8n_token_name ?? 'n8n-default';
                $token = $serviceUser->createToken($tokenName, ['n8n']);
                $request->session()->flash('n8n_plain_text_token', $token->plainTextToken);
            }
        }

        return redirect()->route('organizations.edit', $organization->id)->with('success', 'Organization updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organization $organization)
    {
        Gate::authorize('delete', $organization);
        $organization->delete();
        return redirect()->route('organizations.index')->with('success', 'Organization deleted successfully.');
    }

    public function usersIndex()
    {
        $organizations = Organization::withCount('users')->latest()->paginate(9);
        return view('organizations.users_index', compact('organizations'));
    }
}
