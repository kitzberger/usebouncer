<?php

namespace Kitzberger\Usebouncer\Command;

use Kitzberger\Usebouncer\Service\Api;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestCommand extends Command
{
    /**
     * @var []
     */
    protected $conf = null;

    /**
     * @var SymfonyStyle
     */
    protected $io = null;

    private $api;

    public function __construct(Api $api)
    {
        $this->api = $api;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('CLI Tool to test mail addresses against usebouncer.com');

        $this->addArgument(
            'mail',
            InputArgument::REQUIRED,
            'Valid mail address',
            null
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->conf = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['queisser_cache_mgmt'];

        $this->io = new SymfonyStyle($input, $output);

        if ($output->isVerbose()) {
            $this->io->title($this->getDescription());
        }

        $mail = $input->getArgument('mail');

        if ($this->api->checkMail($mail)) {
            $this->io->success($mail . ' is valid.');
        } else {
            $this->io->error($mail . ' is invalid: ' . $this->api->getReason());
        }

        if (false) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
