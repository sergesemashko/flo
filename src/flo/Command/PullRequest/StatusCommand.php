<?php

/**
 * Posts statuses to GitHub Pull Request.
 */

namespace flo\Command\PullRequest;

use flo\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Github;


class StatusCommand extends Command {

  protected static $STATUSES = array('success', 'failure', 'error', 'pending');

  protected function configure() {
    $this->setName('pr-status')
      ->setDescription('runs phpunit and report to GH PR Status.')
      ->addOption(
        'context',
        'c',
        InputOption::VALUE_REQUIRED,
        'The commit to deploy on GitHub using their Deployment API.'
      )
      ->addOption(
        'status',
        's',
        InputOption::VALUE_REQUIRED,
        'The commit to deploy on GitHub using their Deployment API.'
      )
      ->addOption(
        'description',
        'd',
        InputOption::VALUE_OPTIONAL,
        'If set, the output will be posted to github as a comment on the relevant Pull Request'
      )
      ->addOption(
        'target_url',
        null,
        InputOption::VALUE_OPTIONAL,
        'The commit to deploy on GitHub using their Deployment API.'
      );
  }

  /**
   * Post status to GitHub PullRequest.
   *
   * {@inheritDoc}
   *
   * This command takes in environment variables for knowing what branch to target.
   * If no branch is passed in the environment variable
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $targetRef = getenv(self::GITHUB_PULL_REQUEST_COMMIT);
    $targetURL = getenv(self::JENKINS_BUILD_URL);
    $pullRequest = getenv(self::GITHUB_PULL_REQUEST_ID);
    $gh_status_state = $input->getOption('status');
    $context = $input->getOption('context');
    $description = $input->getOption('description');
    $github = $this->getGithub();

    if (!in_array($gh_status_state, static::$STATUSES)) {
      throw new \Exception('--status can accept only one of these values: ' . implode(', ', static::$STATUSES));
    }

    // override system target_url value if specified by command parameter
    if ($input->getOption('target_url')) {
      $targetURL = $input->getOption('target_url');
    }

    if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
      $output->writeln("<info>target ref: {$targetRef}</info>");
      $output->writeln("<info>target URL: {$targetURL}</info>");
      $output->writeln("<info> pull request: {$pullRequest}</info>");
    }

    $output->writeln("<info>Posting to Github Status API.</info>");
    $github->api('repo')->statuses()->create(
      $this->getConfigParameter('organization'),
      $this->getConfigParameter('repository'),
      $targetRef,
      array(
        'state' => $gh_status_state,
        'target_url' => $targetURL,
        'description' => $description,
        'context' => $context,
      )
    );

  }

}
