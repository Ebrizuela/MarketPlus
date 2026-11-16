<?php
function obtenerImagenProducto(array $producto, string $directorioRaiz): ?string
{
    $raizReal = realpath($directorioRaiz);
    if ($raizReal === false) {
        return null;
    }

    $candidatos = [];
    if (!empty($producto['imagen'])) {
        $candidatos[] = (string) $producto['imagen'];
    }

    $idProducto = (int) ($producto['id_producto'] ?? 0);
    if ($idProducto > 0) {
        foreach (glob($raizReal . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $idProducto . '.*') ?: [] as $archivo) {
            $candidatos[] = 'uploads/' . basename($archivo);
        }
        foreach (glob($raizReal . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $idProducto . '.*') ?: [] as $archivo) {
            if (in_array(strtolower(pathinfo($archivo, PATHINFO_EXTENSION)), ['avif', 'gif', 'jpeg', 'jpg', 'png', 'webp'], true)) {
                $candidatos[] = 'img/' . basename($archivo);
            }
        }
    }

    foreach ($candidatos as $candidato) {
        $rutaRelativa = str_replace('\\', '/', ltrim($candidato, '/\\'));
        if ($rutaRelativa === '' || in_array('..', explode('/', $rutaRelativa), true)) {
            continue;
        }

        $rutaReal = realpath($raizReal . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rutaRelativa));
        $prefijoRaiz = rtrim($raizReal, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if ($rutaReal !== false
            && strncasecmp($rutaReal, $prefijoRaiz, strlen($prefijoRaiz)) === 0
            && is_file($rutaReal)) {
            return rtrim(BASE_URL, '/') . '/' . $rutaRelativa;
        }
    }

    return null;
}
