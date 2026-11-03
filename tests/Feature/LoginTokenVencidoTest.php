<?php

namespace Tests\Feature;

use App\Exceptions\Handler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Tests\TestCase;

class LoginTokenVencidoTest extends TestCase
{
    public function test_el_login_vencido_vuelve_al_formulario(): void
    {
        $request = Request::create('/login', 'POST', [
            'email' => 'persona@repro.com',
            'password' => 'secreta',
        ]);
        $this->app->instance('request', $request);

        $response = $this->app->make(Handler::class)->render(
            $request,
            new TokenMismatchException('CSRF token mismatch.')
        );

        $this->assertTrue($response->isRedirect(route('login')));
        $this->assertSame(
            'La página se venció. Ya quedó lista otra vez: vuelve a intentar.',
            session('error')
        );
    }

    public function test_un_formulario_vencido_regresa_a_su_pagina(): void
    {
        $request = Request::create('/cuestionario/abc/seccion/1', 'POST');
        $request->headers->set('referer', url('/cuestionario/abc/seccion/1'));
        $this->app->instance('request', $request);
        $this->app['url']->setRequest($request);

        $response = $this->app->make(Handler::class)->render(
            $request,
            new TokenMismatchException('CSRF token mismatch.')
        );

        $this->assertTrue($response->isRedirect(url('/cuestionario/abc/seccion/1')));
    }

    public function test_un_referer_externo_no_se_sigue(): void
    {
        $request = Request::create('/cuestionario/abc/seccion/1', 'POST');
        $request->headers->set('referer', 'https://ejemplo.test/robar');

        $response = $this->app->make(Handler::class)->render(
            $request,
            new TokenMismatchException('CSRF token mismatch.')
        );

        $this->assertTrue($response->isRedirect(url('/')));
    }

    public function test_una_peticion_json_vencida_sigue_en_419(): void
    {
        $request = Request::create('/login', 'POST');
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->app->make(Handler::class)->render(
            $request,
            new TokenMismatchException('CSRF token mismatch.')
        );

        $this->assertSame(419, $response->getStatusCode());
    }
}
