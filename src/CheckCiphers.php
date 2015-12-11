<?php
namespace Heel;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
Class CheckCiphers extends Command {

    public function configure()
    {
        $this->setname('check-ciphers')
            ->setDescription('Scans available ciphers from remote web server.')
            ->addArgument('host', InputArgument::REQUIRED,
                'Target host to Scan');
    }

    public function getCiphers()
    {
        $cmd = "openssl ciphers 'ALL:eNULL' | sed -e 's/:/ /g'";
        $ciphers = explode(" ", exec($cmd)); 
        $this->cipherlist=$ciphers;
    }

    public function offer_ciphers($domain)
    {
        foreach ($this->cipherlist as $cipher)
        {
            {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://{$domain}/");
                curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, "{$cipher}");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                curl_close($ch);
                echo "{$cipher} = " . 
                    (boolval($result) ? 'True' : 'False' ) . "\n";
                usleep(250000);
            }

        }
    }        

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getCiphers();
        $output ->writeln("Checking Ciphers...");
        $this->offer_ciphers($input->getArgument('host'));
    }
}
?>

