<?php

namespace App\Exceptions;

use Exception;

class WHMCSException extends Exception
{
    protected $whmcsResponse;
    protected $whmcsResult;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Exception $previous = null,
        ?array $whmcsResponse = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->whmcsResponse = $whmcsResponse;
        $this->whmcsResult = $whmcsResponse['result'] ?? null;
    }

    /**
     * Get WHMCS response
     */
    public function getWhmcsResponse(): ?array
    {
        return $this->whmcsResponse;
    }

    /**
     * Get WHMCS result code
     */
    public function getWhmcsResult(): ?string
    {
        return $this->whmcsResult;
    }

    /**
     * Get WHMCS error message
     */
    public function getWhmcsMessage(): ?string
    {
        return $this->whmcsResponse['message'] ?? null;
    }

    /**
     * Check if this is a connection error
     */
    public function isConnectionError(): bool
    {
        return str_contains(strtolower($this->message), 'connection') ||
               str_contains(strtolower($this->message), 'timeout') ||
               str_contains(strtolower($this->message), 'curl');
    }

    /**
     * Check if this is an authentication error
     */
    public function isAuthError(): bool
    {
        return str_contains(strtolower($this->message), 'authentication') ||
               str_contains(strtolower($this->message), 'invalid identifier') ||
               str_contains(strtolower($this->message), 'invalid credentials') ||
               $this->whmcsResult === 'error' && 
               str_contains(strtolower($this->getWhmcsMessage() ?? ''), 'authentication');
    }

    /**
     * Check if entity already exists
     */
    public function isAlreadyExists(): bool
    {
        return str_contains(strtolower($this->message), 'duplicate') ||
               str_contains(strtolower($this->message), 'already exists') ||
               str_contains(strtolower($this->getWhmcsMessage() ?? ''), 'duplicate');
    }

    /**
     * Check if entity not found
     */
    public function isNotFound(): bool
    {
        return str_contains(strtolower($this->message), 'not found') ||
               str_contains(strtolower($this->message), 'invalid id') ||
               str_contains(strtolower($this->getWhmcsMessage() ?? ''), 'not found');
    }

    /**
     * Render exception as JSON response
     */
    public function render($request)
    {
        return response()->json([
            'success' => false,
            'error' => $this->getMessage(),
            'whmcs_result' => $this->whmcsResult,
            'whmcs_message' => $this->getWhmcsMessage(),
            'whmcs_response' => config('app.debug') ? $this->whmcsResponse : null,
        ], $this->getStatusCode());
    }

    /**
     * Get appropriate HTTP status code
     */
    protected function getStatusCode(): int
    {
        if ($this->isAuthError()) {
            return 401;
        }
        
        if ($this->isNotFound()) {
            return 404;
        }
        
        if ($this->isAlreadyExists()) {
            return 409;
        }
        
        if ($this->isConnectionError()) {
            return 503;
        }
        
        return 500;
    }
}

