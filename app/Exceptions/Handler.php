<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (PostTooLargeException $e, $request) {
            $mensaje = 'El archivo es demasiado grande. La foto debe pesar menos de 3 MB.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $mensaje], 413);
            }

            return redirect()->back()->with('error', $mensaje);
        });

        // Un formulario abierto de antes (o recargar el error 419) reenvía el
        // mismo token. En vez de dejar la página "419 | PAGE EXPIRED", se
        // abre de nuevo la pantalla para que el siguiente intento sea un GET.
        $this->renderable(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            $mensaje = 'La página se venció. Ya quedó lista otra vez: vuelve a intentar.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $mensaje], 419);
            }

            return redirect()->to($this->destinoTrasTokenVencido($request))->with('error', $mensaje);
        });
    }

    private function destinoTrasTokenVencido(Request $request): string
    {
        if ($request->is('login', 'login/*')) {
            return route('login');
        }

        $previous = $request->headers->get('referer') ?: url()->previous();
        $host = parse_url((string) $previous, PHP_URL_HOST);
        $mismoSitio = ! is_string($host) || strcasecmp($host, $request->getHost()) === 0;

        if (! is_string($previous) || $previous === '' || ! $mismoSitio) {
            return url('/');
        }

        return $previous;
    }
}
