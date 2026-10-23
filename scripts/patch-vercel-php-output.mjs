import { readFileSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';

const configPath = join(process.cwd(), '.vercel/output/functions/api/index.func/.vc-config.json');
const config = JSON.parse(readFileSync(configPath, 'utf8'));
const builtin = '.vercel/builders/node_modules/vercel-php/dist/launchers/builtin.js';
const bridge = 'scripts/vercel-launcher-bridge.js';
const launcher = config.filePathMap?.['launcher.js'];

if (launcher !== builtin && launcher !== bridge) {
    console.error('Unexpected launcher.js mapping:', launcher);
    process.exit(1);
}

config.handler = 'launcher.js';
config.launcherType = 'Nodejs';
config.awsLambdaHandler = 'launcher.launcher';
config.shouldAddHelpers = false;
config.shouldAddSourcemapSupport = false;
config.environment = {
    ...config.environment,
    AWS_LAMBDA_HANDLER: 'launcher.launcher',
};
config.filePathMap['launcher-orig.js'] = config.filePathMap['launcher-orig.js'] || builtin;
config.filePathMap['launcher.js'] = bridge;

for (const [key, value] of Object.entries(config.filePathMap)) {
    const isGit = /(^|\/)\.git(\/|$)/.test(key) || /(^|\/)\.git(\/|$)/.test(value);
    const isSecretEnv = /(^|\/)\.env(\.[^/]+)?$/.test(key) && ! key.endsWith('.env.example');

    if (isGit || isSecretEnv) {
        delete config.filePathMap[key];
    }
}

writeFileSync(configPath, JSON.stringify(config));
console.log('Patched function handler to launcher.js');
