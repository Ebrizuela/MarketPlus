<?php

function normalizarRut(string $rut): string
{
    return strtoupper(str_replace(['.', '-'], '', trim($rut)));
}

function validarRut(string $rut): bool
{
    $rut = trim($rut);
    if (!preg_match('/^(?:[0-9]{7,8}|[0-9]{1,2}(?:\.[0-9]{3}){2})-[0-9kK]$/D', $rut)) {
        return false;
    }

    $rutNormalizado = normalizarRut($rut);
    $digitos = substr($rutNormalizado, 0, -1);
    $digitoVerificador = substr($rutNormalizado, -1);
    $suma = 0;
    $multiplicador = 2;

    for ($i = strlen($digitos) - 1; $i >= 0; $i--) {
        $suma += (int) $digitos[$i] * $multiplicador;
        $multiplicador = $multiplicador === 7 ? 2 : $multiplicador + 1;
    }

    $resultado = 11 - ($suma % 11);
    $digitoEsperado = $resultado === 11
        ? '0'
        : ($resultado === 10 ? 'K' : (string) $resultado);

    return $digitoVerificador === $digitoEsperado;
}