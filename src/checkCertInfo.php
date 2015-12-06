<?php

namespace Heel;

class CertificateScanner {
    private $target;
    private $target_port;

    public function set_parameters($target, $target_port)
    {
        $this->target=$target;
        $this->target_port=$target_port;
    }

    public function scan_certificate()
    {
        $g = stream_context_create (array("ssl" => array("capture_peer_cert" => true)));
        $r = stream_socket_client("ssl://$this->target:$this->target_port", $errno, $errstr, 30,
                STREAM_CLIENT_CONNECT, $g);
        $cont = stream_context_get_params($r);
        $cert = openssl_x509_read($cont["options"]["ssl"]["peer_certificate"]);
        $cert_data = openssl_x509_parse( $cert );
        $this->common_name=$cert_data['subject']['CN'];
        $this->issuer=$cert_data['issuer']['O'];
        $this->valid_from=strval($cert_data['validFrom_time_t']);
        $this->valid_to=strval($cert_data['validTo_time_t']);
    }
}

?>
