<?php
/**
 * Command for updating translations.
 *
 * @since 2.0.0
 *
 * @package Required\Traduttore\CLI
 */

namespace Required\Traduttore\CLI;

use Required\Traduttore\{ProjectLocator, LoaderFactory, Updater, Runner};
use WP_CLI;
use WP_CLI_Command;
use function WP_CLI\Utils\get_flag_value;

/**
 * Updates project translations from GitHub repository.
 *
 * Finds the project the repository belongs to and updates the translations accordingly.
 *
 * ## OPTIONS
 *
 * <project|url>
 * : Project path / ID or GitHub repository URL, e.g. https://github.com/wearerequired/required-valencia
 *
 * [--delete]
 * : Whether to first delete the existing local repository or not.
 *
 * ## EXAMPLES
 *
 *     # Update translations from repository URL.
 *     $ wp traduttore update https://github.com/wearerequired/required-valencia
 *     Success: Updated translations for project (ID: 123)!
 *
 *     # Update translations from project path.
 *     $ wp traduttore update required/required-valencia
 *     Success: Updated translations for project (ID: 123)!
 *
 *     # Update translations from project ID.
 *     $ wp traduttore update 123
 *     Success: Updated translations for project (ID: 123)!
 *
 * @since 2.0.0
 */
class UpdateCommand extends WP_CLI_Command {
	/**
	 * Class constructor.
	 *
	 * Automatically called by WP-CLI.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Command args.
	 * @param array $assoc_args Associative args.
	 */
	public function __invoke( $args, $assoc_args ) {
		$delete  = get_flag_value( $assoc_args, 'delete', false );
		$locator = new ProjectLocator( $args[0] );
		$project = $locator->get_project();

		if ( ! $project ) {
			WP_CLI::error( 'Project not found' );
		}

		$loader = ( new LoaderFactory() )->get_loader( $project );

		if ( ! $loader ) {
			WP_CLI::error( 'Invalid project type' );
		}

		$updater = new Updater( $project );

		$runner = new Runner( $loader, $updater );

		if ( $delete ) {
			$runner->delete_local_repository();
		}

		$success = $runner->run();

		if ( $success ) {
			WP_CLI::success( sprintf( 'Updated translations for project (ID: %d)!', $project->id ) );

			return;
		}

		WP_CLI::warning( sprintf( 'Could not update translations for project (ID: %d)!', $project->id ) );
	}
}