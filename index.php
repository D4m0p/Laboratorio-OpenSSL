<?php
require __DIR__ . '/comun.php';
cabecera('Laboratorio: Seguridad en PHP con OpenSSL');
?>
<p>Desarrollo Web VII: PHP y MySQL. Universidad Tecnológica de Panamá.</p>
<ul>
  <li><a href="generar_llaves.php">Generar llaves RSA y certificado X.509</a></li>
  <li><a href="firma7.php">firma7: firma digital y verificación con llaves en disco</a></li>
  <li><a href="firmaOtra.php">firmaOtra: firma con algoritmo a elección y verificación con el certificado</a></li>
  <li><a href="FirmarMensaje.php">FirmarMensaje: emitir y verificar un mensaje firmado</a></li>
  <li><a href="Encriptacion.php">Encriptación simple con OpenSSL (AES-256-GCM y RSA-OAEP)</a></li>
  <li><a href="sodium_ejemplo.php">Ejemplo con Libsodium (cifrado autenticado y firma Ed25519)</a></li>
</ul>
<?php pie();
