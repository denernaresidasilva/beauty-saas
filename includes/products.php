<?php
if (!defined('ABSPATH')) exit;

class Beauty_Products {

    public function __construct() {
        add_action('wp_ajax_beauty_get_products', [$this, 'get_products']);
        add_action('wp_ajax_beauty_save_product', [$this, 'save_product']);
        add_action('wp_ajax_beauty_delete_product', [$this, 'delete_product']);
    }

    /**
     * Lista produtos da empresa
     */
    public function get_products() {
        if (!check_ajax_referer('beauty_nonce', 'nonce', false)) {
            wp_send_json_error('Nonce inválido.');
        }

        Beauty_Permissions::company_only(); // ✅ Proteção aplicada

        global $wpdb;
        $company_id = Beauty_Company::get_company_id();

        $products = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}beauty_products
                 WHERE company_id = %d
                 ORDER BY name ASC",
                $company_id
            )
        );

        wp_send_json_success($products);
    }

    /**
     * Cria ou atualiza produto
     */
    public function save_product() {
        if (!check_ajax_referer('beauty_nonce', 'nonce', false)) {
            wp_send_json_error('Nonce inválido.');
        }

        Beauty_Permissions::company_only(); // ✅ Proteção aplicada

        global $wpdb;
        $company_id = Beauty_Company::get_company_id();

        $id     = intval($_POST['id'] ?? 0);
        $name   = sanitize_text_field($_POST['name']);
        $price  = floatval($_POST['price']);
        $stock  = intval($_POST['stock']);
        $active = isset($_POST['active']) ? 1 : 0;

        if (!$name) {
            wp_send_json_error('Nome obrigatório');
        }

        $data = [
            'company_id' => $company_id,
            'name'       => $name,
            'price'      => $price,
            'stock'      => $stock,
            'active'     => $active
        ];

        if ($id > 0) {
            $wpdb->update(
                "{$wpdb->prefix}beauty_products",
                $data,
                ['id' => $id, 'company_id' => $company_id]
            );
        } else {
            $wpdb->insert(
                "{$wpdb->prefix}beauty_products",
                $data
            );
            $id = $wpdb->insert_id;
        }

        wp_send_json_success(['id' => $id]);
    }

    /**
     * Exclui produto
     */
    public function delete_product() {
        if (!check_ajax_referer('beauty_nonce', 'nonce', false)) {
            wp_send_json_error('Nonce inválido.');
        }

        Beauty_Permissions::company_only(); // ✅ Proteção aplicada

        global $wpdb;
        $company_id = Beauty_Company::get_company_id();
        $id = intval($_POST['id']);

        if ($id <= 0) {
            wp_send_json_error('ID inválido');
        }

        $wpdb->delete(
            "{$wpdb->prefix}beauty_products",
            [
                'id' => $id,
                'company_id' => $company_id
            ]
        );

        wp_send_json_success();
    }
}

new Beauty_Products();
