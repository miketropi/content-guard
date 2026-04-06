<?php
/**
 * Determines whether the current page should be protected.
 *
 * @package WPContentGuard
 */

defined( 'ABSPATH' ) || exit;

final class COGU_Protection {

    private static ?self $instance = null;
    private ?bool         $cache   = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    public function init(): void {
        // Nothing; logic is lazy-evaluated on first call to should_protect().
    }

    /**
     * Returns true if protection scripts should be output on the current page.
     * Result is cached per-request.
     */
    public function should_protect(): bool {
        if ( null !== $this->cache ) {
            return $this->cache;
        }

        $this->cache = $this->evaluate();
        return $this->cache;
    }

    private function evaluate(): bool {
        // Never protect admin / REST / CLI.
        if ( is_admin() ) {
            return false;
        }
        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            return false;
        }
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            return false;
        }

        $s = COGU_Settings::all();

        // Skip logged-in users if option enabled.
        if ( empty( $s['protect_logged_in'] ) && is_user_logged_in() ) {
            return false;
        }

        // Check exclusion list first (works regardless of scope mode).
        if ( $this->is_excluded( $s ) ) {
            return false;
        }

        if ( 'all' === $s['scope'] ) {
            return true;
        }

        return $this->matches_selected_scope( $s );
    }

    private function is_excluded( array $s ): bool {
        $ids = $this->parse_ids( $s['scope_exclude_ids'] );
        if ( empty( $ids ) ) {
            return false;
        }
        return in_array( (int) get_queried_object_id(), $ids, true );
    }

    private function matches_selected_scope( array $s ): bool {
        // Match by post type.
        $post_types = (array) ( $s['scope_post_types'] ?? [] );
        if ( ! empty( $post_types ) && is_singular( $post_types ) ) {
            return true;
        }

        // Match by specific page / post IDs.
        $page_ids = $this->parse_ids( $s['scope_page_ids'] );
        if ( ! empty( $page_ids ) && in_array( (int) get_queried_object_id(), $page_ids, true ) ) {
            return true;
        }

        // Match by category (posts only).
        $categories = array_map( 'intval', (array) ( $s['scope_categories'] ?? [] ) );
        if ( ! empty( $categories ) && is_singular( 'post' ) ) {
            $post_cats = wp_get_post_categories( (int) get_queried_object_id(), [ 'fields' => 'ids' ] );
            if ( array_intersect( $categories, array_map( 'intval', $post_cats ) ) ) {
                return true;
            }
        }

        return false;
    }

    private function parse_ids( string $raw ): array {
        if ( '' === trim( $raw ) ) {
            return [];
        }
        return array_values( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) );
    }
}
