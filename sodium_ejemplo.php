<?php
require __DIR__ . '/comun.php';
cabecera('Ejemplo con Libsodium (ext-sodium)');

$resultado = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = $_POST['mensaje'] ?? '';
    $password = $_POST['password'] ?? '';

    // 1. Cifrado autenticado (XSalsa20-Poly1305).
    $llave = sodium_crypto_secretbox_keygen();
    $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    $cifrado = sodium_crypto_secretbox($mensaje, $nonce, $llave);
    $claro = sodium_crypto_secretbox_open($cifrado, $nonce, $llave);
    // Alteramos un byte para demostrar que la integridad se detecta.
    $alterado = $cifrado;
    $alterado[0] = chr(ord($alterado[0]) ^ 1);
    $claroAlterado = sodium_crypto_secretbox_open($alterado, $nonce, $llave);

    // 2. Firma digital Ed25519.
    $par = sodium_crypto_sign_keypair();
    $secreta = sodium_crypto_sign_secretkey($par);
    $publica = sodium_crypto_sign_publickey($par);
    $firma = sodium_crypto_sign_detached($mensaje, $secreta);
    $valida = sodium_crypto_sign_verify_detached($firma, $mensaje, $publica);
    $validaAlt = sodium_crypto_sign_verify_detached($firma, $mensaje . '!', $publica);

    // 3. Hash de contraseña con Argon2id.
    $hash = sodium_crypto_pwhash_str($password, SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE, SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE);
    $okPass = sodium_crypto_pwhash_str_verify($hash, $password);
    $malPass = sodium_crypto_pwhash_str_verify($hash, $password . 'x');

    $si = '<span class="ok">sí</span>';
    $no = '<span class="mal">no</span>';
    $resultado = '<div class="caja"><h2>1. Cifrado autenticado (secretbox)</h2>'
        . '<p><b>Nonce (hex):</b></p><pre>' . e(sodium_bin2hex($nonce)) . '</pre>'
        . '<p><b>Texto cifrado (hex):</b></p><pre>' . e(sodium_bin2hex($cifrado)) . '</pre>'
        . '<p><b>Descifrado:</b> ' . e((string) $claro) . '</p>'
        . '<p><b>¿Se acepta el texto cifrado con un byte alterado?</b> ' . ($claroAlterado === false ? $no . ' (rechazado)' : $si) . '</p></div>'
        . '<div class="caja"><h2>2. Firma digital Ed25519</h2>'
        . '<p><b>Llave pública (hex):</b></p><pre>' . e(sodium_bin2hex($publica)) . '</pre>'
        . '<p><b>Firma (hex, 64 bytes):</b></p><pre>' . e(sodium_bin2hex($firma)) . '</pre>'
        . '<p><b>¿Firma válida para el mensaje original?</b> ' . ($valida ? $si : $no) . '</p>'
        . '<p><b>¿Firma válida si el mensaje cambia?</b> ' . ($validaAlt ? $si : $no) . '</p></div>'
        . '<div class="caja"><h2>3. Hash de contraseña Argon2id</h2><pre>' . e($hash) . '</pre>'
        . '<p><b>¿Contraseña correcta?</b> ' . ($okPass ? $si : $no) . ' &nbsp; <b>¿Contraseña incorrecta?</b> ' . ($malPass ? $si : $no) . '</p></div>';
}
?>
<form method="post">
  <label>Mensaje</label>
  <textarea name="mensaje" rows="2"><?= e($_POST['mensaje'] ?? 'Historial médico del paciente 0042') ?></textarea>
  <label>Contraseña a proteger con Argon2id</label>
  <input type="text" name="password" value="<?= e($_POST['password'] ?? 'MiContraseña#Segura1') ?>">
  <button>Ejecutar ejemplo</button>
</form>
<?= $resultado ?>
<?php pie();
