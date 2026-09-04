<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * A single artisan-over-HTTP escape hatch for shared cPanel hosts with no
 * SSH/Terminal access — see DEPLOY.txt. Deliberately narrow: only the four
 * named actions below can run, `db:seed` only ever runs PermissionSeeder
 * (never the demo DatabaseSeeder), and every request must carry
 * DEPLOY_SECRET from .env. Unset the env var (or delete this file) once the
 * deploy is done — it stays live as long as the route exists.
 */
class DeployController extends Controller
{
    private const ACTIONS = ['migrate', 'migrate-status', 'seed', 'rollback'];

    public function run(Request $request, string $action)
    {
        $secret = config('app.deploy_secret');

        if (! is_string($secret) || $secret === '' || ! hash_equals($secret, (string) $request->query('secret'))) {
            // Same 404 whether the secret is missing, wrong, or unset —
            // never confirm this endpoint exists to an unauthenticated probe.
            throw new NotFoundHttpException;
        }

        if (! in_array($action, self::ACTIONS, true)) {
            throw new NotFoundHttpException;
        }

        $exitCode = match ($action) {
            'migrate' => Artisan::call('migrate', ['--force' => true]),
            'migrate-status' => Artisan::call('migrate:status'),
            'seed' => Artisan::call('db:seed', ['--class' => 'PermissionSeeder', '--force' => true]),
            'rollback' => Artisan::call('migrate:rollback', ['--force' => true, '--step' => (int) $request->query('step', 1)]),
        };

        return response(
            "\$ php artisan {$action}\n\n".Artisan::output()."\nExit code: {$exitCode}\n",
            200,
            ['Content-Type' => 'text/plain']
        );
    }
}
