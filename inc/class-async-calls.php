<?php
/**
 * Multisite Ultimate Async Calls implementation.
 *
 * @package WP_Ultimo
 * @subpackage Async_Calls
 * @since 2.0.7
 */

namespace WP_Ultimo;

use Amp\Iterator;
use Amp\Sync\LocalSemaphore;
use Amp\Sync\ConcurrentIterator;
use Amp\Http\Client\Request;
use Amp\Http\Client\Connection\DefaultConnectionPool;
use Amp\Socket\ClientTlsContext;
use Amp\Socket\ConnectContext;
use Amp\Http\Client\HttpClientBuilder;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Multisite Ultimate Async Calls implementation.
 *
 * @since 2.0.7
 */
class Async_Calls {

	/**
	 * Keeps a registry of the listeners.
	 *
	 * @var array
	 */
	public static $registry = [];

	/**
	 * Registers a new listener.
	 *
	 * @since 2.0.7
	 * @param string   $id The id of the listener.
	 * @param callable $callback A callback to be run.
	 * @param mixed    ...$args Arguments to be passed to the callback.
	 *
	 * @return void
	 */
	public static function register_listener($id, $callback, ...$args): void {

		self::$registry[ $id ] = [
			'callable' => $callback,
			'args'     => $args,
		];
	}

	/**
	 * Install the registered listeners.
	 *
	 * @since 2.0.7
	 * @return void
	 */
	public static function install_listeners(): void {

		foreach (self::$registry as $id => $listener) {
			add_action(
				"wp_ajax_wu_async_call_listener_{$id}",
				function () use ($listener) {

					try {
						$results = call_user_func_array($listener['callable'], $listener['args']);
					} catch (\Throwable $th) {
						wp_send_json_error($th);
					}

					wp_send_json_success($results);

					exit;
				}
			);
		}
	}

	/**
	 * Build the base URL for the listener calls.
	 *
	 * @since 2.0.7
	 *
	 * @param string $id The listener id.
	 * @param array  $args The additional args passed to the URL.
	 * @return string
	 */
	public static function build_base_url($id, $args) {

		return add_query_arg($args, admin_url('admin-ajax.php'));
	}

	/**
	 * Build the final URL to be called.
	 *
	 * @since 2.0.7
	 *
	 * @param string $id The listener id.
	 * @param int    $total The total number of records.
	 * @param int    $chunk_size The chunk size.
	 * @param array  $args Additional arguments to be passed.
	 * @return array The list of paginates URLs to call.
	 */
	public static function build_url_list($id, $total, $chunk_size, $args = []) {

		$pages = ceil($total / $chunk_size);

		$urls = [];

		for ($i = 1; $i <= $pages; $i++) {
			$urls[] = self::build_base_url(
				$id,
				array_merge(
					$args,
					[
						'action'   => "wu_async_call_listener_$id",
						'parallel' => 1,
						'page'     => $i,
						'per_page' => $chunk_size,
					]
				)
			);
		}

		return $urls;
	}

	/**
	 * Builds and returns the client that will handle the calls.
	 *
	 * @since 2.0.7
	 * @return \Amp\Http\Client\HttpClient;
	 */
	public static function get_client() {

		$client_tls_context = new ClientTlsContext('');

		$connect_base_context = new ConnectContext();

		$tls_context = $client_tls_context->withoutPeerVerification();

		$connect_context = $connect_base_context->withTlsContext($tls_context);

		$builder = new HttpClientBuilder();

		return $builder->usingPool(new DefaultConnectionPool(null, $connect_context))->build();
	}

	/**
	 * Run the parallel queue after everything is correctly enqueued.
	 *
	 * @since 2.0.7
	 *
	 * @param string  $id The listener id.
	 * @param array   $args Additional arguments to be passed.
	 * @param int     $total The total number of records.
	 * @param integer $chunk_size The chunk size to use.
	 * @param integer $parallel_threads The number of parallel threads to be run.
	 * @return true|\WP_Error
	 */
	public static function run($id, $args, $total, $chunk_size = 10, $parallel_threads = 3) {

		$client = self::get_client();

		$urls = self::build_url_list($id, $total, $chunk_size, $args);

		$coroutine = \Amp\call(
			static function () use ($id, $total, $chunk_size, $parallel_threads, $client, $urls) {

				$results = [];

				$chunker = new LocalSemaphore($parallel_threads);

				yield ConcurrentIterator\each(
					Iterator\fromIterable($urls),
					$chunker,
					function ($url) use (&$results, $client) {

						try {
							$request = new Request($url);

							$request->setTcpConnectTimeout(1000 * 1000);

							$request->setTlsHandshakeTimeout(1000 * 1000);

							$request->setTransferTimeout(1000 * 1000);

							$request->setHeader('cookie', wu_get_isset($_SERVER, 'HTTP_COOKIE', ''));

							$response = yield $client->request($request);

							$body = yield $response->getBody()->buffer();

							$results[ $url ] = json_decode($body);
						} catch (\Throwable $e) {
							throw $e;
						}
					}
				);

			return self::condense_results($results); // phpcs:ignore
			}
		);

		$responses = \Amp\Promise\wait($coroutine);

		return $responses;
	}

	/**
	 * Condense multiple results into one single result.
	 *
	 * @since 2.0.7
	 *
	 * @param array $results The different results returned by multiple calls.
	 * @return true|\WP_Error
	 */
	public static function condense_results($results) {

		foreach ($results as $result) {
			$status = wu_get_isset($result, 'success', false);

			if (false === $status) {
				return $result;
			}
		}

		return true;
	}
}
