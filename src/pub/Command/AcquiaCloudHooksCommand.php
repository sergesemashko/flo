<?php

namespace pub\Command;

use pub\Config;
use pub\ProjectConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml;
use Github;

class AcquiaCloudHooksCommand extends Command {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('acquia-init')
      ->setDescription('Set up Acquia Cloud hooks for API Calls.');
  }


  protected function execute(InputInterface $input, OutputInterface $output) {
    $project_config = new ProjectConfig();
    $project_config->load();
  }
}
