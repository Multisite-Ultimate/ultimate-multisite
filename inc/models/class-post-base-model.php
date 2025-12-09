<?php
/**
 * The Post Base Model.
 *
 * @package WP_Ultimo
 * @subpackage Models
 * @since 2.0.0
 */

namespace WP_Ultimo\Models;

use WP_Ultimo\Models\Base_Model;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Post Base model class. Implements the Base Model.
 *
 * This class is the base class that is extended by all of our data types
 * with a title/content structure.
 *
 * @since 2.0.0
 */
class Post_Base_Model extends Base_Model {

	/**
	 * Author ID.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $author_id = '';

	/**
	 * Post type.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $type = '';

	/**
	 * Post title.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $title = '';

	/**
	 * Post title.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name = '';

	/**
	 * Post content.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $content = '';

	/**
	 * Post excerpt.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $excerpt = '';

	/**
	 * The post list order. Useful when ordering posts in a list.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $list_order = 10;

	/**
	 * The post status.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $status = '';

	/**
	 * Query Class to the static query methods.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = \WP_Ultimo\Database\Posts\Post_Query::class;


	/**
	 * @return $this
	 */
	public function load_attributes_from_post() {
		parent::load_attributes_from_post();

		if (isset($_POST['content'])) { // phpcs:ignore WordPress.Security.NonceVerification
			$this->set_content(sanitize_post_field('post_content', wp_unslash($_POST['content']), $this->get_id(), 'db')); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification
		}

		return $this;
	}

	/**
	 * Get author ID.
	 *
	 * @return int
	 */
	public function get_author_id() {

		return $this->author_id;
	}

	/**
	 * Set author ID.
	 *
	 * @param int $author_id The author ID.
	 */
	public function set_author_id($author_id): void {

		$this->author_id = $author_id;
	}

	/**
	 * Get post type.
	 *
	 * @return string
	 */
	public function get_type() {

		return $this->type;
	}

	/**
	 * Set post type.
	 *
	 * @param string $type Post type.
	 */
	public function set_type($type): void {

		$this->type = $type;
	}

	/**
	 * Get post title.
	 *
	 * @return string
	 */
	public function get_title() {

		return $this->title;
	}

	/**
	 * Set post title.
	 *
	 * @param string $title Post title.
	 */
	public function set_title($title): void {

		$this->title = $title;
	}

	/**
	 * Get post content.
	 *
	 * @return string
	 */
	public function get_content() {
		/**
		 * WordPress add slashes to the content, so we need to remove them when retrieve the data.
		 * Also we need to add the paragraphs and line breaks back.
		 */
		return wpautop(stripslashes($this->content));
	}

	/**
	 * Set post content.
	 *
	 * @param string $content Post content.
	 */
	public function set_content($content): void {

		$this->content = $content;
	}

	/**
	 * Get post excerpt.
	 *
	 * @return string
	 */
	public function get_excerpt() {

		return $this->excerpt;
	}

	/**
	 * Set post excerpt.
	 *
	 * @param string $excerpt Post excerpt.
	 */
	public function set_excerpt($excerpt): void {

		$this->excerpt = $excerpt;
	}

	/**
	 * Get post creation date.
	 *
	 * @return string
	 */
	public function get_date_created() {

		return $this->date_created;
	}

	/**
	 * Set post creation date.
	 *
	 * @param string $date_created Post creation date.
	 */
	public function set_date_created($date_created): void {

		$this->date_created = $date_created;
	}

	/**
	 * Get post last modification date.
	 *
	 * @return string
	 */
	public function get_date_modified() {

		return $this->date_modified;
	}

	/**
	 * Set post last modification date.
	 *
	 * @param string $date_modified Post last modification date.
	 */
	public function set_date_modified($date_modified): void {

		$this->date_modified = $date_modified;
	}

	/**
	 * Get the post list order.
	 *
	 * @return int
	 */
	public function get_list_order() {

		return $this->list_order;
	}

	/**
	 * Set the post list order.
	 *
	 * @param int $list_order The post list order.
	 */
	public function set_list_order($list_order): void {

		$this->list_order = $list_order;
	}

	/**
	 * Get the post status.
	 *
	 * @return string
	 */
	public function get_status() {

		return $this->status;
	}

	/**
	 * Set the post status.
	 *
	 * @param string $status The post status.
	 */
	public function set_status($status): void {

		$this->status = $status;
	}

	/**
	 * Save (create or update) the model on the database,
	 * setting creation and modification dates first.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function save() {

		if ( ! $this->author_id) {
			$this->author_id = get_current_user_id();
		}

		if ( ! $this->status) {

			/**
			 * Filters the object data before it is stored into the database.
			 *
			 * @since 2.0.0
			 *
			 * @param string     $status    The default status.
			 * @param string     $post_type The post type.
			 * @param Base_Model $this      The object instance.
			 */
			$this->status = apply_filters('wu_post_default_status', 'draft', $this->type, $this);
		}

		return parent::save();
	}
}
