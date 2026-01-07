<?php
if (!defined('ABSPATH')) exit;

class Beauty_Company {

    public static function get_company_id() {
        $user_id = get_current_user_id();

        if (!$user_id) {
            return 0;
        }

        global $wpdb;

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}beauty_companies WHERE user_id = %d",
                $user_id
            )
        );
    }

    public static function is_owner() {
        return self::get_company_id() > 0;
    }

    public static function get_professional_id() {
        $user_id = get_current_user_id();

        if (!$user_id) {
            return 0;
        }

        global $wpdb;

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}beauty_professionals WHERE user_id = %d AND active = 1",
                $user_id
            )
        );
    }

    public static function is_professional() {
        return self::get_professional_id() > 0;
    }
}
