<?php

/**
 * Nextcloud - Cospend
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Julien Veyssier <julien-nc@posteo.net>
 * @copyright Julien Veyssier 2019
 */

namespace OCA\Cospend\Command;

use OC\Core\Command\Base;
use OCA\Cospend\Service\ProjectService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportProject extends Base {

	public function __construct(private ProjectService $projectService) {
		parent::__construct();
	}

	protected function configure() {
		$this->setName('cospend:export-project')
			->setDescription('Export a project to CSV')
			->addArgument(
				'project_id',
				InputArgument::REQUIRED,
				'The id of the project you want to export'
			)
			->addArgument(
				'filename',
				InputArgument::OPTIONAL,
				'The name of the exported file'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$projectId = $input->getArgument('project_id');
		$name = $input->getArgument('filename');
		$project = $this->projectService->getProjectById($projectId);
		if ($project !== null) {
			$result = $this->projectService->exportCsvProject($projectId, $project['userid'], $name);
			if (array_key_exists('path', $result)) {
				$output->writeln(
					'Project "'.$projectId.'" exported in "'.$result['path'].
					'" of user "'.$project['userid'].'" storage'
				);
			} else {
				$output->writeln('Error: '.$result['message']);
			}
		} else {
			$output->writeln('Project '.$projectId.' not found');
		}
		return 0;
	}
}
