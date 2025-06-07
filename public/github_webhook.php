<?php

// --- Configuration ---
define('ENV_FILE', __DIR__.'/../.env'); // Assuming .env is in the project root
define('SECRET_ENV_VAR_NAME', 'GITHUB_WEBHOOK_SECRET');
define('REPO_PATH_ENV_VAR_NAME', 'GIT_REPO_PATH');
define('GIT_EXECUTABLE', '/usr/bin/git'); // Adjust if git is elsewhere
define('LOG_FILE', __DIR__.'/../storage/logs/github_webhook.log'); // Log to Laravel's storage directory
// --- End Configuration ---

// --- Logging Function ---
function log_message($message)
{
    // Ensure LOG_FILE is defined and not empty before attempting to write
    if (defined('LOG_FILE') && LOG_FILE) {
        $timestamp = date('Y-m-d H:i:s');
        // Use error_log as a fallback if file_put_contents fails? Maybe not necessary here.
        @file_put_contents(LOG_FILE, '['.$timestamp.'] '.$message."\n", FILE_APPEND);
    }
    // Optionally, you could add an error_log() call here as a fallback
    // if file logging fails, e.g., error_log("Webhook Log: " . $message);
}

// --- Security Checks ---
// Prevent direct browser access
if (! isset($_SERVER['HTTP_USER_AGENT']) || ! isset($_SERVER['HTTP_X_GITHUB_EVENT'])) {
    http_response_code(403);
    log_message('ERROR: Direct access attempt blocked. Missing required headers.');
    exit('Direct access not allowed. This endpoint is for GitHub webhooks only.');
}

// Additional security: Check for GitHub User-Agent
if (! str_contains($_SERVER['HTTP_USER_AGENT'], 'GitHub-Hookshot/')) {
    http_response_code(403);
    log_message('ERROR: Invalid User-Agent. Expected GitHub-Hookshot, got: '.($_SERVER['HTTP_USER_AGENT'] ?? 'none'));
    exit('Invalid request source.');
}

log_message('Security checks passed. Processing webhook request.');

// --- Manual .env Parsing and Variable Retrieval (Using fgets) ---
$webhookSecret = null;
$repoPath = null;
$linesRead = 0;

log_message('Starting .env parsing process (using fgets).');

if (! file_exists(ENV_FILE)) {
    log_message('ERROR: .env file not found at: '.ENV_FILE);
} else {
    log_message('.env file found at: '.ENV_FILE);
    $handle = @fopen(ENV_FILE, 'r'); // Open the file for reading, suppress errors on failure
    if ($handle) {
        log_message('.env file opened successfully.');
        while (($line = fgets($handle)) !== false) {
            $linesRead++;
            $line = trim($line); // Trim whitespace AND newline characters

            log_message('Debug: Processing line #'.$linesRead.": '".$line."'");

            // Ignore comments and empty lines
            if (empty($line) || str_starts_with($line, '#')) {
                log_message('Debug: Skipping line as comment or empty.');

                continue;
            }

            // Ignore lines not in KEY=VALUE format
            if (strpos($line, '=') === false) {
                log_message('Debug: Skipping line as not key=value format.');

                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $originalKey = trim($key); // Trim key just in case
            $originalValue = trim($value); // Value is already trimmed from the line trim

            log_message("Debug: Extracted Key: '".$originalKey."'");
            log_message("Debug: Extracted Value (before quote removal): '".$originalValue."'");

            $key = $originalKey;
            $value = $originalValue;

            // Remove quotes from value if present
            if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
                $value = substr($value, 1, -1);
                log_message('Debug: Removed double quotes from value.');
            } elseif (str_starts_with($value, "'") && str_ends_with($value, "'")) {
                $value = substr($value, 1, -1);
                log_message('Debug: Removed single quotes from value.');
            }

            log_message("Debug: Final Value (after quote removal): '".$value."'");

            // Store the value if it matches a variable name we're looking for
            if ($key === SECRET_ENV_VAR_NAME) {
                $webhookSecret = $value;
                log_message('Debug: '.SECRET_ENV_VAR_NAME.' found! Value length: '.strlen($webhookSecret));
            } elseif ($key === REPO_PATH_ENV_VAR_NAME) {
                $repoPath = $value;
                log_message('Debug: '.REPO_PATH_ENV_VAR_NAME." found! Value: '".$repoPath."'");
            }
        }

        // Check for errors after the loop (e.g., read error partway through)
        if (! feof($handle)) {
            log_message('ERROR: Error occurred while reading .env file with fgets().');
        }

        fclose($handle); // Close the file handle
        log_message('.env file closed.');

    } else {
        // Log error if fopen failed
        $error = error_get_last();
        log_message('ERROR: Failed to open .env file for reading. Error: '.($error['message'] ?? 'Unknown error'));
    }
}

log_message('Finished .env parsing process.');
log_message('Debug: Final webhookSecret: '.($webhookSecret === null ? 'null' : (empty($webhookSecret) ? 'empty string' : 'set')));
log_message('Debug: Final repoPath: '.($repoPath === null ? 'null' : (empty($repoPath) ? 'empty string' : $repoPath)));

// --- Validate Retrieved Variables ---
if ($webhookSecret === null || $webhookSecret === '') {
    http_response_code(500); // Internal Server Error
    log_message("ERROR: GitHub webhook secret not found or is empty after parsing .env file ('".SECRET_ENV_VAR_NAME."').");
    exit('Server configuration error: Webhook secret is not set.');
}
if ($repoPath === null || $repoPath === '') {
    http_response_code(500); // Internal Server Error
    log_message("ERROR: Repository path not found or is empty after parsing .env file ('".REPO_PATH_ENV_VAR_NAME."').");
    exit('Server configuration error: Repository path is not set.');
}

// Define constants only if they haven't been defined elsewhere (unlikely here, but good practice)
if (! defined('GITHUB_WEBHOOK_SECRET')) {
    define('GITHUB_WEBHOOK_SECRET', $webhookSecret);
}
if (! defined('REPO_PATH')) {
    define('REPO_PATH', $repoPath);
}

// --- Request Validation ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    log_message('ERROR: Invalid request method: '.$_SERVER['REQUEST_METHOD']);
    exit('Method Not Allowed');
}
if (! isset($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] !== 'application/json') {
    http_response_code(400); // Bad Request
    log_message('ERROR: Invalid Content-Type: '.($_SERVER['CONTENT_TYPE'] ?? 'Not set'));
    exit('Invalid Content-Type');
}
if (! isset($_SERVER['HTTP_X_GITHUB_EVENT']) || $_SERVER['HTTP_X_GITHUB_EVENT'] !== 'push') {
    http_response_code(200); // OK, but we ignore non-push events
    log_message('Ignoring non-push event: '.($_SERVER['HTTP_X_GITHUB_EVENT'] ?? 'Not set'));
    exit('Ignoring event: '.($_SERVER['HTTP_X_GITHUB_EVENT'] ?? 'Not set'));
}
// Prioritize X-Hub-Signature-256, fall back to X-Hub-Signature if present
$signatureHeader = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? null;
if (! $signatureHeader) {
    http_response_code(403); // Forbidden
    log_message('ERROR: Signature header (X-Hub-Signature-256 or X-Hub-Signature) missing!');
    exit('Signature header missing');
}

// --- Signature Verification ---
[$algo, $receivedHash] = explode('=', $signatureHeader, 2) + ['', ''];
$supportedAlgos = ['sha256', 'sha1']; // List supported algorithms
$usedAlgo = null;
if (in_array($algo, $supportedAlgos)) {
    $usedAlgo = $algo;
}
if (! $usedAlgo) {
    http_response_code(403); // Forbidden
    log_message('ERROR: Unsupported signature algorithm: '.$algo);
    exit('Unsupported signature algorithm');
}
$payloadBody = file_get_contents('php://input');
if ($payloadBody === false) {
    http_response_code(500); // Internal Server Error
    log_message('ERROR: Could not read request body.');
    exit('Could not read request body');
}
$expectedHash = hash_hmac($usedAlgo, $payloadBody, GITHUB_WEBHOOK_SECRET);
if (! hash_equals($receivedHash, $expectedHash)) {
    http_response_code(403); // Forbidden
    log_message('ERROR: Signature verification failed!');
    log_message('Received: '.$receivedHash);
    log_message('Expected: '.$expectedHash);
    exit('Signature verification failed');
}
log_message('Signature verified successfully for push event using '.$usedAlgo.'.');

// --- Execute Git Pull ---
if (! is_dir(REPO_PATH)) {
    http_response_code(500);
    log_message('ERROR: Repository path does not exist: '.REPO_PATH);
    exit('Repository path not found on server.');
}
// Check if the directory is writable by the web server user
if (! is_writable(REPO_PATH)) {
    http_response_code(500);
    log_message('ERROR: Repository path is not writable by the web server user: '.REPO_PATH);
    exit('Repository path is not writable by the server.');
}
// Ensure the git executable is found and executable
if (! is_executable(GIT_EXECUTABLE)) {
    http_response_code(500);
    log_message('ERROR: Git executable not found or not executable: '.GIT_EXECUTABLE);
    exit('Git executable not found.');
}
// Construct the command using cd for safety
$command = 'cd '.escapeshellarg(REPO_PATH).' && '.escapeshellcmd(GIT_EXECUTABLE).' pull 2>&1'; // Redirect stderr to stdout
log_message('Executing command: '.$command);
// Execute the command
$output = shell_exec($command);
log_message('Git pull output:'."\n".$output);
// Basic check for success (look for absence of common error indicators)
if (strpos($output, 'fatal:') === false && strpos($output, 'error:') === false) {
    log_message('Git pull executed successfully. Starting post-deployment tasks...');

    // --- Post-Deployment Processing ---
    $deploymentSuccess = true;
    $deploymentOutput = [];

    // 1. Install/Update Composer Dependencies
    log_message('Running composer install...');
    $composerCommand = 'cd '.escapeshellarg(REPO_PATH).' && HOME=/tmp COMPOSER_HOME=/tmp composer install --no-dev --optimize-autoloader 2>&1';
    $composerOutput = shell_exec($composerCommand);
    log_message('Composer output: '."\n".$composerOutput);
    if (strpos($composerOutput, 'error') !== false || strpos($composerOutput, 'failed') !== false) {
        $deploymentSuccess = false;
        $deploymentOutput[] = 'Composer install failed: '.$composerOutput;
        log_message('ERROR: Composer install failed.');
    } else {
        $deploymentOutput[] = 'Composer dependencies updated successfully.';
    }

    // 2. Build Frontend Assets
    log_message('Building frontend assets...');

    // Debug: Check node and npm versions and PATH
    $debugCommand = 'cd '.escapeshellarg(REPO_PATH).' && echo "PATH: $PATH" && which node && which npm && node --version && npm --version 2>&1';
    $debugOutput = shell_exec($debugCommand);
    log_message('Debug info: '."\n".$debugOutput);

    // First ensure package-lock.json exists
    $lockFileCheck = 'cd '.escapeshellarg(REPO_PATH).' && ls -la package-lock.json 2>&1';
    $lockFileOutput = shell_exec($lockFileCheck);
    log_message('Package-lock.json check: '."\n".$lockFileOutput);

    // If package-lock.json doesn't exist, create it
    if (strpos($lockFileOutput, 'No such file') !== false || strpos($lockFileOutput, 'cannot access') !== false) {
        log_message('Package-lock.json missing, generating it...');
        $generateLockCommand = 'cd '.escapeshellarg(REPO_PATH).' && npm install --package-lock-only 2>&1';
        $generateLockOutput = shell_exec($generateLockCommand);
        log_message('Generate lock file output: '."\n".$generateLockOutput);
    }

    // Install all dependencies (including dev deps for build tools like Vite)
    log_message('Installing npm dependencies...');
    $npmInstallCommand = 'cd '.escapeshellarg(REPO_PATH).' && npm ci || npm install 2>&1';
    $npmInstallOutput = shell_exec($npmInstallCommand);
    log_message('NPM install output: '."\n".$npmInstallOutput);

    // Verify vite was installed
    $vitePackageCheck = 'cd '.escapeshellarg(REPO_PATH).' && ls -la node_modules/vite/package.json 2>&1';
    $vitePackageOutput = shell_exec($vitePackageCheck);
    log_message('Vite package check: '."\n".$vitePackageOutput);

    // Check if npm install was successful
    if (strpos($npmInstallOutput, 'npm ERR!') !== false || strpos($vitePackageOutput, 'No such file') !== false) {
        $deploymentSuccess = false;
        $deploymentOutput[] = 'NPM install failed or vite not installed: '.$npmInstallOutput;
        log_message('ERROR: NPM install failed or vite package missing.');
    } else {
        // Verify node_modules exists
        $nodeModulesCheck = 'cd '.escapeshellarg(REPO_PATH).' && ls -la node_modules/ | head -20 2>&1';
        $nodeModulesOutput = shell_exec($nodeModulesCheck);
        log_message('Node modules check: '."\n".$nodeModulesOutput);

        // Check if vite exists in node_modules
        $viteCheck = 'cd '.escapeshellarg(REPO_PATH).' && ls -la node_modules/.bin/vite 2>&1';
        $viteCheckOutput = shell_exec($viteCheck);
        log_message('Vite binary check: '."\n".$viteCheckOutput);

        // Try multiple methods to build
        // First try with npx which should find vite
        $buildCommand = 'cd '.escapeshellarg(REPO_PATH).' && npx vite build 2>&1';
        $buildOutput = shell_exec($buildCommand);

        // If npx fails, try with explicit path
        if (strpos($buildOutput, 'not found') !== false || strpos($buildOutput, 'npx: command not found') !== false) {
            log_message('First build attempt failed, trying with node_modules/.bin path...');
            $buildCommand = 'cd '.escapeshellarg(REPO_PATH).' && ./node_modules/.bin/vite build 2>&1';
            $buildOutput = shell_exec($buildCommand);
        }

        // If that also fails, try npm run build (which will use package.json script)
        if (strpos($buildOutput, 'not found') !== false || strpos($buildOutput, 'No such file') !== false) {
            log_message('Second build attempt failed, trying npm run build...');
            $buildCommand = 'cd '.escapeshellarg(REPO_PATH).' && npm run build 2>&1';
            $buildOutput = shell_exec($buildCommand);
        }

        log_message('Build output: '."\n".$buildOutput);

        // Check build output for errors
        if (strpos($buildOutput, 'npm ERR!') !== false || strpos($buildOutput, 'error') !== false || strpos($buildOutput, 'not found') !== false) {
            $deploymentSuccess = false;
            $deploymentOutput[] = 'Frontend build failed: '.$buildOutput;
            log_message('ERROR: Frontend build failed.');
        } else {
            $deploymentOutput[] = 'Frontend assets built successfully.';

            // Optional: Clean up dev dependencies after build to save space
            log_message('Cleaning up dev dependencies...');
            $cleanupCommand = 'cd '.escapeshellarg(REPO_PATH).' && npm prune --production 2>&1';
            $cleanupOutput = shell_exec($cleanupCommand);
            log_message('Cleanup output: '."\n".$cleanupOutput);
        }
    }

    // 3. Run Laravel Optimizations
    log_message('Running Laravel optimizations...');
    $laravelCommands = [
        'php artisan config:cache',
        'php artisan view:cache',
        'php artisan migrate --force',
    ];

    // Handle route cache separately as it can fail due to route conflicts
    $routeCacheCommand = 'cd '.escapeshellarg(REPO_PATH).' && php artisan route:cache 2>&1';
    $routeCacheOutput = shell_exec($routeCacheCommand);
    log_message('Route cache command output: '.$routeCacheOutput);
    if (strpos($routeCacheOutput, 'Unable to prepare route') !== false) {
        log_message('NOTICE: Route cache skipped due to route conflicts. This is non-fatal.');
        $deploymentOutput[] = 'Route caching skipped due to route name conflicts (non-fatal).';
    } elseif (strpos($routeCacheOutput, 'error') !== false || strpos($routeCacheOutput, 'failed') !== false) {
        log_message('WARNING: Route cache failed with unexpected error.');
        $deploymentOutput[] = 'Route caching failed with unexpected error (non-fatal).';
    } else {
        $deploymentOutput[] = 'Route cache completed successfully.';
    }

    foreach ($laravelCommands as $cmd) {
        $fullCommand = 'cd '.escapeshellarg(REPO_PATH).' && '.$cmd.' 2>&1';
        $cmdOutput = shell_exec($fullCommand);
        log_message("Command: $cmd");
        log_message('Output: '.$cmdOutput);

        if (strpos($cmdOutput, 'error') !== false || strpos($cmdOutput, 'failed') !== false) {
            $deploymentSuccess = false;
            $deploymentOutput[] = "Laravel command failed ($cmd): ".$cmdOutput;
            log_message("ERROR: Laravel command failed: $cmd");
        }
    }

    // 4. Clear and warm caches
    log_message('Clearing and warming caches...');
    $cacheCommands = [
        'php artisan cache:clear',
        'php artisan config:clear',
        'php artisan view:clear',
        'php artisan route:clear',
        'php artisan clear-compiled',
        'composer dump-autoload',
        // Temporarily removed package:discover due to Heroicons configuration error
        // 'php artisan package:discover --ansi',
        'php artisan config:cache',
        'php artisan view:cache',
    ];

    foreach ($cacheCommands as $cmd) {
        // Add HOME and COMPOSER_HOME for composer commands
        if (strpos($cmd, 'composer') !== false) {
            $fullCommand = 'cd '.escapeshellarg(REPO_PATH).' && HOME=/tmp COMPOSER_HOME=/tmp '.$cmd.' 2>&1';
        } else {
            $fullCommand = 'cd '.escapeshellarg(REPO_PATH).' && '.$cmd.' 2>&1';
        }
        $cmdOutput = shell_exec($fullCommand);
        log_message("Cache command: $cmd - Output: ".$cmdOutput);
    }

    // Handle route cache separately in cache warming (already done above)
    log_message('Route cache already handled in Laravel optimizations section.');

    // 5. Set proper permissions (if needed)
    log_message('Setting proper permissions...');
    $permissionCommands = [
        'chmod -R 755 '.escapeshellarg(REPO_PATH.'/storage'),
        'chmod -R 755 '.escapeshellarg(REPO_PATH.'/bootstrap/cache'),
    ];

    foreach ($permissionCommands as $cmd) {
        $cmdOutput = shell_exec($cmd.' 2>&1');
        log_message("Permission command: $cmd - Output: ".$cmdOutput);
    }

    // Final response
    if ($deploymentSuccess) {
        http_response_code(200);
        log_message('Deployment completed successfully.');
        echo 'Webhook processed successfully. Git pull and deployment tasks completed.'."\n";
        echo implode("\n", $deploymentOutput);
    } else {
        http_response_code(500);
        log_message('ERROR: Deployment completed with errors.');
        echo 'Git pull succeeded but deployment tasks had errors:'."\n";
        echo implode("\n", $deploymentOutput);
    }
} else {
    http_response_code(500); // Internal Server Error
    log_message('ERROR: Git pull command failed or produced errors.');
    echo 'Webhook processed, but git pull command failed.'."\n".'Output:'."\n".$output;
}

exit;
