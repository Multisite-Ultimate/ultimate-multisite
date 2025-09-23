<?php
/**
 * Interface for notable models.
 *
 * @package WP_Ultimo
 * @subpackage Models\Interfaces
 * @since 2.0.0
 */

namespace WP_Ultimo\Models\Interfaces;

defined('ABSPATH') || exit;

/**
 * Notable interface.
 */
interface Notable {

	/**
	 * Get all the notes saved for this model.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Objects\Note[]
	 */
	public function get_notes();

	/**
	 * Adds a new note to this model.
	 *
	 * @since 2.0.0
	 *
	 * @param array|\WP_Ultimo\Objects\Note $note The note to add.
	 * @return bool
	 */
	public function add_note($note);

	/**
	 * Remove all notes related to this model.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function clear_notes();

	/**
	 * Remove one note related to this model.
	 *
	 * @since 2.0.0
	 *
	 * @param string $note_id The Note ID.
	 *
	 * @return bool
	 */
	public function delete_note($note_id);
}
