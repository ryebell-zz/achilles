<?php
$g = stream_context_create (array("ssl" => array("capture_peer_cert" => true)));
$r = stream_socket_client("ssl://$DOMAIN:$PORT", $errno, $errstr, 30,
    STREAM_CLIENT_CONNECT, $g);
$cont = stream_context_get_params($r);
$cert = openssl_x509_read($cont["options"]["ssl"]["peer_certificate"]);
$cert_data = openssl_x509_parse( $cert );
$common_name = $cert_data['subject']['CN'];
$issuer = $cert_data['issuer']['O'];
$valid_from = $cert_data['validFrom_time_t']; 
$valid_to = $cert_data['validTo_time_t'];

echo "Common Name: " . $common_name . "\n" . "Issuing CA: " . $issuer . "\n" . "Valid from: " . $valid_from . "\n" . "Valid to: " . $valid_to . "\n";
?>
