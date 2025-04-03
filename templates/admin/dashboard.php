<?php
/**
 * Template para o Dashboard Administrativo do Plugin Rifas
 *
 * @package Rifas
 */

// Impedir acesso direto
if (!defined('ABSPATH')) {
    exit;
}

// Obter estatísticas
$status = rifas_obter_status();
$estatisticas = rifas_obter_estatisticas_detalhadas();
$logs = rifas_obter_logs(10);

// Calcular valores 
$total_numeros = $status['total_numeros'];
$numeros_vendidos = $status['numeros_vendidos'];
$numeros_disponiveis = $status['numeros_disponiveis'];
$valor_total = $status['valor_total'];
$total_compradores = $status['total_compradores'];
$percentual_vendido = $status['percentual_vendido'];
$options = get_option('rifas_options');
$valor_por_numero = floatval($options['valor_por_numero']);
?>

<div class="wrap">
    <h1>
        <span class="dashicons dashicons-tickets-alt" style="font-size: 30px; height: 30px; width: 30px; padding-right: 10px;"></span>
        Dashboard - Rifas
    </h1>
    
    <div class="rifas-dashboard-header">
        <div class="rifas-progresso">
            <div class="rifas-barra-progresso">
                <div class="rifas-barra-progresso-preenchimento" style="width: <?php echo esc_attr($percentual_vendido); ?>%;">
                    <span class="rifas-barra-progresso-texto"><?php echo esc_html($percentual_vendido); ?>%</span>
                </div>
            </div>
            <div class="rifas-barra-legenda">
                <span><?php echo sprintf(esc_html__('Vendidos: %d de %d números', 'rifas'), $numeros_vendidos, $total_numeros); ?></span>
            </div>
        </div>
        
        <div class="rifas-acoes-rapidas">
            <a href="<?php echo admin_url('admin.php?page=rifas-compras&action=export&_wpnonce=' . wp_create_nonce('rifas_export_nonce')); ?>" class="button">
                <span class="dashicons dashicons-download"></span>
                <?php esc_html_e('Exportar Compras', 'rifas'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=rifas-configuracoes'); ?>" class="button">
                <span class="dashicons dashicons-admin-settings"></span>
                <?php esc_html_e('Configurações', 'rifas'); ?>
            </a>
        </div>
    </div>
    
    <div class="rifas-dashboard">
        <!-- Resumo -->
        <div class="rifas-card">
            <div class="rifas-card-header">
                <h2>
                    <span class="dashicons dashicons-chart-pie"></span>
                    <?php esc_html_e('Resumo', 'rifas'); ?>
                </h2>
            </div>
            <div class="rifas-card-body">
                <div class="rifas-stat">
                    <span class="rifas-stat-value"><?php echo esc_html($total_numeros); ?></span>
                    <span class="rifas-stat-label"><?php esc_html_e('Total de Números', 'rifas'); ?></span>
                </div>
                <div class="rifas-stat">
                    <span class="rifas-stat-value"><?php echo esc_html($numeros_vendidos); ?></span>
                    <span class="rifas-stat-label"><?php esc_html_e('Números Vendidos', 'rifas'); ?></span>
                </div>
                <div class="rifas-stat">
                    <span class="rifas-stat-value"><?php echo esc_html($numeros_disponiveis); ?></span>
                    <span class="rifas-stat-label"><?php esc_html_e('Números Disponíveis', 'rifas'); ?></span>
                </div>
                <div class="rifas-stat">
                    <span class="rifas-stat-value"><?php echo rifas_formatar_valor($valor_total); ?></span>
                    <span class="rifas-stat-label"><?php esc_html_e('Valor Arrecadado', 'rifas'); ?></span>
                </div>
                <div class="rifas-stat">
                    <span class="rifas-stat-value"><?php echo esc_html($total_compradores); ?></span>
                    <span class="rifas-stat-label"><?php esc_html_e('Total de Compradores', 'rifas'); ?></span>
                </div>
                <?php if ($numeros_vendidos > 0) : ?>
                <div class="rifas-stat">
                    <span class="rifas-stat-value"><?php echo rifas_formatar_valor($estatisticas['valor_medio']); ?></span>
                    <span class="rifas-stat-label"><?php esc_html_e('Valor Médio por Compra', 'rifas'); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Shortcode -->
        <div class="rifas-card">
            <div class="rifas-card-header">
                <h2>
                    <span class="dashicons dashicons-shortcode"></span>
                    <?php esc_html_e('Shortcode', 'rifas'); ?>
                </h2>
            </div>
            <div class="rifas-card-body">
                <p><?php esc_html_e('Use o shortcode abaixo para exibir o formulário de rifas em qualquer página ou post:', 'rifas'); ?></p>
                <div class="rifas-shortcode-container">
                    <code>[rifas_form]</code>
                    <button class="rifas-copy-shortcode button button-small" data-clipboard-text="[rifas_form]">
                        <span class="dashicons dashicons-clipboard"></span>
                        <?php esc_html_e('Copiar', 'rifas'); ?>
                    </button>
                </div>
                <p class="description"><?php esc_html_e('Este shortcode exibirá o formulário completo, incluindo a seleção de números.', 'rifas'); ?></p>
            </div>
        </div>
        
        <!-- Vendas Recentes -->
        <div class="rifas-card">
            <div class="rifas-card-header">
                <h2>
                    <span class="dashicons dashicons-list-view"></span>
                    <?php esc_html_e('Vendas Recentes', 'rifas'); ?>
                </h2>
            </div>
            <div class="rifas-card-body">
                <?php
                global $wpdb;
                $table_compras = $wpdb->prefix . 'rifas_compras';
                
                $compras_recentes = $wpdb->get_results(
                    "SELECT * FROM $table_compras ORDER BY data_compra DESC LIMIT 5"
                );
                
                if (empty($compras_recentes)) :
                ?>
                <p><?php esc_html_e('Nenhuma compra registrada ainda.', 'rifas'); ?></p>
                <?php else : ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Nome', 'rifas'); ?></th>
                            <th><?php esc_html_e('Email', 'rifas'); ?></th>
                            <th><?php esc_html_e('Valor', 'rifas'); ?></th>
                            <th><?php esc_html_e('Números', 'rifas'); ?></th>
                            <th><?php esc_html_e('Data', 'rifas'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($compras_recentes as $compra) : ?>
                        <tr>
                            <td><?php echo esc_html($compra->nome); ?></td>
                            <td><?php echo esc_html($compra->email); ?></td>
                            <td><?php echo rifas_formatar_valor($compra->valor); ?></td>
                            <td>
                                <?php 
                                $numeros = explode(',', $compra->numeros_selecionados);
                                if (count($numeros) > 5) {
                                    echo esc_html(implode(', ', array_slice($numeros, 0, 5))) . '...';
                                } else {
                                    echo esc_html($compra->numeros_selecionados);
                                }
                                echo ' <span class="rifas-numeros-count">(' . count($numeros) . ')</span>';
                                ?>
                            </td>
                            <td><?php echo date_i18n('d/m/Y H:i', strtotime($compra->data_compra)); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="rifas-card-footer">
                    <a href="<?php echo admin_url('admin.php?page=rifas-compras'); ?>" class="button button-small">
                        <?php esc_html_e('Ver todas as compras', 'rifas'); ?>
                    </a>
                </p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Gráfico de Vendas -->
        <?php if (!empty($estatisticas['vendas_por_dia'])) : ?>
        <div class="rifas-card rifas-card-full">
            <div class="rifas-card-header">
                <h2>
                    <span class="dashicons dashicons-chart-bar"></span>
                    <?php esc_html_e('Vendas dos Últimos 30 Dias', 'rifas'); ?>
                </h2>
            </div>
            <div class="rifas-card-body">
                <div class="rifas-grafico-container">
                    <canvas id="rifas-grafico-vendas" height="300"></canvas>
                </div>
                <script>
                    jQuery(document).ready(function($) {
                        var ctx = document.getElementById('rifas-grafico-vendas').getContext('2d');
                        var dados = <?php echo json_encode($estatisticas['vendas_por_dia']); ?>;
                        
                        var labels = [];
                        var valores = [];
                        var compras = [];
                        
                        // Inverter a ordem para cronológica
                        dados.reverse();
                        
                        dados.forEach(function(item) {
                            var data = new Date(item.data);
                            labels.push(data.toLocaleDateString('pt-BR'));
                            valores.push(parseFloat(item.valor_total));
                            compras.push(parseInt(item.total_compras));
                        });
                        
                        var myChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: '<?php esc_html_e('Valor Arrecadado (R$)', 'rifas'); ?>',
                                    data: valores,
                                    backgroundColor: 'rgba(76, 175, 80, 0.5)',
                                    borderColor: 'rgba(76, 175, 80, 1)',
                                    borderWidth: 1,
                                    yAxisID: 'y-axis-1'
                                }, {
                                    label: '<?php esc_html_e('Quantidade de Compras', 'rifas'); ?>',
                                    data: compras,
                                    type: 'line',
                                    fill: false,
                                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    borderWidth: 2,
                                    pointRadius: 4,
                                    pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                                    yAxisID: 'y-axis-2'
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    x: {
                                        title: {
                                            display: true,
                                            text: '<?php esc_html_e('Data', 'rifas'); ?>'
                                        }
                                    },
                                    'y-axis-1': {
                                        type: 'linear',
                                        display: true,
                                        position: 'left',
                                        title: {
                                            display: true,
                                            text: '<?php esc_html_e('Valor (R$)', 'rifas'); ?>'
                                        },
                                        ticks: {
                                            beginAtZero: true,
                                            callback: function(value) {
                                                return 'R$ ' + value;
                                            }
                                        }
                                    },
                                    'y-axis-2': {
                                        type: 'linear',
                                        display: true,
                                        position: 'right',
                                        grid: {
                                            drawOnChartArea: false,
                                        },
                                        title: {
                                            display: true,
                                            text: '<?php esc_html_e('Quantidade', 'rifas'); ?>'
                                        },
                                        ticks: {
                                            beginAtZero: true,
                                            stepSize: 1
                                        }
                                    }
                                }
                            }
                        });
                    });
                </script>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Top Compradores -->
        <?php if (!empty($estatisticas['top_compradores'])) : ?>
        <div class="rifas-card">
            <div class="rifas-card-header">
                <h2>
                    <span class="dashicons dashicons-groups"></span>
                    <?php esc_html_e('Top Compradores', 'rifas'); ?>
                </h2>
            </div>
            <div class="rifas-card-body">
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Nome', 'rifas'); ?></th>
                            <th><?php esc_html_e('Email', 'rifas'); ?></th>
                            <th><?php esc_html_e('Total Gasto', 'rifas'); ?></th>
                            <th><?php esc_html_e('Compras', 'rifas'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estatisticas['top_compradores'] as $comprador) : ?>
                        <tr>
                            <td><?php echo esc_html($comprador->nome); ?></td>
                            <td><?php echo esc_html($comprador->email); ?></td>
                            <td><?php echo rifas_formatar_valor($comprador->total_gasto); ?></td>
                            <td><?php echo esc_html($comprador->total_compras); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Registros de Atividade -->
        <?php if (!empty($logs)) : ?>
        <div class="rifas-card">
            <div class="rifas-card-header">
                <h2>
                    <span class="dashicons dashicons-clock"></span>
                    <?php esc_html_e('Atividades Recentes', 'rifas'); ?>
                </h2>
            </div>
            <div class="rifas-card-body">
                <div class="rifas-log-list">
                    <?php foreach ($logs as $log) : ?>
                    <div class="rifas-log-item">
                        <span class="rifas-log-time"><?php echo date_i18n('d/m/Y H:i', $log['timestamp']); ?></span>
                        <span class="rifas-log-action"><?php echo esc_html($log['acao']); ?></span>
                        <?php if (!empty($log['dados'])) : ?>
                        <div class="rifas-log-details">
                            <?php
                            foreach ($log['dados'] as $key => $value) {
                                echo '<span>' . esc_html($key) . ': ' . esc_html(is_array($value) ? implode(', ', $value) : $value) . '</span>';
                            }
                            ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Informações do Plugin -->
        <div class="rifas-card">
            <div class="rifas-card-header">
                <h2>
                    <span class="dashicons dashicons-info"></span>
                    <?php esc_html_e('Informações do Plugin', 'rifas'); ?>
                </h2>
            </div>
            <div class="rifas-card-body">
                <table class="widefat" cellspacing="0">
                    <tbody>
                        <tr>
                            <th><?php esc_html_e('Versão do Plugin:', 'rifas'); ?></th>
                            <td>1.0.0</td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Valor por Número:', 'rifas'); ?></th>
                            <td><?php echo rifas_formatar_valor($valor_por_numero); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Quantidade de Números:', 'rifas'); ?></th>
                            <td><?php echo esc_html($total_numeros); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Configurações:', 'rifas'); ?></th>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=rifas-configuracoes'); ?>" class="button button-small">
                                    <?php esc_html_e('Editar Configurações', 'rifas'); ?>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Estilo adicional para este template -->
<style>
    .rifas-dashboard {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .rifas-card-full {
        grid-column: 1 / -1;
    }
    
    .rifas-dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 20px 0;
        background: #fff;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .rifas-progresso {
        width: 70%;
    }
    
    .rifas-barra-progresso {
        height: 30px;
        background-color: #f0f0f0;
        border-radius: 15px;
        overflow: hidden;
        margin-bottom: 5px;
    }
    
    .rifas-barra-progresso-preenchimento {
        height: 100%;
        background-color: var(--rifas-cor-principal, #4CAF50);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 30px;
    }
    
    .rifas-barra-progresso-texto {
        color: white;
        font-weight: bold;
    }
    
    .rifas-barra-legenda {
        font-size: 12px;
        color: #666;
    }
    
    .rifas-acoes-rapidas {
        display: flex;
        gap: 10px;
    }
    
    .rifas-shortcode-container {
        display: flex;
        align-items: center;
        background: #f5f5f5;
        padding: 10px;
        border-radius: 4px;
        margin: 10px 0;
    }
    
    .rifas-shortcode-container code {
        flex-grow: 1;
        background: none;
        padding: 0 10px;
    }
    
    .rifas-card-footer {
        margin-top: 15px;
        text-align: right;
    }
    
    .rifas-grafico-container {
        position: relative;
        height: 300px;
    }
    
    .rifas-log-list {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #eee;
        border-radius: 4px;
    }
    
    .rifas-log-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .rifas-log-item:last-child {
        border-bottom: none;
    }
    
    .rifas-log-time {
        font-size: 12px;
        color: #666;
        margin-right: 10px;
    }
    
    .rifas-log-action {
        font-weight: bold;
    }
    
    .rifas-log-details {
        margin-top: 5px;
        padding-left: 10px;
        border-left: 3px solid #eee;
        font-size: 12px;
        color: #666;
        display: flex;
        flex-direction: column;
    }
    
    .rifas-numeros-count {
        color: #666;
        font-size: 12px;
    }

    @media (max-width: 768px) {
        .rifas-dashboard {
            grid-template-columns: 1fr;
        }
        
        .rifas-dashboard-header {
            flex-direction: column;
        }
        
        .rifas-progresso {
            width: 100%;
            margin-bottom: 15px;
        }
        
        .rifas-acoes-rapidas {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<!-- Script para copiar shortcode -->
<script>
    jQuery(document).ready(function($) {
        $('.rifas-copy-shortcode').on('click', function(e) {
            e.preventDefault();
            
            var texto = $(this).data('clipboard-text');
            
            // Criar elemento temporário
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(texto).select();
            document.execCommand("copy");
            $temp.remove();
            
            // Alterar texto do botão temporariamente
            var $botao = $(this);
            var textoOriginal = $botao.html();
            $botao.html('<span class="dashicons dashicons-yes"></span> Copiado!');
            
            setTimeout(function() {
                $botao.html(textoOriginal);
            }, 2000);
        });
        
        // Carregar Chart.js se não estiver disponível
        if (typeof Chart === 'undefined') {
            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js';
            script.onload = function() {
                // Initialize charts after loading
                if (typeof initializeCharts === 'function') {
                    initializeCharts();
                }
            };
            document.head.appendChild(script);
        }
    });
</script>