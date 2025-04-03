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
        // Incluir o arquivo de helpers se necessário
        require_once(plugin_dir_path(__FILE__) . 'includes/helpers.php');
        
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
            PRIMARY KEY  (id),
            KEY numero (numero),
            KEY status (status)
        ) $charset_collate;
        
        CREATE TABLE $table_compras (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            nome varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            valor decimal(10,2) NOT NULL,
            numeros_selecionados text NOT NULL,
            data_compra datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY email (email),
            KEY data_compra (data_compra)
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
        add_option('rifas_quantidade_anterior', $default_options['quantidade_numeros']);
        
        // Preencher tabela de números
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
        
        // Registrar log de ativação do plugin
        if (function_exists('rifas_registrar_log')) {
            rifas_registrar_log('plugin_ativado', array(
                'versao' => '1.0',
                'quantidade_numeros' => $quantidade
            ));
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
        
        // Registrar log de desativação
        if (function_exists('rifas_registrar_log')) {
            rifas_registrar_log('plugin_desativado', array(
                'data' => current_time('mysql')
            ));
        }
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
                    
                    // Registrar log
                    if (function_exists('rifas_registrar_log')) {
                        rifas_registrar_log('numeros_adicionados', array(
                            'de' => $antiga_quantidade,
                            'para' => $nova_quantidade,
                            'total_adicionado' => $nova_quantidade - $antiga_quantidade
                        ));
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
                        // Verificar se os números estão em alguma compra pendente
                        $compras_pendentes = $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT COUNT(*) FROM $table_rifas r 
                                INNER JOIN {$wpdb->prefix}rifas_compras c ON r.compra_id = c.id 
                                WHERE r.numero > %d",
                                $nova_quantidade
                            )
                        );
                        
                        if ($compras_pendentes > 0) {
                            // Não podemos remover números em compras pendentes
                            add_settings_error(
                                'rifas_options',
                                'compras_pendentes',
                                'Não é possível reduzir a quantidade de números pois alguns estão em compras pendentes.',
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
                            
                            // Registrar log
                            if (function_exists('rifas_registrar_log')) {
                                rifas_registrar_log('numeros_removidos', array(
                                    'de' => $antiga_quantidade,
                                    'para' => $nova_quantidade,
                                    'total_removido' => $antiga_quantidade - $nova_quantidade
                                ));
                            }
                        }
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
        
        // Parâmetros de paginação e filtro
        $mostrar = isset($_POST['mostrar']) ? sanitize_text_field($_POST['mostrar']) : 'disponiveis';
        $pagina = isset($_POST['pagina']) ? intval($_POST['pagina']) : 1;
        $por_pagina = isset($_POST['por_pagina']) ? intval($_POST['por_pagina']) : 100;
        
        // Limitar máximo de itens por página para evitar sobrecarga
        $por_pagina = min($por_pagina, 500);
        
        // Calcular offset para paginação
        $offset = ($pagina - 1) * $por_pagina;
        
        global $wpdb;
        $table_rifas = $wpdb->prefix . 'rifas_numeros';
        
        // Obter o total de registros para calcular o número total de páginas
        if ($mostrar == 'todos') {
            $total_registros = $wpdb->get_var("SELECT COUNT(*) FROM $table_rifas");
        } elseif ($mostrar == 'vendidos') {
            $total_registros = $wpdb->get_var("SELECT COUNT(*) FROM $table_rifas WHERE status = 'vendido'");
        } else {
            // Padrão: mostrar apenas disponíveis
            $total_registros = $wpdb->get_var("SELECT COUNT(*) FROM $table_rifas WHERE status = 'disponivel'");
        }
        
        // Calcular total de páginas
        $total_paginas = ceil($total_registros / $por_pagina);
        
        // Consulta para obter os números da página atual
        if ($mostrar == 'todos') {
            $numeros = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id, numero, status FROM $table_rifas ORDER BY numero ASC LIMIT %d OFFSET %d",
                    $por_pagina,
                    $offset
                )
            );
        } elseif ($mostrar == 'vendidos') {
            $numeros = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id, numero, status FROM $table_rifas WHERE status = 'vendido' ORDER BY numero ASC LIMIT %d OFFSET %d",
                    $por_pagina,
                    $offset
                )
            );
        } else {
            // Padrão: mostrar apenas disponíveis
            $numeros = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id, numero, status FROM $table_rifas WHERE status = 'disponivel' ORDER BY numero ASC LIMIT %d OFFSET %d",
                    $por_pagina,
                    $offset
                )
            );
        }
        
        // Performance: cache da consulta se estiver em produção
        if (!WP_DEBUG) {
            $cache_key = 'rifas_numeros_' . md5($mostrar . '_' . $pagina . '_' . $por_pagina);
            $cache_time = 5 * MINUTE_IN_SECONDS; // 5 minutos
            wp_cache_set($cache_key, $numeros, 'rifas', $cache_time);
        }
        
        // Registrar log se necessário (apenas para admin)
        if (current_user_can('manage_options') && function_exists('rifas_registrar_log')) {
            rifas_registrar_log('consulta_numeros', array(
                'filtro' => $mostrar,
                'pagina' => $pagina,
                'total_registros' => $total_registros
            ));
        }
        
        // Enviar resposta com informações de paginação
        wp_send_json_success(array(
            'numeros' => $numeros,
            'pagina_atual' => $pagina,
            'total_paginas' => $total_paginas,
            'total_registros' => $total_registros,
            'por_pagina' => $por_pagina
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
        
        // Verificar máximo de números permitidos pelo valor informado
        $max_numeros_permitidos = floor($valor / $valor_por_numero);
        
        if ($quantidade_numeros > $max_numeros_permitidos) {
            wp_send_json_error(array(
                'message' => "Com o valor informado de R$ " . number_format($valor, 2, ',', '.') . " você pode comprar no máximo {$max_numeros_permitidos} número(s)."
            ));
            wp_die();
        }
        
        // Verificar se os números estão dentro do intervalo válido
        $quantidade_total = $options['quantidade_numeros'];
        foreach ($numeros as $numero) {
            if ($numero < 1 || $numero > $quantidade_total) {
                wp_send_json_error(array(
                    'message' => "O número {$numero} está fora do intervalo válido (1 a {$quantidade_total})."
                ));
                wp_die();
            }
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
        
        // Realizar a compra - adicionar bloqueio para evitar race conditions
        $wpdb->query('START TRANSACTION');
        
        try {
            // Verificar novamente a disponibilidade antes de efetuar a compra
            $numeros_indisponiveis_recheck = array();
            
            foreach ($numeros as $numero) {
                $status = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT status FROM $table_rifas WHERE numero = %d FOR UPDATE",
                        $numero
                    )
                );
                
                if ($status != 'disponivel') {
                    $numeros_indisponiveis_recheck[] = $numero;
                }
            }
            
            if (!empty($numeros_indisponiveis_recheck)) {
                $wpdb->query('ROLLBACK');
                wp_send_json_error(array(
                    'message' => 'Os seguintes números foram reservados por outro usuário: ' . implode(', ', $numeros_indisponiveis_recheck),
                    'numeros_indisponiveis' => $numeros_indisponiveis_recheck
                ));
                wp_die();
            }
            
            // Inserir compra
            $wpdb->insert(
                $table_compras,
                array(
                    'nome' => $nome,
                    'email' => $email,
                    'valor' => $valor,
                    'numeros_selecionados' => implode(',', $numeros),
                    'data_compra' => current_time('mysql')
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
            
            $wpdb->query('COMMIT');
            
            // Registrar log da compra
            if (function_exists('rifas_registrar_log')) {
                rifas_registrar_log('compra_realizada', array(
                    'compra_id' => $compra_id,
                    'nome' => $nome,
                    'numeros' => $numeros,
                    'valor' => $valor
                ));
            }
            
            // Enviar email de confirmação
            $this->enviar_email_confirmacao($nome, $email, $numeros, $valor);
            
            wp_send_json_success(array(
                'message' => 'Compra realizada com sucesso!',
                'compra_id' => $compra_id
            ));
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            wp_send_json_error(array(
                'message' => 'Erro ao processar a compra: ' . $e->getMessage()
            ));
        }
        
        wp_die();
    }
    
    // Enviar email de confirmação
    private function enviar_email_confirmacao($nome, $email, $numeros, $valor) {
        // Verificar se a função de helpers está disponível
        if (function_exists('rifas_enviar_email')) {
            $options = get_option('rifas_options');
            $titulo_rifa = $options['titulo_rifa'];
            
            $assunto = sprintf(__('Confirmação de compra - %s', 'rifas'), $titulo_rifa);
            
            // Criar conteúdo HTML
            $mensagem = '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>' . esc_html($assunto) . '</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: ' . esc_attr($options['cor_principal']) . '; color: ' . esc_attr($options['cor_texto']) . '; padding: 15px; text-align: center; }
                    .content { padding: 20px; background-color: #f9f9f9; }
                    .details { margin: 20px 0; padding: 15px; background-color: #fff; border-left: 4px solid ' . esc_attr($options['cor_principal']) . '; }
                    .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>' . esc_html($titulo_rifa) . '</h1>
                    </div>
                    <div class="content">
                        <p>' . sprintf(__('Olá %s,', 'rifas'), esc_html($nome)) . '</p>
                        <p>' . sprintf(__('Sua compra para a rifa "%s" foi confirmada!', 'rifas'), esc_html($titulo_rifa)) . '</p>
                        
                        <div class="details">
                            <h3>' . __('Detalhes da compra:', 'rifas') . '</h3>
                            <p><strong>' . __('Valor:', 'rifas') . '</strong> ' . esc_html(rifas_formatar_valor($valor)) . '</p>
                            <p><strong>' . __('Números:', 'rifas') . '</strong> ' . esc_html(implode(', ', $numeros)) . '</p>
                        </div>
                        
                        <p>' . __('Obrigado por participar!', 'rifas') . '</p>
                    </div>
                    <div class="footer">
                        <p>' . esc_html(get_bloginfo('name')) . '</p>
                    </div>
                </div>
            </body>
            </html>';
            
            // Enviar email usando a função helper
            return rifas_enviar_email($email, $assunto, $mensagem);
        } else {
            // Fallback para o método anterior
            $options = get_option('rifas_options');
            $titulo_rifa = $options['titulo_rifa'];
            
            $assunto = sprintf(__('Confirmação de compra - %s', 'rifas'), $titulo_rifa);
            
            $mensagem = sprintf(__('Olá %s,', 'rifas'), $nome) . "\n\n";
            $mensagem .= sprintf(__('Sua compra para a rifa "%s" foi confirmada!', 'rifas'), $titulo_rifa) . "\n\n";
            $mensagem .= __('Detalhes da compra:', 'rifas') . "\n";
            $mensagem .= '- ' . __('Valor:', 'rifas') . ' ' . rifas_formatar_valor($valor) . "\n";
            $mensagem .= '- ' . __('Números:', 'rifas') . ' ' . implode(', ', $numeros) . "\n\n";
            $mensagem .= __('Obrigado por participar!', 'rifas') . "\n\n";
            $mensagem .= get_bloginfo('name');
            
            $headers = array('Content-Type: text/html; charset=UTF-8');
            
            return wp_mail($email, $assunto, nl2br($mensagem), $headers);
        }
    }
}

// Inicializar o plugin
$rifas_plugin = new Rifas_Plugin();

// Incluir arquivos adicionais
require_once(plugin_dir_path(__FILE__) . 'includes/helpers.php');