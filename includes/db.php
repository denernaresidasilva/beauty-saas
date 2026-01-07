<?php
if (!defined('ABSPATH')) exit;

class Beauty_DB {

    public static function install() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();

        /**
         * EMPRESAS
         */
        dbDelta("CREATE TABLE {$wpdb->prefix}beauty_companies (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(150) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY user_id (user_id)
        ) $charset;");

        /**
         * CLIENTES
         */
        dbDelta("CREATE TABLE {$wpdb->prefix}beauty_clients (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            company_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(150) NOT NULL,
            phone VARCHAR(50),
            birthday DATE NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY company_id (company_id)
        ) $charset;");

        /**
         * SERVIÇOS
         */
        dbDelta("CREATE TABLE {$wpdb->prefix}beauty_services (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            company_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(150) NOT NULL,
            duration INT DEFAULT 60,
            price DECIMAL(10,2) DEFAULT 0,
            followup_enabled TINYINT(1) DEFAULT 0,
            followup_delay_value INT DEFAULT NULL,
            followup_delay_unit ENUM('day','week','month','year') DEFAULT NULL,
            active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY company_id (company_id)
        ) $charset;");

        /**
         * PROFISSIONAIS
         */
        dbDelta("CREATE TABLE {$wpdb->prefix}beauty_professionals (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            company_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NULL,
            name VARCHAR(150) NOT NULL,
            phone VARCHAR(50),
            commission DECIMAL(5,2) DEFAULT 0,
            active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY company_id (company_id)
        ) $charset;");

        /**
         * PROFISSIONAL x SERVIÇOS
         */
        dbDelta("CREATE TABLE {$wpdb->prefix}beauty_professional_services (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            professional_id BIGINT UNSIGNED NOT NULL,
            service_id BIGINT UNSIGNED NOT NULL,
            UNIQUE KEY prof_service (professional_id, service_id)
        ) $charset;");

        /**
         * PRODUTOS
         */
        dbDelta("CREATE TABLE {$wpdb->prefix}beauty_products (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            company_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(150) NOT NULL,
            price DECIMAL(10,2) DEFAULT 0,
            stock INT DEFAULT 0,
            active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY company_id (company_id)
        ) $charset;");

        /**
         * AGENDAMENTOS
         */
        dbDelta("CREATE TABLE {$wpdb->prefix}beauty_appointments (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            company_id BIGINT UNSIGNED NOT NULL,
            client_id BIGINT UNSIGNED NOT NULL,
            professional_id BIGINT UNSIGNED NOT NULL,
            service_id BIGINT UNSIGNED NOT NULL,
            start_time DATETIME NOT NULL,
            end_time DATETIME NOT NULL,
            status VARCHAR(30) DEFAULT 'confirmado',
            reminder_sent TINYINT(1) DEFAULT 0,
            followup_sent TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY company_id (company_id),
            KEY professional_id (professional_id),
            KEY start_time (start_time)
        ) $charset;");

        /**
         * FINANCEIRO
         */
        dbDelta("CREATE TABLE {$wpdb->prefix}beauty_financial (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            company_id BIGINT UNSIGNED NOT NULL,
            appointment_id BIGINT UNSIGNED NOT NULL,
            professional_id BIGINT UNSIGNED NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            payment_method VARCHAR(50),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY company_id (company_id)
        ) $charset;");

        /**
         * MENSAGENS
         */
        dbDelta("CREATE TABLE {$wpdb->prefix}beauty_messages (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            company_id BIGINT UNSIGNED NOT NULL,
            slug VARCHAR(100) NOT NULL,
            title VARCHAR(150),
            content TEXT,
            active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY company_slug (company_id, slug)
        ) $charset;");

        /**
         * AUTOMAÇÕES
         */
        dbDelta("CREATE TABLE {$wpdb->prefix}beauty_automations (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            company_id BIGINT UNSIGNED NOT NULL,
            event VARCHAR(100) NOT NULL,
            message_id BIGINT UNSIGNED NOT NULL,
            delay_days INT DEFAULT 0,
            active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY company_id (company_id),
            KEY event (event)
        ) $charset;");

        /**
         * SESSÕES (LOGIN TOKEN)
         */
        dbDelta("CREATE TABLE {$wpdb->prefix}beauty_sessions (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            token VARCHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY token (token),
            KEY user_id (user_id)
        ) $charset;");

        /**
         * LOGS (AUDITORIA)
         */
        dbDelta("CREATE TABLE {$wpdb->prefix}beauty_logs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED,
            action VARCHAR(100) NOT NULL,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY user_id (user_id),
            KEY action (action)
        ) $charset;");
    }
}
