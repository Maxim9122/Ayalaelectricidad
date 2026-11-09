<?php

namespace App\Filters;

use App\Libraries\BloqueoVenta;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Mantiene vivo el bloqueo de la venta abierta mientras el usuario sigue trabajando,
 * y si otra sesión la tomó, limpia esta sesión y avisa.
 */
class BloqueoVentaFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Sin cookie de sesión (webhooks, robots) no hay nada que revisar ni sesión que abrir.
        if (empty($_COOKIE[config('App')->sessionCookieName])) {
            return null;
        }

        if (BloqueoVenta::tocar()) {
            return null;
        }

        $destino = strtolower($request->getMethod()) === 'get' ? uri_string() : 'catalogo';

        return BloqueoVenta::perdida($destino);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
