<?php
/**
 * Template para a página de compras administrativas do Plugin Rifas
 *
 * @package Rifas
 */

// Impedir acesso direto
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap rifas-wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-cart" style="font-size: 30px; height: 30px; width: 30px; padding-right: 10px;"></span>
        <?php esc_html_e('Compras de Rifas', 'rifas'); ?>
    </h1>
    
    <a href="<?php echo esc_url(add_query_arg(array('action' => 'export', '_wpnonce' => wp_create_nonce('rifas_export_nonce')))); ?>" class="page-title-action rifas-export-link">
        <span class="dashicons dashicons-download"></span>
        <?php esc_html_e('Exportar CSV', 'rifas'); ?>
    </a>
    
    <hr class="wp-header-end">
    
    <?php
    // Exibir filtros de pesquisa se houver compras
    if ($total_items > 0) :
    ?>
    <div class="tablenav top">
        <div class="alignleft actions">
            <form method="get" action="">
                <input type="hidden" name="page" value="rifas-compras">
                
                <label for="filter-by-date" class="screen-reader-text"><?php esc_html_e('Filtrar por data', 'rifas'); ?></label>
                <select name="filter_date" id="filter-by-date">
                    <option value=""><?php esc_html_e('Todas as datas', 'rifas'); ?></option>
                    <?php
                    // Obter meses com compras
                    global $wpdb;
                    $table_compras = $wpdb->prefix . 'rifas_compras';
                    $meses = $wpdb->get_results("SELECT DISTINCT YEAR(data_compra) as ano, MONTH(data_compra) as mes FROM $table_compras ORDER BY ano DESC, mes DESC");
                    
                    $meses_nomes = array(
                        1 => __('Janeiro', 'rifas'),
                        2 => __('Fevereiro', 'rifas'),
                        3 => __('Março', 'rifas'),
                        4 => __('Abril', 'rifas'),
                        5 => __('Maio', 'rifas'),
                        6 => __('Junho', 'rifas'),
                        7 => __('Julho', 'rifas'),
                        8 => __('Agosto', 'rifas'),
                        9 => __('Setembro', 'rifas'),
                        10 => __('Outubro', 'rifas'),
                        11 => __('Novembro', 'rifas'),
                        12 => __('Dezembro', 'rifas')
                    );
                    
                    $filtro_atual = isset($_GET['filter_date']) ? sanitize_text_field($_GET['filter_date']) : '';
                    
                    foreach ($meses as $mes) {
                        $valor = $mes->ano . '-' . str_pad($mes->mes, 2, '0', STR_PAD_LEFT);
                        $texto = $meses_nomes[intval($mes->mes)] . ' ' . $mes->ano;
                        
                        echo '<option value="' . esc_attr($valor) . '" ' . selected($filtro_atual, $valor, false) . '>' . esc_html($texto) . '</option>';
                    }
                    ?>
                </select>
                
                <label for="filter-by-number" class="screen-reader-text"><?php esc_html_e('Filtrar por número', 'rifas'); ?></label>
                <input type="text" id="filter-by-number" name="filter_numero" placeholder="<?php esc_attr_e('Buscar por número', 'rifas'); ?>" value="<?php echo isset($_GET['filter_numero']) ? esc_attr(sanitize_text_field($_GET['filter_numero'])) : ''; ?>">
                
                <?php submit_button(__('Filtrar', 'rifas'), 'action', 'filter_action', false); ?>
            </form>
        </div>
        
        <?php
        // Paginação
        $total_pages = ceil($total_items / $per_page);
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        
        if ($total_pages > 1) :
        ?>
        <div class="tablenav-pages">
            <span class="displaying-num">
                <?php
                printf(
                    _n(
                        '%s item',
                        '%s itens',
                        $total_items,
                        'rifas'
                    ),
                    number_format_i18n($total_items)
                );
                ?>
            </span>
            
            <span class="pagination-links">
                <?php
                // Link para primeira página
                if ($current_page > 1) {
                    echo '<a class="first-page button" href="' . esc_url(add_query_arg('paged', 1)) . '"><span class="screen-reader-text">' . __('Primeira página', 'rifas') . '</span><span aria-hidden="true">&laquo;</span></a>';
                } else {
                    echo '<span class="first-page button disabled"><span class="screen-reader-text">' . __('Primeira página', 'rifas') . '</span><span aria-hidden="true">&laquo;</span></span>';
                }
                
                // Link para página anterior
                if ($current_page > 1) {
                    echo '<a class="prev-page button" href="' . esc_url(add_query_arg('paged', max(1, $current_page - 1))) . '"><span class="screen-reader-text">' . __('Página anterior', 'rifas') . '</span><span aria-hidden="true">&lsaquo;</span></a>';
                } else {
                    echo '<span class="prev-page button disabled"><span class="screen-reader-text">' . __('Página anterior', 'rifas') . '</span><span aria-hidden="true">&lsaquo;</span></span>';
                }
                
                // Input para pular para página específica
                echo '<span class="paging-input">';
                echo '<label for="current-page-selector" class="screen-reader-text">' . __('Página atual', 'rifas') . '</label>';
                echo '<input class="current-page" id="current-page-selector" type="text" name="paged" value="' . esc_attr($current_page) . '" size="1" aria-describedby="table-paging">';
                echo '<span class="tablenav-paging-text"> ' . __('de', 'rifas') . ' <span class="total-pages">' . esc_html($total_pages) . '</span></span>';
                echo '</span>';
                
                // Link para próxima página
                if ($current_page < $total_pages) {
                    echo '<a class="next-page button" href="' . esc_url(add_query_arg('paged', min($total_pages, $current_page + 1))) . '"><span class="screen-reader-text">' . __('Próxima página', 'rifas') . '</span><span aria-hidden="true">&rsaquo;</span></a>';
                } else {
                    echo '<span class="next-page button disabled"><span class="screen-reader-text">' . __('Próxima página', 'rifas') . '</span><span aria-hidden="true">&rsaquo;</span></span>';
                }
                
                // Link para última página
                if ($current_page < $total_pages) {
                    echo '<a class="last-page button" href="' . esc_url(add_query_arg('paged', $total_pages)) . '"><span class="screen-reader-text">' . __('Última página', 'rifas') . '</span><span aria-hidden="true">&raquo;</span></a>';
                } else {
                    echo '<span class="last-page button disabled"><span class="screen-reader-text">' . __('Última página', 'rifas') . '</span><span aria-hidden="true">&raquo;</span></span>';
                }
                ?>
            </span>
        </div>
        <?php endif; ?>
        
        <br class="clear">
    </div>
    <?php endif; ?>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="column-id"><?php esc_html_e('ID', 'rifas'); ?></th>
                <th scope="col" class="column-nome"><?php esc_html_e('Nome', 'rifas'); ?></th>
                <th scope="col" class="column-email"><?php esc_html_e('Email', 'rifas'); ?></th>
                <th scope="col" class="column-valor"><?php esc_html_e('Valor', 'rifas'); ?></th>
                <th scope="col" class="column-numeros"><?php esc_html_e('Números', 'rifas'); ?></th>
                <th scope="col" class="column-data_compra"><?php esc_html_e('Data', 'rifas'); ?></th>
                <th scope="col" class="column-acoes"><?php esc_html_e('Ações', 'rifas'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (empty($compras)) :
            ?>
            <tr>
                <td colspan="7"><?php esc_html_e('Nenhuma compra encontrada.', 'rifas'); ?></td>
            </tr>
            <?php
            else :
                foreach ($compras as $compra) :
                    $numeros_array = explode(',', $compra->numeros_selecionados);
                    $total_numeros = count($numeros_array);
                    $numeros_exibicao = $total_numeros > 10 ? implode(', ', array_slice($numeros_array, 0, 10)) . '...' : $compra->numeros_selecionados;
            ?>
            <tr>
                <td><?php echo esc_html($compra->id); ?></td>
                <td><?php echo esc_html($compra->nome); ?></td>
                <td><?php echo esc_html($compra->email); ?></td>
                <td><?php echo rifas_formatar_valor($compra->valor); ?></td>
                <td>
                    <div class="rifas-numeros-list">
                        <?php echo esc_html($numeros_exibicao); ?>
                        <div class="rifas-numeros-count"><?php echo sprintf(_n('%d número', '%d números', $total_numeros, 'rifas'), $total_numeros); ?></div>
                    </div>
                </td>
                <td><?php echo date_i18n('d/m/Y H:i', strtotime($compra->data_compra)); ?></td>
                <td>
                    <button type="button" class="button button-small rifas-detalhes-button" 
                            data-id="<?php echo esc_attr($compra->id); ?>"
                            data-nome="<?php echo esc_attr($compra->nome); ?>"
                            data-email="<?php echo esc_attr($compra->email); ?>"
                            data-valor="<?php echo esc_attr(rifas_formatar_valor($compra->valor)); ?>"
                            data-numeros="<?php echo esc_attr($compra->numeros_selecionados); ?>"
                            data-data="<?php echo esc_attr(date_i18n('d/m/Y H:i', strtotime($compra->data_compra))); ?>">
                        <?php esc_html_e('Detalhes', 'rifas'); ?>
                    </button>
                </td>
            </tr>
            <?php
                endforeach;
            endif;
            ?>
        </tbody>
    </table>
    
    <?php if ($total_pages > 1) : ?>
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <span class="displaying-num">
                <?php
                printf(
                    _n(
                        '%s item',
                        '%s itens',
                        $total_items,
                        'rifas'
                    ),
                    number_format_i18n($total_items)
                );
                ?>
            </span>
            
            <span class="pagination-links">
                <?php
                // Link para primeira página
                if ($current_page > 1) {
                    echo '<a class="first-page button" href="' . esc_url(add_query_arg('paged', 1)) . '"><span class="screen-reader-text">' . __('Primeira página', 'rifas') . '</span><span aria-hidden="true">&laquo;</span></a>';
                } else {
                    echo '<span class="first-page button disabled"><span class="screen-reader-text">' . __('Primeira página', 'rifas') . '</span><span aria-hidden="true">&laquo;</span></span>';
                }
                
                // Link para página anterior
                if ($current_page > 1) {
                    echo '<a class="prev-page button" href="' . esc_url(add_query_arg('paged', max(1, $current_page - 1))) . '"><span class="screen-reader-text">' . __('Página anterior', 'rifas') . '</span><span aria-hidden="true">&lsaquo;</span></a>';
                } else {
                    echo '<span class="prev-page button disabled"><span class="screen-reader-text">' . __('Página anterior', 'rifas') . '</span><span aria-hidden="true">&lsaquo;</span></span>';
                }
                
                // Input para pular para página específica
                echo '<span class="paging-input">';
                echo '<label for="current-page-selector-bottom" class="screen-reader-text">' . __('Página atual', 'rifas') . '</label>';
                echo '<input class="current-page" id="current-page-selector-bottom" type="text" name="paged" value="' . esc_attr($current_page) . '" size="1" aria-describedby="table-paging">';
                echo '<span class="tablenav-paging-text"> ' . __('de', 'rifas') . ' <span class="total-pages">' . esc_html($total_pages) . '</span></span>';
                echo '</span>';
                
                // Link para próxima página
                if ($current_page < $total_pages) {
                    echo '<a class="next-page button" href="' . esc_url(add_query_arg('paged', min($total_pages, $current_page + 1))) . '"><span class="screen-reader-text">' . __('Próxima página', 'rifas') . '</span><span aria-hidden="true">&rsaquo;</span></a>';
                } else {
                    echo '<span class="next-page button disabled"><span class="screen-reader-text">' . __('Próxima página', 'rifas') . '</span><span aria-hidden="true">&rsaquo;</span></span>';
                }
                
                // Link para última página
                if ($current_page < $total_pages) {
                    echo '<a class="last-page button" href="' . esc_url(add_query_arg('paged', $total_pages)) . '"><span class="screen-reader-text">' . __('Última página', 'rifas') . '</span><span aria-hidden="true">&raquo;</span></a>';
                } else {
                    echo '<span class="last-page button disabled"><span class="screen-reader-text">' . __('Última página', 'rifas') . '</span><span aria-hidden="true">&raquo;</span></span>';
                }
                ?>
            </span>
        </div>
        <br class="clear">
    </div>
    <?php endif; ?>
</div>

<!-- Modal de detalhes da compra -->
<div id="rifas-modal" class="rifas-modal">
    <div class="rifas-modal-content">
        <span class="rifas-modal-close">&times;</span>
        <h2 id="rifas-modal-title" class="rifas-modal-title"></h2>
        
        <div class="rifas-modal-detalhes">
            <p><strong><?php esc_html_e('Nome:', 'rifas'); ?></strong> <span id="rifas-modal-nome"></span></p>
            <p><strong><?php esc_html_e('Email:', 'rifas'); ?></strong> <span id="rifas-modal-email"></span></p>
            <p><strong><?php esc_html_e('Valor:', 'rifas'); ?></strong> <span id="rifas-modal-valor"></span></p>
            <p><strong><?php esc_html_e('Data:', 'rifas'); ?></strong> <span id="rifas-modal-data"></span></p>
            <p><strong><?php esc_html_e('Números:', 'rifas'); ?></strong></p>
            <div class="rifas-modal-numeros">
                <p id="rifas-modal-numeros"></p>
            </div>
        </div>
        
        <div class="rifas-modal-footer">
            <button class="button rifas-modal-btn-fechar"><?php esc_html_e('Fechar', 'rifas'); ?></button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Abrir modal de detalhes
    $('.rifas-detalhes-button').on('click', function() {
        var id = $(this).data('id');
        var nome = $(this).data('nome');
        var email = $(this).data('email');
        var valor = $(this).data('valor');
        var numeros = $(this).data('numeros');
        var data = $(this).data('data');
        
        // Preencher modal
        $('#rifas-modal-title').text('<?php esc_html_e('Detalhes da Compra #', 'rifas'); ?>' + id);
        $('#rifas-modal-nome').text(nome);
        $('#rifas-modal-email').text(email);
        $('#rifas-modal-valor').text(valor);
        $('#rifas-modal-numeros').text(numeros);
        $('#rifas-modal-data').text(data);
        
        // Exibir modal
        $('#rifas-modal').show();
    });
    
    // Fechar modal
    $('.rifas-modal-close, .rifas-modal-btn-fechar').on('click', function() {
        $('#rifas-modal').hide();
    });
    
    // Fechar modal ao clicar fora
    $(window).on('click', function(e) {
        if ($(e.target).is('#rifas-modal')) {
            $('#rifas-modal').hide();
        }
    });
    
    // Destacar linha após filtro
    if (window.location.search.indexOf('filter_action') !== -1) {
        setTimeout(function() {
            $('.wp-list-table tbody tr').addClass('rifas-table-row-highlight');
        }, 100);
    }
});
</script>