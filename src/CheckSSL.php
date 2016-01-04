<?php

namespace Heel;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckSSL extends Command {

    public function configure()
    {
        $this->setname('check-ssl')
            ->setDescription('Retrieves information about SSL certificate in
            use by the remote host.')
            ->addArgument('URL', InputArgument::REQUIRED, 'Target URL to Scan')
            ->addOption('port', null, InputOption::VALUE_OPTIONAL, 
            'Specify port (default is 443)', '443');

    }

    public function make_request()
    {                                   
        $g = stream_context_create (                                            
            array("ssl" => array("capture_peer_cert" => true)));                
        $r = stream_socket_client(                                              
            "ssl://$this->target:$this->target_port", $errno, $errstr, 30,                  
                STREAM_CLIENT_CONNECT, $g);                                     
        $cont = stream_context_get_params($r);                                  
        $cert = openssl_x509_read($cont["options"]["ssl"]["peer_certificate"]); 
        $cert_data = openssl_x509_parse( $cert );                               
        openssl_x509_export($cert, $out, FALSE);                                
        $signature_algorithm = null;                                            
        if(preg_match('/^\s+Signature Algorithm:\s*(.*)\s*$/m', $out, $match))  
            $signature_algorithm = $match[1];                                   
        $this->sha_type=$signature_algorithm;                                   
        $this->common_name=$cert_data['subject']['CN'];                         
        $this->alternative_names=$cert_data['extensions']['subjectAltName'];    
        $this->issuer=$cert_data['issuer']['O'];                                
        $this->valid_from=date('m-d-Y H:i:s',                                   
            strval($cert_data['validFrom_time_t']));                            
        $this->valid_to=date('m-d-Y H:i:s',                                     
            strval($cert_data['validTo_time_t']));                              
        $this->parse_alternative_names();                                       
    }
    public function parse_alternative_names()
    {
        $this->alternative_names = explode(',',$this->alternative_names);
        foreach ($this->alternative_names as $row)
            {
                $this->alt_names[] = preg_replace('/DNS:/', '', $row);
            }
        $this->alt_domains = join(',', $this->alt_names);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->target = $input->getArgument('URL');
        $this->target_port = $input->getOption('port');
        $this->make_request();    
        $info = "<info>Main Domain:</info> " . $this->common_name . "\n" . 
            "<info>Alternative Domains:</info> " . "{$this->alt_domains}"  . "\n" . 
            "<info>Issuer:</info> " . $this->issuer . "\n" . 
            "<info>Creation Date:</info> " . $this->valid_from . 
            "\n" . "<info>Valid Until:</info> " . $this->valid_to . "\n" . 
            "<info>Signature Algorithm:</info> " . $this->sha_type;
        $output->writeln("{$info}");
    }
}

?>
