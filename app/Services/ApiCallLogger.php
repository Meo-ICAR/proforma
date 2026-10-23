<?php

namespace App\Services;

use App\Models\ApiCall;
use GuzzleHttp\Promise\Create;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Registra in `api_calls` ogni chiamata HTTP verso un servizio esterno, come middleware Guzzle:
 * `Http::logged('mediafacile', 'pratiche')->get(...)`. Si salvano solo metodo, indirizzo (senza credenziali),
 * esito, durata ed errore; un guasto del registro non interrompe mai la chiamata.
 */
class ApiCallLogger
{
    public function __construct(private readonly int $errorExcerptLength = 1000) {}

    /**
     * @return callable(callable): callable
     */
    public function middleware(string $service, ?string $operation = null): callable
    {
        return fn (callable $handler): callable => function (RequestInterface $request, array $options) use ($handler, $service, $operation) {
            $startedAt = microtime(true);
            $calledAt = now();

            try {
                $promise = $handler($request, $options);
            } catch (Throwable $e) {
                $this->record($service, $operation, $request, $startedAt, $calledAt, null, false, null, $e->getMessage());

                throw $e;
            }

            return $promise->then(
                function (ResponseInterface $response) use ($request, $service, $operation, $startedAt, $calledAt) {
                    $status = $response->getStatusCode();
                    $this->record($service, $operation, $request, $startedAt, $calledAt, $status, $status < 400, $response->getBody()->getSize(), $status >= 400 ? $this->excerpt($response) : null);

                    return $response;
                },
                function (mixed $reason) use ($request, $service, $operation, $startedAt, $calledAt) {
                    $this->record($service, $operation, $request, $startedAt, $calledAt, null, false, null, $reason instanceof Throwable ? $reason->getMessage() : (string) $reason);

                    return Create::rejectionFor($reason);
                },
            );
        };
    }

    private function record(string $service, ?string $operation, RequestInterface $request, float $startedAt, mixed $calledAt, ?int $status, bool $successful, ?int $bytes, ?string $error): void
    {
        try {
            ApiCall::create([
                'service' => $service,
                'operation' => $operation,
                'method' => $request->getMethod(),
                'url' => mb_substr((string) $request->getUri()->withUserInfo(''), 0, 1000),
                'status' => $status,
                'successful' => $successful,
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'response_bytes' => $bytes,
                'error' => $error,
                'called_at' => $calledAt,
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function excerpt(ResponseInterface $response): string
    {
        $body = $response->getBody();
        $excerpt = mb_substr((string) $body, 0, $this->errorExcerptLength);
        $body->rewind();

        return 'HTTP '.$response->getStatusCode().': '.$excerpt;
    }
}
