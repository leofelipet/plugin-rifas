<?php
/**
 * Plugin Name: Rifas
 * Description: Sistema de rifas com seleção de números
 * Version: 1.0
 * Author: Seu Nome
 * Text Domain: rifas
 */

// Impedir acesso direto
if (!defined('ABSPATH')) {
    exit;
}

// Classe principal do plugin
class Rifas_Plugin {
    
    public function __construct() {
        // Ativação e desativação do plugin
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Adicionar scripts e estilos
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Adicionar menu de administração
        add_action('admin_menu', array($this, 'admin_menu'));
        
        // Adicionar shortcode
        add_shortcode('rifas_form', array($this, 'shortcode_rifas_form'));
        
        // Registrar AJAX handlers
        add_action('wp_ajax_get_numeros_rifas', array($this, 'ajax_get_numeros_rifas'));
        add_action('wp_ajax_nopriv_get_numeros_rifas', array($this, 'ajax_get_numeros_rifas'));
        add_action('wp_ajax_comprar_rifas', array($this, 'ajax_comprar_rifas'));
        add_action('wp_ajax_nopriv_comprar_rifas', array($this, 'ajax_comprar_rifas'));
        
        // Inicializar postmeta
        add_action('init', array($this, 'register_post_types'));
    }
    
    // Ativação do plugin
    public function activate() {
        // Criar tabelas personalizadas
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        $table_rifas = $wpdb->prefix . 'rifas_numeros';
        $table_compras = $wpdb->prefix . 'rifas_compras';
        
        $sql = "CREATE TABLE $table_rifas (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            numero int NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'disponivel',
            compra_id mediumint(9) DEFAULT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;
        
        CREATE TABLE $table_compras (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            nome varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            valor decimal(10,2) NOT NULL,
            numeros_selecionados text NOT NULL,
            data_compra datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Criar opções padrão
        $default_options = array(
            'quantidade_numeros' => 1000,
            'valor_por_numero' => 100,
            'titulo_rifa' => 'Rifa Beneficente',
            'descricao_rifa' => 'Ajude-nos a arrecadar fundos participando da nossa rifa.',
            'cor_principal' => '#4CAF50',
            'cor_secundaria' => '#2E7D32',
            'cor_texto' => '#FFFFFF',
            'cor_numeros_disponiveis' => '#FFFFFF',
            'cor_numeros_vendidos' => '#CCCCCC'
        );
        
        add_option('rifas_options', $default_options);
        
        // Preencher tabela de números
        $valores = array();
        $quantidade = $default_options['quantidade_numeros'];
        
        for ($i = 1; $i <= $quantidade; $i++) {
            $wpdb->insert(
                $table_rifas,
                array(
                    'numero' => $i,
                    'status' => 'disponivel'
                )
            );
        }
    }
    
    // Desativação do plugin
    public function deactivate() {
        // Não vamos remover as tabelas ou dados ao desativar
        // Se quiser limpar completamente, descomente o código abaixo
        /*
        global $wpdb;
        $table_rifas = $wpdb->prefix . 'rifas_numeros';
        $table_compras = $wpdb->prefix . 'rifas_compras';
        
        $wpdb->query("DROP TABLE IF EXISTS $table_rifas");
        $wpdb->query("DROP TABLE IF EXISTS $table_compras");
        
        delete_option('rifas_options');
        */
    }
    
    // Registrar post types
    public function register_post_types() {
        // Se precisar de post types específicos para rifas
    }
    
    // Adicionar scripts e estilos
    public function enqueue_scripts() {
        // Estilo
        wp_enqueue_style('rifas-style', plugins_url('assets/css/rifas.css', __FILE__), array(), '1.0.0');
        
        // Script
        wp_enqueue_script('rifas-script', plugins_url('assets/js/rifas.js', __FILE__), array('jquery'), '1.0.0', true);
        
        // Localize script para AJAX
        wp_localize_script('rifas-script', 'rifas_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rifas_nonce')
        ));
    }
    
    // Menu de administração
    public function admin_menu() {
        add_menu_page(
            'Rifas', 
            'Rifas', 
            'manage_options', 
            'rifas', 
            array($this, 'admin_page'),
            'dashicons-tickets-alt',
            30
        );
        
        add_submenu_page(
            'rifas',
            'Configurações',
            'Configurações',
            'manage_options',
            'rifas-configuracoes',
            array($this, 'admin_configuracoes_page')
        );
        
        add_submenu_page(
            'rifas',
            'Compras',
            'Compras',
            'manage_options',
            'rifas-compras',
            array($this, 'admin_compras_page')
        );
    }
    
    // Página principal do admin
    public function admin_page() {
        global $wpdb;
        $table_rifas = $wpdb->prefix . 'rifas_numeros';
        $table_compras = $wpdb->prefix . 'rifas_compras';
        
        // Contar números disponíveis e vendidos
        $total_numeros = $wpdb->get_var("SELECT COUNT(*) FROM $table_rifas");
        $numeros_vendidos = $wpdb->get_var("SELECT COUNT(*) FROM $table_rifas WHERE status = 'vendido'");
        $numeros_disponiveis = $total_numeros - $numeros_vendidos;
        
        // Calcular valor total arrecadado
        $valor_total = $wpdb->get_var("SELECT SUM(valor) FROM $table_compras");
        $valor_total = $valor_total ? $valor_total : 0;
        
        // Total de compradores
        $total_compradores = $wpdb->get_var("SELECT COUNT(DISTINCT email) FROM $table_compras");
        
        // Exibir dashboard
        include(plugin_dir_path(__FILE__) . 'templates/admin/dashboard.php');
    }
    
    // Página de configurações
    public function admin_configuracoes_page() {
        // Salvar configurações se o formulário foi enviado
        if (isset($_POST['rifas_save_settings']) && check_admin_referer('rifas_settings_nonce')) {
            $options = array(
                'quantidade_numeros' => intval($_POST['quantidade_numeros']),
                'valor_por_numero' => floatval($_POST['valor_por_numero']),
                'titulo_rifa' => sanitize_text_field($_POST['titulo_rifa']),
                'descricao_rifa' => wp_kses_post($_POST['descricao_rifa']),
                'cor_principal' => sanitize_hex_color($_POST['cor_principal']),
                'cor_secundaria' => sanitize_hex_color($_POST['cor_secundaria']),
                'cor_texto' => sanitize_hex_color($_POST['cor_texto']),
                'cor_numeros_disponiveis' => sanitize_hex_color($_POST['cor_numeros_disponiveis']),
                'cor_numeros_vendidos' => sanitize_hex_color($_POST['cor_numeros_vendidos'])
            );
            
            update_option('rifas_options', $options);
            
            // Atualizar quantidade de números se necessário
            $antiga_quantidade = get_option('rifas_quantidade_anterior', 0);
            $nova_quantidade = $options['quantidade_numeros'];
            
            if ($nova_quantidade != $antiga_quantidade) {
                global $wpdb;
                $table_rifas = $wpdb->prefix . 'rifas_numeros';
                
                if ($nova_quantidade > $antiga_quantidade) {
                    // Adicionar novos números
                    for ($i = $antiga_quantidade + 1; $i <= $nova_quantidade; $i++) {
                        $wpdb->insert(
                            $table_rifas,
                            array(
                                'numero' => $i,
                                'status' => 'disponivel'
                            )
                        );
                    }
                } else if ($nova_quantidade < $antiga_quantidade) {
                    // Verificar se os números a serem removidos estão disponíveis
                    $numeros_vendidos = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT COUNT(*) FROM $table_rifas WHERE numero > %d AND status = 'vendido'",
                            $nova_quantidade
                        )
                    );
                    
                    if ($numeros_vendidos > 0) {
                        // Não podemos remover números já vendidos
                        add_settings_error(
                            'rifas_options',
                            'numeros_vendidos',
                            'Não é possível reduzir a quantidade de números pois alguns já foram vendidos.',
                            'error'
                        );
                        
                        // Restaurar a quantidade anterior
                        $options['quantidade_numeros'] = $antiga_quantidade;
                        update_option('rifas_options', $options);
                    } else {
                        // Remover números não vendidos
                        $wpdb->query(
                            $wpdb->prepare(
                                "DELETE FROM $table_rifas WHERE numero > %d",
                                $nova_quantidade
                            )
                        );
                    }
                }
                
                update_option('rifas_quantidade_anterior', $nova_quantidade);
            }
            
            // Adicionar mensagem de sucesso
            add_settings_error(
                'rifas_options',
                'settings_updated',
                'Configurações salvas com sucesso.',
                'updated'
            );
        }
        
        // Buscar configurações atuais
        $options = get_option('rifas_options');
        
        // Exibir formulário de configurações
        include(plugin_dir_path(__FILE__) . 'templates/admin/configuracoes.php');
    }
    
    // Página de compras
    public function admin_compras_page() {
        global $wpdb;
        $table_compras = $wpdb->prefix . 'rifas_compras';
        
        // Paginação
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;
        
        // Total de compras
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_compras");
        $total_pages = ceil($total_items / $per_page);
        
        // Buscar compras
        $compras = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_compras ORDER BY data_compra DESC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );
        
        // Exibir lista de compras
        include(plugin_dir_path(__FILE__) . 'templates/admin/compras.php');
    }
    
    // Shortcode para o formulário de rifas
    public function shortcode_rifas_form() {
        ob_start();
        
        $options = get_option('rifas_options');
        
        // Exibir o formulário
        include(plugin_dir_path(__FILE__) . 'templates/frontend/formulario.php');
        
        return ob_get_clean();
    }
    
    // AJAX: Obter números de rifas
    public function ajax_get_numeros_rifas() {
        check_ajax_referer('rifas_nonce', 'nonce');
        
        $mostrar = isset($_POST['mostrar']) ? sanitize_text_field($_POST['mostrar']) : 'disponiveis';
        
        global $wpdb;
        $table_rifas = $wpdb->prefix . 'rifas_numeros';
        
        if ($mostrar == 'todos') {
            $numeros = $wpdb->get_results("SELECT id, numero, status FROM $table_rifas ORDER BY numero ASC");
        } else {
            $numeros = $wpdb->get_results("SELECT id, numero, status FROM $table_rifas WHERE status = 'disponivel' ORDER BY numero ASC");
        }
        
        wp_send_json_success(array(
            'numeros' => $numeros
        ));
        
        wp_die();
    }
    
    // AJAX: Comprar rifas
    public function ajax_comprar_rifas() {
        check_ajax_referer('rifas_nonce', 'nonce');
        
        $nome = isset($_POST['nome']) ? sanitize_text_field($_POST['nome']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $valor = isset($_POST['valor']) ? floatval($_POST['valor']) : 0;
        $numeros = isset($_POST['numeros']) ? array_map('intval', $_POST['numeros']) : array();
        
        // Validações
        if (empty($nome) || empty($email) || empty($numeros)) {
            wp_send_json_error(array(
                'message' => 'Por favor, preencha todos os campos obrigatórios.'
            ));
            wp_die();
        }
        
        if (!is_email($email)) {
            wp_send_json_error(array(
                'message' => 'Por favor, informe um email válido.'
            ));
            wp_die();
        }
        
        // Verificar valor mínimo
        $options = get_option('rifas_options');
        $valor_por_numero = $options['valor_por_numero'];
        $quantidade_numeros = count($numeros);
        $valor_minimo = $quantidade_numeros * $valor_por_numero;
        
        if ($valor < $valor_minimo) {
            wp_send_json_error(array(
                'message' => "O valor mínimo para {$quantidade_numeros} número(s) é R$ " . number_format($valor_minimo, 2, ',', '.')
            ));
            wp_die();
        }
        
        // Verificar se os números estão disponíveis
        global $wpdb;
        $table_rifas = $wpdb->prefix . 'rifas_numeros';
        $table_compras = $wpdb->prefix . 'rifas_compras';
        
        $numeros_indisponiveis = array();
        
        foreach ($numeros as $numero) {
            $status = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT status FROM $table_rifas WHERE numero = %d",
                    $numero
                )
            );
            
            if ($status != 'disponivel') {
                $numeros_indisponiveis[] = $numero;
            }
        }
        
        if (!empty($numeros_indisponiveis)) {
            wp_send_json_error(array(
                'message' => 'Os seguintes números já não estão disponíveis: ' . implode(', ', $numeros_indisponiveis),
                'numeros_indisponiveis' => $numeros_indisponiveis
            ));
            wp_die();
        }
        
        // Realizar a compra
        $wpdb->insert(
            $table_compras,
            array(
                'nome' => $nome,
                'email' => $email,
                'valor' => $valor,
                'numeros_selecionados' => implode(',', $numeros)
            )
        );
        
        $compra_id = $wpdb->insert_id;
        
        // Atualizar status dos números
        foreach ($numeros as $numero) {
            $wpdb->update(
                $table_rifas,
                array(
                    'status' => 'vendido',
                    'compra_id' => $compra_id
                ),
                array('numero' => $numero)
            );
        }
        
        // Enviar email de confirmação (opcional)
        $this->enviar_email_confirmacao($nome, $email, $numeros, $valor);
        
        wp_send_json_success(array(
            'message' => 'Compra realizada com sucesso!',
            'compra_id' => $compra_id
        ));
        
        wp_die();
    }
    
    // Enviar email de confirmação
    private function enviar_email_confirmacao($nome, $email, $numeros, $valor) {
        $options = get_option('rifas_options');
        $titulo_rifa = $options['titulo_rifa'];
        
        $assunto = "Confirmação de compra - {$titulo_rifa}";
        
        $mensagem = "Olá {$nome},\n\n";
        $mensagem .= "Sua compra para a rifa \"{$titulo_rifa}\" foi confirmada!\n\n";
        $mensagem .= "Detalhes da compra:\n";
        $mensagem .= "- Valor: R$ " . number_format($valor, 2, ',', '.') . "\n";
        $mensagem .= "- Números: " . implode(', ', $numeros) . "\n\n";
        $mensagem .= "Obrigado por participar!\n\n";
        $mensagem .= get_bloginfo('name');
        
        wp_mail($email, $assunto, $mensagem);
    }
}

// Inicializar o plugin
$rifas_plugin = new Rifas_Plugin();

// Incluir arquivos adicionais
require_once(plugin_dir_path(__FILE__) . 'includes/helpers.php');