<?php

/**
 * Posts comment to Pull Request through GitHub API.
 */

namespace flo\Command\PullRequest;

use flo\Drupal;
use flo\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Github;


class CommentCommand extends Command {

  protected function configure() {
    $this->setName('pr-comment')
         ->setDescription('Generic command to add comment to Pull Request.')
         ->addOption(
           'body',
           null,
           InputOption::VALUE_REQUIRED,
           'If set, the output will be posted to github as a comment on the relevant Pull Request'
         );
  }

  /**
   * Process the pr-comment command.
   *
   * GH API: POST /repos/:owner/:repo/issues/:number/comments {"body": "Me too"}
   *
   * {@inheritDoc}
   *
   * This command takes in environment variables for knowing what branch to target.
   * If no branch is passed in the environment variable
   *
   * @param InputInterface $input
   * @param OutputInterface $output
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $targetBranch = getenv(self::GITHUB_PULL_REQUEST_TARGET_BRANCH);
    $targetRef = getenv(self::GITHUB_PULL_REQUEST_COMMIT);
    $targetURL = getenv(self::JENKINS_BUILD_URL);
    $pullRequest = getenv(self::GITHUB_PULL_REQUEST_ID);
    $github = $this->getGithub();

    if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
      $output->writeln("<info>target branch:{$targetBranch}</info>");
      $output->writeln("<info>target ref: {$targetRef}</info>");
      $output->writeln("<info>target URL: {$targetURL}</info>");
      $output->writeln("<info>pull request: {$pullRequest}</info>");
    }

    if ($input->getOption('body') && !empty($pullRequest)) {
      $this->addGithubComment($pullRequest, $input->getOption('body'));
      $github->api('issue')->comments()->create(
        $this->getConfigParameter('organization'),
        $this->getConfigParameter('repository'),
        $pullRequest,
        array('body' => $input->getOption('body'))
      );
      $output->writeln("<info>Posted to Github Comment API.</info>");
    }

  }

}
