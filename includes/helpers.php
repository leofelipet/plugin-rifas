<?php
/**
 * Funções auxiliares para o plugin Rifas
 * 
 * @package Rifas
 */

// Impedir acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Injetar estilos personalizados com base nas configurações
 */
function rifas_injetar_estilos_personalizados() {
    $options = get_option('rifas_options');
    
    if (!$options) return;
    
    $cor_principal = sanitize_hex_color($options['cor_principal']);
    $cor_secundaria = sanitize_hex_color($options['cor_secundaria']);
    $cor_texto = sanitize_hex_color($options['cor_texto']);
    $cor_numeros_disponiveis = sanitize_hex_color($options['cor_numeros_disponiveis']);
    $cor_numeros_vendidos = sanitize_hex_color($options['cor_numeros_vendidos']);
    
    $css = "
        :root {
            --rifas-cor-principal: {$cor_principal};
            --rifas-cor-secundaria: {$cor_secundaria};
            --rifas-cor-texto: {$cor_texto};
            --rifas-cor-numeros-disponiveis: {$cor_numeros_disponiveis};
            --rifas-cor-numeros-vendidos: {$cor_numeros_vendidos};
        }
    ";
    
    echo '<style>' . $css . '</style>';
}
add_action('wp_head', 'rifas_injetar_estilos_personalizados');
add_action('admin_head', 'rifas_injetar_estilos_personalizados');

/**
 * Calcular quantos números o usuário tem direito com base no valor
 * 
 * @param float $valor Valor da compra
 * @return int Quantidade de números
 */
function rifas_calcular_numeros_permitidos($valor) {
    $options = get_option('rifas_options');
    $valor_por_numero = floatval($options['valor_por_numero']);
    
    if ($valor_por_numero <= 0) {
        return 0;
    }
    
    return floor($valor / $valor_por_numero);
}

/**
 * Verificar se um número de rifa está disponível
 * 
 * @param int $numero Número da rifa
 * @return bool True se disponível, false se não
 */
function rifas_verificar_disponibilidade($numero) {
    global $wpdb;
    $table_rifas = $wpdb->prefix . 'rifas_numeros';
    
    $status = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT status FROM $table_rifas WHERE numero = %d",
            $numero
        )
    );
    
    return $status === 'disponivel';
}

/**
 * Obter detalhes de uma compra
 * 
 * @param int $compra_id ID da compra
 * @return object|null Objeto com dados da compra ou null se não encontrada
 */
function rifas_obter_detalhes_compra($compra_id) {
    global $wpdb;
    $table_compras = $wpdb->prefix . 'rifas_compras';
    
    return $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_compras WHERE id = %d",
            $compra_id
        )
    );
}

/**
 * Formatar valor em reais
 * 
 * @param float $valor Valor a ser formatado
 * @return string Valor formatado
 */
function rifas_formatar_valor($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/**
 * Verificar se todas as dependências do plugin estão ativas
 * 
 * @return bool True se todas as dependências estão ativas
 */
function rifas_verificar_dependencias() {
    // Por enquanto, não há dependências obrigatórias
    // Se quiser integrar com WooCommerce ou outro plugin, adicione aqui
    return true;
}

/**
 * Registrar scripts e estilos para o admin
 */
function rifas_admin_enqueue_scripts() {
    $screen = get_current_screen();
    
    if (strpos($screen->id, 'rifas') !== false) {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        wp_enqueue_style('rifas-admin-style', plugins_url('assets/css/rifas-admin.css', dirname(__FILE__)));
        wp_enqueue_script('rifas-admin-script', plugins_url('assets/js/rifas-admin.js', dirname(__FILE__)), array('jquery', 'wp-color-picker'), '1.0.0', true);
    }
}
add_action('admin_enqueue_scripts', 'rifas_admin_enqueue_scripts');

/**
 * Exportar lista de compras para CSV
 * 
 * @return void
 */
function rifas_exportar_compras_csv() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    if (isset($_GET['page']) && $_GET['page'] === 'rifas-compras' && isset($_GET['action']) && $_GET['action'] === 'export') {
        global $wpdb;
        $table_compras = $wpdb->prefix . 'rifas_compras';
        
        $compras = $wpdb->get_results("SELECT * FROM $table_compras ORDER BY data_compra DESC");
        
        // Configurar cabeçalhos para download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=rifas-compras-' . date('Y-m-d') . '.csv');
        
        // Criar o recurso de saída
        $output = fopen('php://output', 'w');
        
        // Adicionar BOM para UTF-8
        fputs($output, "\xEF\xBB\xBF");
        
        // Cabeçalhos do CSV
        fputcsv($output, array(
            'ID',
            'Nome',
            'Email',
            'Valor',
            'Números',
            'Data da Compra'
        ));
        
        // Dados
        foreach ($compras as $compra) {
            fputcsv($output, array(
                $compra->id,
                $compra->nome,
                $compra->email,
                $compra->valor,
                $compra->numeros_selecionados,
                $compra->data_compra
            ));
        }
        
        fclose($output);
        exit;
    }
}
add_action('admin_init', 'rifas_exportar_compras_csv');

/**
 * Adicionar link de exportação na página de compras
 * 
 * @param string $page Página atual
 * @return void
 */
function rifas_adicionar_botao_exportar($page) {
    if ($page === 'rifas-compras') {
        $url = add_query_arg(array(
            'action' => 'export',
            '_wpnonce' => wp_create_nonce('rifas_export_nonce')
        ));
        
        echo '<a href="' . esc_url($url) . '" class="page-title-action">Exportar CSV</a>';
    }
}
add_action('admin_notices', 'rifas_adicionar_botao_exportar');

/**
 * Verificar se um número está dentro do intervalo válido
 * 
 * @param int $numero Número para verificar
 * @return bool True se está dentro do intervalo válido
 */
function rifas_numero_valido($numero) {
    $options = get_option('rifas_options');
    $quantidade_numeros = intval($options['quantidade_numeros']);
    
    return ($numero >= 1 && $numero <= $quantidade_numeros);
}

/**
 * Obter lista de números vendidos
 * 
 * @return array Lista de números vendidos
 */
function rifas_obter_numeros_vendidos() {
    global $wpdb;
    $table_rifas = $wpdb->prefix . 'rifas_numeros';
    
    $numeros = $wpdb->get_col("SELECT numero FROM $table_rifas WHERE status = 'vendido' ORDER BY numero ASC");
    
    return $numeros;
}

/**
 * Obter lista de números disponíveis
 * 
 * @return array Lista de números disponíveis
 */
function rifas_obter_numeros_disponiveis() {
    global $wpdb;
    $table_rifas = $wpdb->prefix . 'rifas_numeros';
    
    $numeros = $wpdb->get_col("SELECT numero FROM $table_rifas WHERE status = 'disponivel' ORDER BY numero ASC");
    
    return $numeros;
}

/**
 * Resetar as configurações para os valores padrão
 * 
 * @return void
 */
function rifas_resetar_configuracoes() {
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
    
    update_option('rifas_options', $default_options);
}

/**
 * Sanitizar as configurações antes de salvar
 * 
 * @param array $input Configurações enviadas pelo formulário
 * @return array Configurações sanitizadas
 */
function rifas_sanitizar_opcoes($input) {
    $output = array();
    
    $output['quantidade_numeros'] = absint($input['quantidade_numeros']);
    $output['valor_por_numero'] = floatval($input['valor_por_numero']);
    $output['titulo_rifa'] = sanitize_text_field($input['titulo_rifa']);
    $output['descricao_rifa'] = wp_kses_post($input['descricao_rifa']);
    $output['cor_principal'] = sanitize_hex_color($input['cor_principal']);
    $output['cor_secundaria'] = sanitize_hex_color($input['cor_secundaria']);
    $output['cor_texto'] = sanitize_hex_color($input['cor_texto']);
    $output['cor_numeros_disponiveis'] = sanitize_hex_color($input['cor_numeros_disponiveis']);
    $output['cor_numeros_vendidos'] = sanitize_hex_color($input['cor_numeros_vendidos']);
    
    return $output;
}

/**
 * Obter o status atual da rifa
 * 
 * @return array Informações sobre o status da rifa
 */
function rifas_obter_status() {
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
    
    return array(
        'total_numeros' => $total_numeros,
        'numeros_vendidos' => $numeros_vendidos,
        'numeros_disponiveis' => $numeros_disponiveis,
        'valor_total' => $valor_total,
        'total_compradores' => $total_compradores,
        'percentual_vendido' => ($total_numeros > 0) ? round(($numeros_vendidos / $total_numeros) * 100, 2) : 0
    );
}

/**
 * Adicionar meta box para compras recentes no dashboard do WordPress
 */
function rifas_adicionar_metabox_dashboard() {
    if (current_user_can('manage_options')) {
        wp_add_dashboard_widget(
            'rifas_dashboard_widget',
            'Rifas - Compras Recentes',
            'rifas_metabox_dashboard_conteudo'
        );
    }
}
add_action('wp_dashboard_setup', 'rifas_adicionar_metabox_dashboard');

/**
 * Conteúdo do meta box no dashboard
 */
function rifas_metabox_dashboard_conteudo() {
    global $wpdb;
    $table_compras = $wpdb->prefix . 'rifas_compras';
    
    $compras = $wpdb->get_results(
        "SELECT * FROM $table_compras ORDER BY data_compra DESC LIMIT 5"
    );
    
    $status = rifas_obter_status();
    
    echo '<div class="rifas-dashboard-widget">';
    
    // Resumo rápido
    echo '<div class="rifas-dashboard-resumo">';
    echo '<p><strong>Vendidos:</strong> ' . $status['numeros_vendidos'] . ' de ' . $status['total_numeros'] . ' (' . $status['percentual_vendido'] . '%)</p>';
    echo '<p><strong>Arrecadado:</strong> ' . rifas_formatar_valor($status['valor_total']) . '</p>';
    echo '</div>';
    
    // Compras recentes
    if (empty($compras)) {
        echo '<p>Nenhuma compra registrada ainda.</p>';
    } else {
        echo '<table class="wp-list-table widefat fixed striped" style="margin-top: 10px;">';
        echo '<thead><tr><th>Nome</th><th>Valor</th><th>Data</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($compras as $compra) {
            echo '<tr>';
            echo '<td>' . esc_html($compra->nome) . '</td>';
            echo '<td>' . rifas_formatar_valor($compra->valor) . '</td>';
            echo '<td>' . date_i18n('d/m/Y H:i', strtotime($compra->data_compra)) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
    
    echo '<p class="rifas-dashboard-link"><a href="' . admin_url('admin.php?page=rifas') . '">Ver todos os detalhes</a></p>';
    echo '</div>';
}

/**
 * Registrar um log de atividade
 * 
 * @param string $acao Ação realizada
 * @param array $dados Dados relacionados à ação
 * @return void
 */
function rifas_registrar_log($acao, $dados = array()) {
    $log = get_option('rifas_logs', array());
    
    $novo_item = array(
        'timestamp' => current_time('timestamp'),
        'acao' => $acao,
        'dados' => $dados,
        'ip' => $_SERVER['REMOTE_ADDR']
    );
    
    // Adicionar novo item no início do array
    array_unshift($log, $novo_item);
    
    // Limitar a 100 registros
    if (count($log) > 100) {
        $log = array_slice($log, 0, 100);
    }
    
    update_option('rifas_logs', $log);
}

/**
 * Obter logs de atividade
 * 
 * @param int $limite Quantidade de logs a retornar
 * @return array Logs de atividade
 */
function rifas_obter_logs($limite = 50) {
    $logs = get_option('rifas_logs', array());
    
    if ($limite > 0) {
        return array_slice($logs, 0, $limite);
    }
    
    return $logs;
}

/**
 * Renderizar uma mensagem de erro ou sucesso
 * 
 * @param string $mensagem Mensagem a ser exibida
 * @param string $tipo Tipo da mensagem (error, success, warning, info)
 * @return void
 */
function rifas_exibir_mensagem($mensagem, $tipo = 'info') {
    echo '<div class="notice notice-' . esc_attr($tipo) . ' is-dismissible">';
    echo '<p>' . esc_html($mensagem) . '</p>';
    echo '</div>';
}

/**
 * Verificar se o usuário pode gerenciar rifas
 * 
 * @return bool True se o usuário tem permissão
 */
function rifas_usuario_pode_gerenciar() {
    return current_user_can('manage_options');
}

/**
 * Obter URL da página de administração do plugin
 * 
 * @param string $tab Tab específico (opcional)
 * @return string URL da página
 */
function rifas_obter_admin_url($tab = '') {
    $url = admin_url('admin.php?page=rifas');
    
    if (!empty($tab)) {
        $url = add_query_arg('tab', $tab, $url);
    }
    
    return $url;
}

/**
 * Enviar email personalizado de confirmação
 * 
 * @param string $para Email do destinatário
 * @param string $assunto Assunto do email
 * @param string $mensagem Conteúdo do email
 * @return bool True se o email foi enviado com sucesso
 */
function rifas_enviar_email($para, $assunto, $mensagem) {
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    // Usar template padrão do WordPress se disponível
    $mensagem = wpautop($mensagem); // Converter quebras de linha em parágrafos
    
    return wp_mail($para, $assunto, $mensagem, $headers);
}

/**
 * Prepara as tabelas do banco de dados para o plugin
 */
function rifas_criar_tabelas() {
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
}

/**
 * Obter estatísticas detalhadas das vendas
 * 
 * @return array Estatísticas detalhadas
 */
function rifas_obter_estatisticas_detalhadas() {
    global $wpdb;
    $table_compras = $wpdb->prefix . 'rifas_compras';
    
    // Total por dia (últimos 30 dias)
    $vendas_por_dia = $wpdb->get_results(
        "SELECT 
            DATE(data_compra) as data, 
            COUNT(*) as total_compras, 
            SUM(valor) as valor_total 
        FROM 
            $table_compras 
        WHERE 
            data_compra >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
        GROUP BY 
            DATE(data_compra) 
        ORDER BY 
            data_compra DESC"
    );
    
    // Valor médio das compras
    $valor_medio = $wpdb->get_var("SELECT AVG(valor) FROM $table_compras");
    
    // Top 5 compradores (por valor)
    $top_compradores = $wpdb->get_results(
        "SELECT 
            nome, 
            email, 
            SUM(valor) as total_gasto, 
            COUNT(*) as total_compras 
        FROM 
            $table_compras 
        GROUP BY 
            email 
        ORDER BY 
            total_gasto DESC 
        LIMIT 5"
    );
    
    return array(
        'vendas_por_dia' => $vendas_por_dia,
        'valor_medio' => $valor_medio ? $valor_medio : 0,
        'top_compradores' => $top_compradores
    );
}