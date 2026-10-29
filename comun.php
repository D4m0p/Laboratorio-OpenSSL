<?php
// Funciones compartidas por todos los scripts del laboratorio.

const DIR_LLAVES = __DIR__ . '/keys';
const DIR_CERT = __DIR__ . '/keysCert';

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/**
 * Devuelve la configuración de OpenSSL para openssl_pkey_new / openssl_csr_new.
 * Solución 2 de la guía: busca openssl.cnf en las rutas comunes de XAMPP/WAMP.
 * En Linux/macOS no hace falta indicarlo y se devuelve solo el tipo de llave.
 */
function config_openssl(int $bits = 2048): array
{
    $cfg = ['private_key_bits' => $bits, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
    $candidatas = [
        'C:\\xampp\\php\\extras\\ssl\\openssl.cnf',
        'D:\\xampp\\php\\extras\\ssl\\openssl.cnf',
        'C:\\wamp64\\bin\\apache\\apache2.4.54\\conf\\openssl.cnf',
        dirname(PHP_BINARY) . '\\extras\\ssl\\openssl.cnf',
    ];
    if (PHP_OS_FAMILY === 'Windows') {
        foreach ($candidatas as $ruta) {
            if (is_file($ruta)) {
                $cfg['config'] = $ruta;
                break;
            }
        }
    }
    return $cfg;
}

function crear_par_de_llaves(int $bits = 2048): void
{
    $cfg = config_openssl($bits);
    $recurso = openssl_pkey_new($cfg);
    if ($recurso === false) {
        throw new RuntimeException('No se pudo generar la llave: ' . openssl_error_string());
    }
    openssl_pkey_export($recurso, $privadaPem, null, $cfg);
    $publicaPem = openssl_pkey_get_details($recurso)['key'];
    if (!is_dir(DIR_LLAVES)) {
        mkdir(DIR_LLAVES, 0700, true);
    }
    file_put_contents(DIR_LLAVES . '/private_key.pem', $privadaPem);
    file_put_contents(DIR_LLAVES . '/public_key.pem', $publicaPem);
}

/** Garantiza que exista el par de llaves y devuelve [privadaPem, publicaPem]. */
function cargar_llaves(): array
{
    if (!is_file(DIR_LLAVES . '/private_key.pem') || !is_file(DIR_LLAVES . '/public_key.pem')) {
        crear_par_de_llaves();
    }
    return [
        file_get_contents(DIR_LLAVES . '/private_key.pem'),
        file_get_contents(DIR_LLAVES . '/public_key.pem'),
    ];
}

function cabecera(string $titulo): void
{
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>' . e($titulo) . '</title><style>'
        . 'body{font-family:Arial,Helvetica,sans-serif;max-width:860px;margin:24px auto;padding:0 16px;color:#1b2430}'
        . 'h1{font-size:22px;border-bottom:3px solid #1f6f43;padding-bottom:8px}'
        . 'label{display:block;font-weight:bold;margin:14px 0 4px}'
        . 'input[type=text],input[type=password],textarea,select{width:100%;box-sizing:border-box;padding:8px;border:1px solid #9aa5b1;border-radius:4px;font:inherit}'
        . 'button{margin-top:14px;padding:9px 18px;background:#1f6f43;color:#fff;border:0;border-radius:4px;font:inherit;cursor:pointer}'
        . 'pre{background:#f1f4f7;border:1px solid #d3dae1;padding:10px;border-radius:4px;white-space:pre-wrap;word-break:break-all;font-size:12px}'
        . '.ok{color:#14732f;font-weight:bold}.mal{color:#b42318;font-weight:bold}'
        . '.caja{border:1px solid #d3dae1;border-radius:6px;padding:4px 16px 16px;margin:18px 0}'
        . 'nav a{margin-right:12px}'
        . '</style></head><body><nav><a href="index.php">Inicio</a></nav><h1>' . e($titulo) . '</h1>';
}

function pie(): void
{
    echo '</body></html>';
}

function resultado_verificacion(int $r): string
{
    return match ($r) {
        1 => '<span class="ok">Firma VÁLIDA: el mensaje no fue alterado.</span>',
        0 => '<span class="mal">Firma INVÁLIDA: el mensaje o la firma fueron alterados.</span>',
        default => '<span class="mal">Error al verificar: ' . e((string) openssl_error_string()) . '</span>',
    };
}
