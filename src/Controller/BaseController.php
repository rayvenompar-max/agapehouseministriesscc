<?php
/**
 * Controller\BaseController
 *
 * Shared helpers available to every controller.
 */
declare(strict_types=1);

namespace Controller;

abstract class BaseController
{
    /** Send a JSON response and exit. */
    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /** Send a JSON success envelope. */
    protected function success(mixed $data = null, string $message = 'OK', int $status = 200): void
    {
        $this->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /** Send a JSON error envelope. */
    protected function error(string $message, int $status = 400): void
    {
        $this->json([
            'status'  => 'error',
            'message' => $message,
        ], $status);
    }

    /** Read and decode the JSON request body. Returns array or null. */
    protected function getJsonBody(): ?array
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            return null;
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    /** Safely get a string field from an array. */
    protected function str(array $data, string $key, string $default = ''): string
    {
        return isset($data[$key]) ? (string) $data[$key] : $default;
    }

    /** Safely get a float field from an array. */
    protected function float(array $data, string $key, float $default = 0.0): float
    {
        return isset($data[$key]) ? (float) $data[$key] : $default;
    }

    /** Safely get an int field from an array. */
    protected function int(array $data, string $key, int $default = 0): int
    {
        return isset($data[$key]) ? (int) $data[$key] : $default;
    }
}
