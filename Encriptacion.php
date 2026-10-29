<?php
require __DIR__ . '/comun.php';
cabecera('Encriptación simple con OpenSSL');

$resultado = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $texto = $_POST['texto'] ?? '';
    $clave = $_POST['clave'] ?? '';

    // --- Cifrado simétrico: AES-256-GCM (confidencialidad + integridad) ---
    $sal = random_bytes(16);
    $llaveAes = hash_pbkdf2('sha256', $clave, $sal, 100000, 32, true);
    $iv = random_bytes(12);
    $cifrado = openssl_encrypt($texto, 'aes-256-gcm', $llaveAes, OPENSSL_RAW_DATA, $iv, $tag);
    $paquete = base64_encode($sal . $iv . $tag . $cifrado);
    // Descifrado: se separa el paquete en sal, iv, tag y texto cifrado.
    $raw = base64_decode($paquete);
    $llaveD = hash_pbkdf2('sha256', $clave, substr($raw, 0, 16), 100000, 32, true);
    $claro = openssl_decrypt(substr($raw, 44), 'aes-256-gcm', $llaveD, OPENSSL_RAW_DATA, substr($raw, 16, 12), substr($raw, 28, 16));

    // --- Cifrado asimétrico: RSA con relleno OAEP ---
    [$privadaPem, $publicaPem] = cargar_llaves();
    openssl_public_encrypt($texto, $cifradoRsa, openssl_pkey_get_public($publicaPem), OPENSSL_PKCS1_OAEP_PADDING);
    openssl_private_decrypt($cifradoRsa, $claroRsa, openssl_pkey_get_private($privadaPem), OPENSSL_PKCS1_OAEP_PADDING);

    $resultado = '<div class="caja"><h2>AES-256-GCM (simétrico)</h2>'
        . '<p><b>Texto original:</b> ' . e($texto) . '</p>'
        . '<h3>Paquete cifrado (sal + IV + tag + texto, Base64)</h3><pre>' . e($paquete) . '</pre>'
        . '<p><b>Descifrado:</b> ' . e($claro === false ? '(falló: ' . openssl_error_string() . ')' : $claro) . '</p></div>'
        . '<div class="caja"><h2>RSA-OAEP (asimétrico)</h2>'
        . '<h3>Texto cifrado con la llave pública (Base64)</h3><pre>' . e(base64_encode($cifradoRsa)) . '</pre>'
        . '<p><b>Descifrado con la llave privada:</b> ' . e((string) $claroRsa) . '</p></div>';
}
?>
<form method="post">
  <label>Texto a cifrar</label>
  <textarea name="texto" rows="2"><?= e($_POST['texto'] ?? 'Número de tarjeta: 4111 1111 1111 1111') ?></textarea>
  <label>Contraseña para AES</label>
  <input type="password" name="clave" value="<?= e($_POST['clave'] ?? 'ClaveSegura#2026') ?>">
  <button>Cifrar y descifrar</button>
</form>
<?= $resultado ?>
<?php pie();
