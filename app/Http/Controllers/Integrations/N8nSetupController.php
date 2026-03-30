<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationIntegration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class N8nSetupController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'manager') {
            abort(403);
        }

        $orgId = $user->organization_id;
        if (!$orgId) {
            abort(403, 'Missing organization.');
        }

        $organization = Organization::findOrFail($orgId);

        $integration = OrganizationIntegration::firstOrNew([
            'organization_id' => $organization->id,
        ]);

        if (!$integration->exists) {
            $secret = Str::random(40);
            $path = 'flowcrm-line-' . $organization->id . '-' . $secret;

            $integration->line_webhook_secret = $secret;
            $integration->line_webhook_path = $path;
            $integration->n8n_token_name = 'n8n-default';
            $integration->save();
        }

        // Ensure integration service user exists (token owner)
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

        // Create token only once per "first setup" redirect; user can rotate later.
        $plainTextToken = null;
        if ($request->session()->pull('flowcrm.n8n_generate_token', false) === true) {
            $token = $serviceUser->createToken($integration->n8n_token_name ?? 'n8n-default', ['n8n']);
            $plainTextToken = $token->plainTextToken;
        }

        $n8nBaseUrl = (string) config('services.n8n.base_url', '');
        $webhookPrefix = (string) config('services.n8n.webhook_prefix', '/webhook/');
        $webhookPrefix = '/' . ltrim($webhookPrefix, '/');
        $webhookUrl = $n8nBaseUrl !== ''
            ? rtrim($n8nBaseUrl, '/') . $webhookPrefix . ltrim($integration->line_webhook_path, '/')
            : '';

        $lineTokenPresent = $integration->line_channel_access_token_encrypted ? true : false;

        return view('integrations.n8n-setup', [
            'organization' => $organization,
            'integration' => $integration,
            'plainTextToken' => $plainTextToken,
            'webhookUrl' => $webhookUrl,
            'n8nBaseUrl' => $n8nBaseUrl,
            'lineTokenPresent' => $lineTokenPresent,
        ]);
    }

    public function rotateToken(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'manager') {
            abort(403);
        }

        $orgId = $user->organization_id;
        if (!$orgId) {
            abort(403, 'Missing organization.');
        }

        $integration = OrganizationIntegration::where('organization_id', $orgId)->firstOrFail();
        $serviceUser = $integration->n8nServiceUser;

        if (!$serviceUser) {
            abort(500, 'Missing n8n service user.');
        }

        // Revoke all old tokens and create a new one
        $serviceUser->tokens()->delete();

        $tokenName = $integration->n8n_token_name ?? 'n8n-default';
        $token = $serviceUser->createToken($tokenName, ['n8n']);

        return redirect()
            ->route('integrations.n8n.setup')
            ->with('n8n_plain_text_token', $token->plainTextToken);
    }

    public function upsertLineAccessToken(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'manager') {
            abort(403);
        }

        $orgId = $user->organization_id;
        if (!$orgId) {
            abort(403, 'Missing organization.');
        }

        $request->validate([
            'line_channel_access_token' => ['nullable', 'string', 'max:5000'],
        ]);

        $integration = OrganizationIntegration::where('organization_id', $orgId)->firstOrFail();

        $raw = trim((string) $request->input('line_channel_access_token', ''));
        if ($raw === '') {
            $integration->line_channel_access_token_encrypted = null;
        } else {
            $integration->line_channel_access_token_encrypted = Crypt::encryptString($raw);
        }

        $integration->save();

        return redirect()->route('integrations.n8n.setup')->with('saved', true);
    }
}

