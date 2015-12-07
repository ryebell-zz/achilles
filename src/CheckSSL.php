<?php

namespace Heel;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#require '../vendor/autoload.php';

class CheckSSL extends Command {

    public function configure()
    {
        $this->setname('check-ssl')
            ->setDescription('Retrieves information about SSL certificate in use by the remote host.')
            ->addArgument('URL', InputArgument::REQUIRED, 'Target URL to Scan')
            ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'Specify port (default is 443)', '443');

    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $input->getArgument('URL');
        $target_port = $input->getOption('port');
        $g = stream_context_create (array("ssl" => array("capture_peer_cert" => true)));
        $r = stream_socket_client("ssl://$target:$target_port", $errno, $errstr, 30,
                STREAM_CLIENT_CONNECT, $g);
        $cont = stream_context_get_params($r);
        $cert = openssl_x509_read($cont["options"]["ssl"]["peer_certificate"]);
        $cert_data = openssl_x509_parse( $cert );
        $this->common_name=$cert_data['subject']['CN'];
        $this->issuer=$cert_data['issuer']['O'];
        $this->valid_from=date('m-d-Y H:i:s', strval($cert_data['validFrom_time_t']));
        $this->valid_to=date('m-d-Y H:i:s', strval($cert_data['validTo_time_t']));
        $info = "Main Domain: " . $this->common_name . "\n" . "Issuer: " . $this->issuer . "\n" . "Creation Date: " . $this->valid_from . "\n" . "Valid Until: " . $this->valid_to;
        $output->writeln("<info>{$info}</info>");
    }
}

?>
