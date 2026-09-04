<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

/**
 * Packages a deploy-ready, file-count-optimised release zip for cPanel shared
 * hosting (≈300k inode budget). Builds the front-end, stages only runtime files
 * (no node_modules, no dev vendor, no agent/dev fluff, no source maps), ships a
 * fully-populated .env with a freshly generated APP_KEY, and zips the result so
 * the operator only has to upload + extract + migrate.
 *
 * NOTE: this build does not run the demo DatabaseSeeder. That seeder creates a
 * fake business with five test accounts that all share the password
 * "password" — fine for local/staging, never for a real deployment. The real
 * bootstrap path is `php artisan central:create-admin` (see DEPLOY.txt).
 *
 * NOTE: vendor/ is NOT shipped by default. Pass --vendor to have this command
 * run `composer install --no-dev` into the release. Without it, the target
 * host is responsible for running `composer install --no-dev` itself
 * (DEPLOY.txt is adjusted accordingly).
 */
class BuildRelease extends Command
{
    protected $signature = 'release:build
        {--skip-npm : Reuse the existing public/build instead of rebuilding the front-end}
        {--vendor : Install a production-only vendor/ into the release (omitted by default)}
        {--no-zip : Stage the release but do not create the zip archive}
        {--no-open : Do not open the output folder once the build finishes}';

    protected $description = 'Build a one-shot, deploy-ready release zip (Laravel + compiled front-end + populated .env) for cPanel.';

    /**
     * Whole directories (matched by relative path, "/" separated) that are never shipped.
     *
     * @var list<string>
     */
    private array $excludedDirs = [
        // Dependencies / build tooling — regenerated or compiled away.
        'node_modules', 'vendor', 'bootstrap/cache', 'resources/js', 'resources/css',
        // VCS, CI, agent and editor metadata.
        '.git', '.github', '.claude', '.agents', '.codex', '.cursor', '.gemini',
        '.idea', '.vscode', '.zed', '.nova', '.fleet',
        // Not needed at runtime.
        'tests', 'docs',
        // Dev/runtime state — recreated empty in the release (see scaffoldStorage).
        'storage', 'public/build/.vite', 'public/hot', 'public/storage',
    ];

    /**
     * Exact files (relative path) that are never shipped.
     *
     * @var list<string>
     */
    private array $excludedFiles = [
        '.env', '.env.backup', '.env.testing', 'auth.json',
        '.editorconfig', '.prettierignore', '.prettierrc', '.gitattributes',
        '.gitignore', '.mcp.json', 'boost.json', '.phpactor.json', '.phpunit.result.cache',
        'AGENTS.md', 'CLAUDE.md', 'GEMINI.md', 'README.md',
        'vite.config.js', 'phpunit.xml', 'package.json', 'package-lock.json',
        // Stray local SQLite DB some artisan run left at the project root —
        // production uses MySQL (see writeReleaseEnv); never ship dev data.
        'autocare',
    ];

    public function handle(): int
    {
        $base = base_path();
        $stage = storage_path('app/release/build');
        $stamp = now()->format('Ymd-His');
        $zipPath = storage_path("app/release/autocare-release-{$stamp}.zip");
        $includeVendor = (bool) $this->option('vendor');

        if (! $this->option('skip-npm')) {
            $this->components->task('Building front-end (npm run build)', fn () => $this->runProcess(['npm', 'run', 'build'], $base));
        }

        if (! is_file($base.'/public/build/manifest.json')) {
            $this->components->error('public/build/manifest.json is missing. Run "npm run build" first or drop --skip-npm.');

            return self::FAILURE;
        }

        $this->components->task('Cleaning staging directory', function () use ($stage) {
            $this->deleteDir($stage);
            @mkdir($stage, 0755, true);
        });

        $count = 0;
        $this->components->task('Copying runtime files', function () use ($base, $stage, &$count) {
            $count = $this->copyTree($base, $stage);
        });

        $this->components->task('Recreating storage skeleton', fn () => $this->scaffoldStorage($stage));

        if ($includeVendor) {
            $this->components->task('Installing production vendor (composer --no-dev)', fn () => $this->runProcess([
                'composer', 'install', '--no-dev', '--optimize-autoloader',
                '--classmap-authoritative', '--no-interaction', '--no-progress', '--no-scripts',
            ], $stage));
        } else {
            $this->components->info('Skipping vendor/ — pass --vendor to bundle it, or run "composer install --no-dev" on the target after upload.');
        }

        $this->writeReleaseEnv($stage);
        $this->writeDeployNotes($stage, $includeVendor);

        if ($this->option('no-zip')) {
            $this->components->info("Staged release at: {$stage}");

            if (! $this->option('no-open')) {
                $this->openInExplorer($stage);
            }

            return self::SUCCESS;
        }

        $zipStats = ['files' => 0, 'dirs' => 0];
        $this->components->task('Creating zip archive', function () use ($stage, $zipPath, &$zipStats) {
            $zipStats = $this->zipDir($stage, $zipPath);
        });

        $sizeMb = round(filesize($zipPath) / 1048576, 1);
        $inodes = $zipStats['files'] + $zipStats['dirs'];
        $budgetPct = round($inodes / 300000 * 100, 1);

        $this->newLine();
        $this->components->info('Release built successfully.');
        $this->table(['Metric', 'Value'], [
            ['Zip', $zipPath],
            ['Size', "{$sizeMb} MB"],
            ['Files in archive', number_format($zipStats['files'])],
            ['Directories in archive', number_format($zipStats['dirs'])],
            ['Estimated inodes', number_format($inodes).' ('.$budgetPct.'% of 300k budget)'],
            ['Inode budget', '300,000'],
            ['Vendor', $includeVendor ? 'included (composer install --no-dev)' : 'NOT included — run composer install on the target (see DEPLOY.txt)'],
            ['Database', 'NOT migrated/seeded — see DEPLOY.txt'],
            ['.env', 'included, fully populated (fresh APP_KEY)'],
        ]);

        $this->components->warn('This release does NOT migrate or seed a database. Run the artisan commands in DEPLOY.txt against the target yourself before the app will boot.');

        if (! $includeVendor) {
            $this->components->warn('This release does NOT include vendor/. Run "composer install --no-dev" on the target before the app will boot, or rebuild with --vendor. See DEPLOY.txt.');
        }

        if ($inodes > 280000) {
            $this->components->warn('Inode count is close to the 300k cPanel limit — review the exclude list.');
        }

        if (! $this->option('no-open')) {
            $this->openInExplorer($zipPath);
        }

        return self::SUCCESS;
    }

    private function shouldSkip(string $relative, bool $isDir): bool
    {
        $relative = str_replace('\\', '/', $relative);

        if ($isDir) {
            return in_array($relative, $this->excludedDirs, true);
        }

        if (in_array($relative, $this->excludedFiles, true)) {
            return true;
        }

        // Drop log files, JS source maps and stray local SQLite DBs anywhere in the tree.
        return Str::endsWith($relative, ['.log', '.map', '.sqlite', '.sqlite-journal']);
    }

    /**
     * Copy $src into $dst honouring the exclude lists. Returns the file count copied.
     */
    private function copyTree(string $src, string $dst): int
    {
        $count = 0;
        /** @var RecursiveDirectoryIterator $items */
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
        );

        $items->setMaxDepth(-1);

        foreach ($items as $item) {
            $relative = $this->relativePath($item->getPathname(), $src);

            // Prune excluded directories (and everything beneath them).
            $segments = explode('/', $relative);
            $skip = false;
            for ($i = 1; $i <= count($segments); $i++) {
                if (in_array(implode('/', array_slice($segments, 0, $i)), $this->excludedDirs, true)) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) {
                continue;
            }

            if ($item->isDir()) {
                if (! $this->shouldSkip($relative, true)) {
                    @mkdir($dst.'/'.$relative, 0755, true);
                }

                continue;
            }

            if ($this->shouldSkip($relative, false)) {
                continue;
            }

            @mkdir(dirname($dst.'/'.$relative), 0755, true);
            copy($item->getPathname(), $dst.'/'.$relative);
            $count++;
        }

        return $count;
    }

    /**
     * Recreates storage/ empty and leaves public/storage untouched so
     * `php artisan storage:link` can create it as a real symlink on the
     * target (config/filesystems.php's 'public' disk still expects that —
     * see DEPLOY.txt). The two .htaccess files this project already relies
     * on for storage/ (deny all) and the public disk root (deny script
     * execution on uploaded vehicle photos) don't survive being copied from
     * a directory this method wipes, so they're rewritten here from the same
     * source the project keeps at the repo root.
     */
    private function scaffoldStorage(string $stage): void
    {
        $dirs = [
            'storage/app/public', 'storage/app/private',
            'storage/framework/cache/data', 'storage/framework/sessions',
            'storage/framework/views', 'storage/framework/testing',
            'storage/logs', 'bootstrap/cache',
        ];

        foreach ($dirs as $dir) {
            @mkdir($stage.'/'.$dir, 0755, true);
            file_put_contents($stage.'/'.$dir.'/.gitignore', "*\n!.gitignore\n");
        }

        file_put_contents($stage.'/storage/.htaccess', $this->denyAllHtaccess());

        // Lives in the real backing directory (not public/storage, which is
        // just a symlink to this once storage:link runs on the target) so it
        // still applies after the symlink is resolved.
        file_put_contents($stage.'/storage/app/public/.htaccess', $this->noScriptExecutionHtaccess());
    }

    private function denyAllHtaccess(): string
    {
        return <<<'HTACCESS'
        <IfModule mod_authz_core.c>
            Require all denied
        </IfModule>
        <IfModule !mod_authz_core.c>
            Order deny,allow
            Deny from all
        </IfModule>

        HTACCESS;
    }

    private function noScriptExecutionHtaccess(): string
    {
        return <<<'HTACCESS'
        Options -Indexes

        # Disable any PHP execution (prevents malware uploads via vehicle images)
        <FilesMatch "\.(php|phar|phtml|php5|php7|php8|pht|shtml|cgi|pl|py|sh|htaccess)$">
            Require all denied
        </FilesMatch>

        <IfModule mod_rewrite.c>
            RewriteEngine On
            # Block double extensions like x.jpg.php
            RewriteRule \.(php|phar|phtml|php5|php7|php8|pht|shtml|cgi|pl|py|sh)$ - [F]
        </IfModule>

        HTACCESS;
    }

    private function writeDeployNotes(string $stage, bool $includeVendor): void
    {
        $vendorStep = $includeVendor
            ? '   (vendor/ is already bundled in this zip — nothing to install.)'
            : <<<'TXT'
               This zip does NOT include vendor/. Before running the artisan
               commands below, install PHP dependencies on the target:
                 - With SSH: cd into the extracted app folder and run
                   `composer install --no-dev --optimize-autoloader --classmap-authoritative`.
                 - Without SSH: rebuild locally with `php artisan release:build --vendor`
                   and re-upload, or use your host's Composer/terminal tool if cPanel
                   provides one (Setup Node.js/PHP App, Terminal, etc).
            TXT;

        $notes = <<<TXT
        AUTOCARE PRO — cPanel one-shot deploy
        ======================================
        Everything up to and including "extract the zip" needs no shell access.
        Step 7 (migrate + create the first admin) needs to run `php artisan`
        once, via SSH, cPanel's Terminal app, or a one-off deploy script/CI job
        — whichever your host gives you.

        PART A — Before uploading
        -------------------------
        1. In cPanel → "MySQL® Databases":
             - Create a database and a user for the app (e.g. yourcpaneluser_autocare).
             - Set the password to whatever you'll paste into .env in step 6.
             - Add the user to the database with ALL PRIVILEGES.
        2. Point your domain (or a subdomain) at this app: in cPanel → "Domains"
           / "Subdomains", set the document root to <app>/public — never the
           Laravel project root. AutoCare Pro is single-domain: Master Control
           (the platform admin panel) lives at https://your-domain/admin and
           every tenant workspace lives at https://your-domain/<tenant-slug> on
           that SAME domain — no wildcard DNS or wildcard SSL is needed.
        3. Get an SSL certificate for that one domain (cPanel AutoSSL usually
           issues this automatically once the domain resolves).

        PART B — Upload & install
        -------------------------
        4. Upload this zip and extract it inside your app folder, e.g. ~/autocare
           (NOT inside public_html — the document root must stay <app>/public).
        5. Ensure storage/ and bootstrap/cache/ are writable by the PHP user.
           cPanel defaults are normally fine; if you see permission errors, set
           them to 755/775.
        {$vendorStep}
        6. Open the extracted .env and fill in DB_DATABASE / DB_USERNAME /
           DB_PASSWORD to match what you created in step 1, and set APP_URL to
           your real domain. Fill in MAIL_* / WHATSAPP_* too if you're using
           those (both are optional — see Part C).
        7. Run these once against the target, in order:
             php artisan migrate
             php artisan db:seed --class=PermissionSeeder
             php artisan storage:link
             php artisan central:create-admin
           `central:create-admin` prompts for a name/email/password (or pass
           --name= --email= --password=) and creates your first Master Control
           login; it prints the exact sign-in URL when done. Sign in there and
           create your first real tenant/business from the UI.
           DO NOT run plain `php artisan db:seed` or `composer run setup` on
           this database — the bundled DatabaseSeeder creates a demo business
           with five test accounts that all use the password "password". That
           seeder is for local development only.
        8. Done. No other `php artisan` commands are needed after that — the
           front-end is pre-compiled in public/build (no Node needed on the
           server), and config isn't cached, so any later .env edit takes
           effect on the next request.
        9. REQUIREMENTS: PHP 8.2+ (cPanel → "MultiPHP Manager", pick the
           version for this domain/account) and a utf8mb4 MySQL database
           (the default).

        PART C — Notes
        --------------
        - MAIL_MAILER=log until you configure real SMTP in .env. Nothing in
          the app sends mail yet, so this is a safe default, not something
          you must fill in before going live.
        - WHATSAPP_PROVIDER=none until WHATSAPP_API_URL / WHATSAPP_API_TOKEN /
          WHATSAPP_PHONE_NUMBER_ID are filled in.
        - Queues run synchronously (QUEUE_CONNECTION=sync) — no worker needed
          on shared hosting.
        - TENANCY_BASE_DOMAIN is left unset on purpose: AutoCare Pro only does
          path-based tenancy today (/admin, /<slug>). config/tenancy.php keeps
          that env var wired up for a possible future subdomain-per-tenant
          rollout, but nothing reads it yet.
        - A fresh APP_KEY was generated for this specific build — it isn't
          reused across releases or shared with any other project.
        TXT;

        // Strip the leading per-line indentation added by the heredoc.
        $lines = array_map(
            fn (string $line) => ltrim($line),
            explode("\n", $notes),
        );

        file_put_contents($stage.'/DEPLOY.txt', implode("\n", $lines));
    }

    /**
     * Writes a fully-populated production .env into the staged release. Only
     * DB_* and APP_URL need filling in on the target (see DEPLOY.txt) — this
     * app has no equivalent of Vellix Point's per-deploy secrets (no S3/R2
     * bucket, no wildcard tenancy domain), so nothing else is a placeholder.
     * APP_KEY is generated fresh on every build, never hard-coded.
     */
    private function writeReleaseEnv(string $stage): void
    {
        $env = <<<'ENV'
        APP_NAME="AutoCare Pro"
        APP_ENV=production
        APP_KEY=__APP_KEY__
        APP_DEBUG=true
        APP_TIMEZONE=Asia/Colombo
        APP_URL=http://localhost

        APP_LOCALE=en
        APP_FALLBACK_LOCALE=en
        APP_FAKER_LOCALE=en_US

        APP_MAINTENANCE_DRIVER=file

        BCRYPT_ROUNDS=12

        LOG_CHANNEL=stack
        LOG_STACK=single
        LOG_DEPRECATIONS_CHANNEL=null
        LOG_LEVEL=error

        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=leomclxr_auto
        DB_USERNAME=leomclxr_auto_user
        DB_PASSWORD=Cx8Fnj4FQg79Gnk5

        SESSION_DRIVER=database
        SESSION_LIFETIME=120
        SESSION_ENCRYPT=false
        SESSION_PATH=/
        SESSION_SECURE_COOKIE=true

        BROADCAST_CONNECTION=log
        FILESYSTEM_DISK=public
        # Shared cPanel does not run a persistent worker by default, and this
        # app has no queued jobs today — sync avoids piling up unprocessed
        # rows in a database queue nothing is draining.
        QUEUE_CONNECTION=sync

        CACHE_STORE=database

        MAIL_MAILER=log
        MAIL_HOST=127.0.0.1
        MAIL_PORT=2525
        MAIL_USERNAME=null
        MAIL_PASSWORD=null
        MAIL_ENCRYPTION=null
        MAIL_FROM_ADDRESS="hello@example.com"
        MAIL_FROM_NAME="${APP_NAME}"

        WHATSAPP_PROVIDER=none
        WHATSAPP_API_URL=
        WHATSAPP_API_TOKEN=
        WHATSAPP_PHONE_NUMBER_ID=

        # Path-based tenancy on a single domain: {APP_URL}/admin is Master
        # Control, {APP_URL}/<tenant-slug> is a tenant workspace. Left unset —
        # see the TENANCY_BASE_DOMAIN note in DEPLOY.txt.
        TENANCY_CENTRAL_PREFIX=admin
        TENANCY_BASE_DOMAIN=

        VITE_APP_NAME="${APP_NAME}"
        ENV;

        $appKey = 'base64:'.base64_encode(random_bytes(32));

        // Strip the leading per-line indentation added by the heredoc, then
        // drop in the real key (kept out of the heredoc so it stays a NOWDOC
        // and the literal ${APP_NAME} references above aren't interpolated).
        $lines = array_map(
            fn (string $line) => ltrim($line),
            explode("\n", $env),
        );

        file_put_contents($stage.'/.env', str_replace('__APP_KEY__', $appKey, implode("\n", $lines))."\n");
    }

    /**
     * @return array{files: int, dirs: int}
     */
    private function zipDir(string $stage, string $zipPath): array
    {
        @mkdir(dirname($zipPath), 0755, true);
        @unlink($zipPath);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Unable to create {$zipPath}");
        }

        $files = 0;
        $dirs = 0;
        /** @var RecursiveDirectoryIterator $items */
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($stage, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
        );

        foreach ($items as $item) {
            $relative = $this->relativePath($item->getPathname(), $stage);
            if ($item->isDir()) {
                $zip->addEmptyDir($relative);
                $dirs++;
            } else {
                $zip->addFile($item->getPathname(), $relative);
                $files++;
            }
        }

        $zip->close();

        return ['files' => $files, 'dirs' => $dirs];
    }

    private function deleteDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }

        @rmdir($dir);
    }

    /**
     * Produce a forward-slash relative path across Windows and Linux hosts.
     */
    private function relativePath(string $path, string $root): string
    {
        $path = str_replace('\\', '/', $path);
        $root = rtrim(str_replace('\\', '/', $root), '/');

        return ltrim(Str::after($path, $root), '/');
    }

    /**
     * @param  list<string>  $command
     */
    private function runProcess(array $command, string $cwd): void
    {
        $process = new Process($command, $cwd, null, null, 1800);
        $process->run(fn ($type, $buffer) => $this->output->write($buffer));

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Command failed: '.implode(' ', $command));
        }
    }

    /**
     * Best-effort convenience: pop open the output in Explorer once the build
     * finishes so there's no zip/folder to go hunting for. Windows only, and
     * never allowed to fail the build — pass --no-open to skip it.
     */
    private function openInExplorer(string $path): void
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return;
        }

        try {
            $args = is_dir($path) ? [$path] : ['/select,'.$path];
            (new Process(array_merge(['explorer.exe'], $args)))->run();
        } catch (Throwable) {
            // Opening a window is a nicety, not a build requirement.
        }
    }
}
