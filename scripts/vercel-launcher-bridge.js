process.env.LAMBDA_TASK_ROOT = process.env.LAMBDA_TASK_ROOT || '/var/task';

console.error('php-launcher-bridge: start');

const orig = require('./launcher-orig.js');

exports.launcher = async (event, context) => {
    console.error('php-launcher-bridge: event', event && event.httpMethod, event && event.Action, typeof (event && event.body));
    const request = { ...event };

    if (Array.isArray(request.body)) {
        request.body = Buffer.from(request.body);
    } else if (typeof request.body === 'string') {
        request.body = Buffer.from(request.body, request.isBase64Encoded ? 'base64' : 'utf8');
    } else {
        request.body = undefined;
    }

    const headers = request.headers || {};
    request.host = request.host || headers['x-forwarded-host'] || headers.host;

    return orig.launcher(request, context);
};
