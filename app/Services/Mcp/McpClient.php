<?php

namespace App\Services\Mcp;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * HTTP client to call the Clockia MCP bridge endpoints.
 * Used by Chat Test "MCP mode" to verify the full MCP pipeline works.
 */
class McpClient
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.mcp.bridge_url', 'http://clockia.local/api/mcp'), '/');
        $this->token = config('services.mcp.bridge_token', '');
    }

    public function listTools(): array
    {
        return $this->get('/tools');
    }

    public function executeTool(string $tool, array $params): array
    {
        return $this->post('/tools/execute', [
            'tool' => $tool,
            'params' => $params,
        ]);
    }

    public function getBusinessProfile(int $negocioId): array
    {
        return $this->get("/businesses/{$negocioId}/profile");
    }

    public function getConversationRequirements(?int $negocioId = null): array
    {
        $qs = $negocioId ? "?negocio_id={$negocioId}" : '';

        return $this->get("/conversation/requirements{$qs}");
    }

    private function get(string $path): array
    {
        $response = Http::timeout(15)
            ->withHeaders([
                'Authorization' => "Bearer {$this->token}",
                'Accept' => 'application/json',
            ])
            ->get("{$this->baseUrl}{$path}");

        if (! $response->successful()) {
            throw new RuntimeException("MCP bridge GET {$path} failed: {$response->status()} {$response->body()}");
        }

        return $response->json();
    }

    private function post(string $path, array $data): array
    {
        $response = Http::timeout(15)
            ->withHeaders([
                'Authorization' => "Bearer {$this->token}",
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post("{$this->baseUrl}{$path}", $data);

        if (! $response->successful()) {
            throw new RuntimeException("MCP bridge POST {$path} failed: {$response->status()} {$response->body()}");
        }

        return $response->json();
    }
}
