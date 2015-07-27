<?php

namespace flo\Command\PullRequest;

use flo\Drupal;
use flo\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Github;


class CheckoutCommand extends Command {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('pr-checkout')
      ->setDescription('Checkout a specific pull-request to a solo environment.')
      ->addArgument(
        'pull-request',
        InputArgument::REQUIRED,
        'The pull-request number to be pulled.'
      );
  }

  /**
   * Process pr-checkout job.
   *
   * - Takes the current working Environment and rsync it to where they belong (config: pr_directories).
   *
   * @param InputInterface $input
   * @param OutputInterface $output
   * @return int|void
   * @throws \Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output) {

    $pr_number = $input->getArgument('pull-request');
    if (!is_numeric($pr_number)) {
      throw new \Exception("PR must be a number.");
    }

    // Lets rsync this workspace now.
    $pull_request = $this->getConfigParameter('pull_request');
    $path = "{$pull_request['prefix']}-{$pr_number}.{$pull_request['domain']}";
    $pr_directories = $this->getConfigParameter('pr_directories');
    if (empty($pr_directories)) {
      throw new \Exception("You must have a pr_directory set in your flo config.");
    }

    if ($output->getVerbosity() == OutputInterface::VERBOSITY_VERY_VERBOSE) {
      $output->writeln("<info>rsyncing to {$pr_directories}{$path}");
      $output->writeln("<info>verbose: Syncing current directory into pr env.</info>");
    }

    $this->checkoutWorkspace($pr_directories . $path);

    $output->writeln("<info>PR #$pr_number has been checked out into {$pr_directories}{$path}.</info>");
  }
}
