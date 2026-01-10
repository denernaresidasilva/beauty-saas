<?php
if (!defined('ABSPATH')) exit;

class Beauty_Permissions {

    /**
     * Garante que apenas empresa (dona do negócio) acesse
     */
    public static function company_only() {
        if (!is_user_logged_in()) {
            wp_die('Acesso negado.');
        }

        if (!self::is_company()) {
            wp_die('Permissão insuficiente.');
        }
    }

    /**
     * Verifica se o usuário atual é empresa
     */
    public static function is_company() {
        if (!is_user_logged_in()) {
            return false;
        }

        return Beauty_Company::get_company_id() > 0;
    }

    /**
     * Verifica se é profissional
     */
    public static function is_professional() {
        if (!is_user_logged_in()) {
            return false;
        }

        return Beauty_Company::get_professional_id() > 0;
    }

    /**
     * Verifica se é contador
     */
    public static function is_accountant() {
        if (!is_user_logged_in()) {
            return false;
        }

        $user = wp_get_current_user();

        return in_array('beauty_accountant', (array) $user->roles, true);
    }

    /**
     * Garante acesso a relatórios financeiros
     */
    public static function financial_read_only() {
        if (!is_user_logged_in()) {
            wp_die('Acesso negado.');
        }

        if (!self::is_company() && !self::is_accountant()) {
            wp_die('Permissão insuficiente.');
        }
    }

    /**
     * Garante que apenas profissional acesse
     */
    public static function professional_only() {
        if (!is_user_logged_in()) {
            wp_die('Acesso negado.');
        }

        if (!self::is_professional()) {
            wp_die('Permissão insuficiente.');
        }
    }
}
