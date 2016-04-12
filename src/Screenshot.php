<?php
namespace Heel;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use JonnyW\PhantomJs\Client;
Class Screenshot extends Command {

    public function configure()
    {
        $this->setname('screenshot')
            ->setDescription('Generates screenshot of target URL.')
            ->addArgument('URL', InputArgument::REQUIRED,
                'Target URL to generate screenshot from.');
    }

    public function take_screenshot($domain)
    {
        $client = Client::getInstance();
        $file_path = $domain . '_' . rand(1, 1000) . '.jpg';
        $width  = 1280;
        $height = 800;

        $request = $client->getMessageFactory()->createCaptureRequest('http://' . $domain, 'GET');
        $request->setOutputFile($file_path);
        $request->setViewportSize($width, $height);
        $request->setCaptureDimensions($width, $height, 0, 0);

        $response = $client->getMessageFactory()->createResponse();

        $client->send($request, $response);
	$this->file_path = $file_path;
    }


    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output ->writeln("Getting screenshot...");
	$this->take_screenshot($input->getArgument('URL'));
	$output ->writeln("Success! Screenshot at: " .  $this->file_path);
    }
}
?>

