<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class Handler extends ExceptionHandler
{
    protected $levels = [
        //
    ];

    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if ($e instanceof QueryException) {
                Log::channel('database')->error('Database Query Exception', [
                    'message' => $e->getMessage(),
                    'sql' => $e->getSql() ?? 'N/A',
                    'bindings' => $e->getBindings() ?? [],
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            if (str_contains($e->getMessage(), 'prepared statement')) {
                Log::channel('database')->critical('Prepared Statement Error', [
                    'message' => $e->getMessage(),
                    'connection' => config('database.default'),
                    'time' => now()->toDateTimeString()
                ]);
            }
        });
    }
    
    public function render($request, Throwable $e)
    {
        if ($e instanceof QueryException && str_contains($e->getMessage(), 'prepared statement')) {
            return response()->view('errors.database', [
                'message' => 'Koneksi database sedang sibuk. Silakan coba beberapa saat lagi.'
            ], 503);
        }
        
        return parent::render($request, $e);
    }
}