<?php
/**
 * Template para a página de configurações administrativas do Plugin Rifas
 *
 * @package Rifas
 */

// Impedir acesso direto
if (!defined('ABSPATH')) {
    exit;
}

// Exibir mensagens de erro ou sucesso
settings_errors('rifas_options');
?>

<div class="wrap rifas-wrap">
    <h1>
        <span class="dashicons dashicons-admin-settings" style="font-size: 30px; height: 30px; width: 30px; padding-right: 10px;"></span>
        <?php esc_html_e('Configurações do Plugin Rifas', 'rifas'); ?>
    </h1>
    
    <p class="description">
        <?php esc_html_e('Configure as opções do plugin de rifas, incluindo cores, valores e quantidades.', 'rifas'); ?>
    </p>
    
    <form method="post" action="">
        <?php wp_nonce_field('rifas_settings_nonce'); ?>
        <input type="hidden" name="rifas_save_settings" value="1">
        
        <div class="rifas-admin-tabs">
            <div class="nav-tab-wrapper">
                <a href="#tab-geral" class="nav-tab nav-tab-active" data-tab="tab-geral">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php esc_html_e('Geral', 'rifas'); ?>
                </a>
                <a href="#tab-aparencia" class="nav-tab" data-tab="tab-aparencia">
                    <span class="dashicons dashicons-admin-appearance"></span>
                    <?php esc_html_e('Aparência', 'rifas'); ?>
                </a>
                <a href="#tab-emails" class="nav-tab" data-tab="tab-emails">
                    <span class="dashicons dashicons-email"></span>
                    <?php esc_html_e('Emails', 'rifas'); ?>
                </a>
                <a href="#tab-avancado" class="nav-tab" data-tab="tab-avancado">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <?php esc_html_e('Avançado', 'rifas'); ?>
                </a>
            </div>
            
            <!-- Aba Geral -->
            <div id="tab-geral" class="rifas-tab-content active">
                <h2><?php esc_html_e('Configurações Gerais', 'rifas'); ?></h2>
                
                <table class="form-table rifas-form-table">
                    <tr>
                        <th scope="row">
                            <label for="titulo_rifa"><?php esc_html_e('Título da Rifa', 'rifas'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="titulo_rifa" name="titulo_rifa" value="<?php echo esc_attr($options['titulo_rifa']); ?>" class="regular-text">
                            <p class="description"><?php esc_html_e('O título que será exibido no topo do formulário.', 'rifas'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="descricao_rifa"><?php esc_html_e('Descrição da Rifa', 'rifas'); ?></label>
                        </th>
                        <td>
                            <?php 
                            wp_editor(
                                wp_kses_post($options['descricao_rifa']),
                                'descricao_rifa',
                                array(
                                    'textarea_name' => 'descricao_rifa',
                                    'textarea_rows' => 5,
                                    'media_buttons' => false,
                                    'teeny' => true,
                                    'quicktags' => true,
                                )
                            ); 
                            ?>
                            <p class="description"><?php esc_html_e('Uma breve descrição que aparecerá logo abaixo do título.', 'rifas'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="quantidade_numeros"><?php esc_html_e('Quantidade de Números', 'rifas'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="quantidade_numeros" name="quantidade_numeros" value="<?php echo esc_attr($options['quantidade_numeros']); ?>" min="1" max="10000" class="small-text">
                            <p class="description"><?php esc_html_e('Total de números disponíveis para venda na rifa. Atenção: reduzir esse número pode causar problemas se já houver números vendidos acima do novo limite.', 'rifas'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="valor_por_numero"><?php esc_html_e('Valor por Número (R$)', 'rifas'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="valor_por_numero" name="valor_por_numero" value="<?php echo esc_attr($options['valor_por_numero']); ?>" min="1" step="0.01" class="small-text">
                            <p class="description"><?php esc_html_e('Valor unitário de cada número da rifa.', 'rifas'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Aba Aparência -->
            <div id="tab-aparencia" class="rifas-tab-content">
                <h2><?php esc_html_e('Configurações de Aparência', 'rifas'); ?></h2>
                
                <table class="form-table rifas-form-table">
                    <tr>
                        <th scope="row">
                            <label for="cor_principal"><?php esc_html_e('Cor Principal', 'rifas'); ?></label>
                        </th>
                        <td>
                            <input type="color" id="cor_principal" name="cor_principal" value="<?php echo esc_attr($options['cor_principal']); ?>" class="color-picker">
                            <span class="color-preview" style="background-color: <?php echo esc_attr($options['cor_principal']); ?>;"></span>
                            <p class="description"><?php esc_html_e('Cor principal para botões e elementos de destaque.', 'rifas'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="cor_secundaria"><?php esc_html_e('Cor Secundária', 'rifas'); ?></label>
                        </th>
                        <td>
                            <input type="color" id="cor_secundaria" name="cor_secundaria" value="<?php echo esc_attr($options['cor_secundaria']); ?>" class="color-picker">
                            <span class="color-preview" style="background-color: <?php echo esc_attr($options['cor_secundaria']); ?>;"></span>
                            <p class="description"><?php esc_html_e('Cor secundária para hover e elementos adicionais.', 'rifas'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="cor_texto"><?php esc_html_e('Cor do Texto', 'rifas'); ?></label>
                        </th>
                        <td>
                            <input type="color" id="cor_texto" name="cor_texto" value="<?php echo esc_attr($options['cor_texto']); ?>" class="color-picker">
                            <span class="color-preview" style="background-color: <?php echo esc_attr($options['cor_texto']); ?>;"></span>
                            <p class="description"><?php esc_html_e('Cor do texto em botões e elementos coloridos.', 'rifas'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="cor_numeros_disponiveis"><?php esc_html_e('Cor de Fundo - Números Disponíveis', 'rifas'); ?></label>
                        </th>
                        <td>
                            <input type="color" id="cor_numeros_disponiveis" name="cor_numeros_disponiveis" value="<?php echo esc_attr($options['cor_numeros_disponiveis']); ?>" class="color-picker">
                            <span class="color-preview" style="background-color: <?php echo esc_attr($options['cor_numeros_disponiveis']); ?>;"></span>
                            <p class="description"><?php esc_html_e('Cor de fundo para números disponíveis.', 'rifas'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="cor_numeros_vendidos"><?php esc_html_e('Cor de Fundo - Números Vendidos', 'rifas'); ?></label>
                        </th>
                        <td>
                            <input type="color" id="cor_numeros_vendidos" name="cor_numeros_vendidos" value="<?php echo esc_attr($options['cor_numeros_vendidos']); ?>" class="color-picker">
                            <span class="color-preview" style="background-color: <?php echo esc_attr($options['cor_numeros_vendidos']); ?>;"></span>
                            <p class="description"><?php esc_html_e('Cor de fundo para números vendidos.', 'rifas'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <div class="rifas-preview-container">
                    <h3><?php esc_html_e('Pré-visualização', 'rifas'); ?></h3>
                    <div class="rifas-preview">
                        <div class="rifas-preview-numero disponivel">1</div>
                        <div class="rifas-preview-numero selecionado">2</div>
                        <div class="rifas-preview-numero vendido">3</div>
                        <button class="rifas-preview-botao"><?php esc_html_e('Botão', 'rifas'); ?></button>
                    </div>
                </div>
            </div>
            
            <!-- Aba Emails -->
            <div id="tab-emails" class="rifas-tab-content">
                <h2><?php esc_html_e('Configurações de Email', 'rifas'); ?></h2>
                
                <table class="form-table rifas-form-table">
                    <tr>
                        <th scope="row">
                            <label for="email_remetente"><?php esc_html_e('Email do Remetente', 'rifas'); ?></label>
                        </th>
                        <td>
                            <input type="email" id="email_remetente" name="email_remetente" value="<?php echo esc_attr(isset($options['email_remetente']) ? $options['email_remetente'] : get_option('admin_email')); ?>" class="regular-text">
                            <p class="description"><?php esc_html_e('Email que será exibido como remetente nas mensagens.', 'rifas'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="nome_remetente"><?php esc_html_e('Nome do Remetente', 'rifas'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="nome_remetente" name="nome_remetente" value="<?php echo esc_attr(isset($options['nome_remetente']) ? $options['nome_remetente'] : get_bloginfo('name')); ?>" class="regular-text">
                            <p class="description"><?php esc_html_e('Nome que será exibido como remetente nas mensagens.', 'rifas'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="enviar_copia_admin"><?php esc_html_e('Receber Cópia das Compras', 'rifas'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" id="enviar_copia_admin" name="enviar_copia_admin" value="1" <?php checked(isset($options['enviar_copia_admin']) ? $options['enviar_copia_admin'] : 0, 1); ?>>
                                <?php esc_html_e('Enviar uma cópia de cada confirmação de compra para o administrador', 'rifas'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <div class="rifas-email-template">
                    <h3><?php esc_html_e('Template de Email', 'rifas'); ?></h3>
                    <p class="description"><?php esc_html_e('O template de email será gerado automaticamente com base nas cores e configurações escolhidas.', 'rifas'); ?></p>
                    
                    <div class="rifas-email-preview">
                        <div class="rifas-email-preview-header" style="background-color: <?php echo esc_attr($options['cor_principal']); ?>; color: <?php echo esc_attr($options['cor_texto']); ?>;">
                            <h3><?php echo esc_html($options['titulo_rifa']); ?></h3>
                        </div>
                        <div class="rifas-email-preview-body">
                            <p><?php esc_html_e('Olá {nome},', 'rifas'); ?></p>
                            <p><?php esc_html_e('Sua compra foi confirmada!', 'rifas'); ?></p>
                            <div class="rifas-email-preview-details" style="border-left-color: <?php echo esc_attr($options['cor_principal']); ?>;">
                                <p><strong><?php esc_html_e('Valor:', 'rifas'); ?></strong> R$ 100,00</p>
                                <p><strong><?php esc_html_e('Números:', 'rifas'); ?></strong> 1, 2, 3, 4, 5</p>
                            </div>
                            <p><?php esc_html_e('Obrigado por participar!', 'rifas'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Aba Avançado -->
            <div id="tab-avancado" class="rifas-tab-content">
                <h2><?php esc_html_e('Configurações Avançadas', 'rifas'); ?></h2>
                
                <table class="form-table rifas-form-table">
                    <tr>
                        <th scope="row">
                            <label for="registrar_logs"><?php esc_html_e('Registrar Logs', 'rifas'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" id="registrar_logs" name="registrar_logs" value="1" <?php checked(isset($options['registrar_logs']) ? $options['registrar_logs'] : 1, 1); ?>>
                                <?php esc_html_e('Registrar logs de atividade do plugin', 'rifas'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('Registrar informações como compras, alterações de configuração e outras atividades.', 'rifas'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="max_logs"><?php esc_html_e('Máximo de Logs', 'rifas'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="max_logs" name="max_logs" value="<?php echo esc_attr(isset($options['max_logs']) ? $options['max_logs'] : 100); ?>" min="10" max="1000" class="small-text">
                            <p class="description"><?php esc_html_e('Quantidade máxima de registros de log a serem mantidos.', 'rifas'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="numeros_por_pagina"><?php esc_html_e('Números por Página', 'rifas'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="numeros_por_pagina" name="numeros_por_pagina" value="<?php echo esc_attr(isset($options['numeros_por_pagina']) ? $options['numeros_por_pagina'] : 100); ?>" min="10" max="500" class="small-text">
                            <p class="description"><?php esc_html_e('Quantidade de números carregados por vez no formulário de seleção.', 'rifas'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <h3><?php esc_html_e('Manutenção', 'rifas'); ?></h3>
                
                <div class="rifas-maintenance-actions">
                    <p>
                        <button type="button" id="rifas-limpar-logs" class="button button-secondary">
                            <span class="dashicons dashicons-trash"></span>
                            <?php esc_html_e('Limpar Logs Antigos', 'rifas'); ?>
                        </button>
                        <span class="description"><?php esc_html_e('Remove logs mais antigos que 30 dias.', 'rifas'); ?></span>
                    </p>
                    
                    <p>
                        <button type="button" id="rifas-reset-config" class="button button-secondary">
                            <span class="dashicons dashicons-image-rotate"></span>
                            <?php esc_html_e('Restaurar Configurações Padrão', 'rifas'); ?>
                        </button>
                        <span class="description"><?php esc_html_e('Restaura todas as configurações para os valores padrão.', 'rifas'); ?></span>
                    </p>
                </div>
            </div>
        </div>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Salvar Configurações', 'rifas'); ?>">
        </p>
    </form>
</div>

<style>
/* Estilos para a página de configurações */
.rifas-admin-tabs {
    margin-top: 20px;
}

.rifas-tab-content {
    display: none;
    padding: 20px;
    background: #fff;
    border: 1px solid #ccc;
    border-top: none;
}

.rifas-tab-content.active {
    display: block;
}

.rifas-preview-container {
    margin-top: 30px;
    padding: 20px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.rifas-preview {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-top: 10px;
}

.rifas-preview-numero {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 5px;
    font-weight: bold;
}

.rifas-preview-numero.disponivel {
    background-color: <?php echo esc_attr($options['cor_numeros_disponiveis']); ?>;
    border: 1px solid <?php echo esc_attr($options['cor_principal']); ?>;
    color: <?php echo esc_attr($options['cor_principal']); ?>;
}

.rifas-preview-numero.selecionado {
    background-color: <?php echo esc_attr($options['cor_principal']); ?>;
    color: <?php echo esc_attr($options['cor_texto']); ?>;
    border: 1px solid <?php echo esc_attr($options['cor_secundaria']); ?>;
}

.rifas-preview-numero.vendido {
    background-color: <?php echo esc_attr($options['cor_numeros_vendidos']); ?>;
    color: #999;
    border: 1px solid #ddd;
}

.rifas-preview-botao {
    padding: 10px 20px;
    background-color: <?php echo esc_attr($options['cor_principal']); ?>;
    color: <?php echo esc_attr($options['cor_texto']); ?>;
    border: none;
    border-radius: 5px;
    font-weight: bold;
    cursor: pointer;
}

.rifas-email-template {
    margin-top: 30px;
}

.rifas-email-preview {
    max-width: 600px;
    margin-top: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    overflow: hidden;
}

.rifas-email-preview-header {
    padding: 15px;
    text-align: center;
}

.rifas-email-preview-header h3 {
    margin: 0;
}

.rifas-email-preview-body {
    padding: 20px;
    background: #f9f9f9;
}

.rifas-email-preview-details {
    margin: 15px 0;
    padding: 10px;
    background: #fff;
    border-left: 4px solid;
}

.rifas-maintenance-actions {
    padding: 15px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.rifas-maintenance-actions p {
    display: flex;
    align-items: center;
    margin: 10px 0;
}

.rifas-maintenance-actions .button {
    margin-right: 10px;
}

.rifas-maintenance-actions .dashicons {
    margin-right: 5px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Abas de navegação
    $('.rifas-admin-tabs .nav-tab').on('click', function(e) {
        e.preventDefault();
        
        // Remover classe ativa de todas as abas
        $('.rifas-admin-tabs .nav-tab').removeClass('nav-tab-active');
        $('.rifas-tab-content').removeClass('active');
        
        // Adicionar classe ativa na aba clicada
        $(this).addClass('nav-tab-active');
        
        // Mostrar conteúdo da aba
        var tab = $(this).data('tab');
        $('#' + tab).addClass('active');
    });
    
    // Atualizar visualização ao alterar cores
    $('.color-picker').on('change', function() {
        var id = $(this).attr('id');
        var valor = $(this).val();
        
        // Atualizar preview de cor
        $(this).next('.color-preview').css('background-color', valor);
        
        // Atualizar visualização com base no campo alterado
        if (id === 'cor_principal') {
            $('.rifas-preview-numero.disponivel').css('border-color', valor);
            $('.rifas-preview-numero.disponivel').css('color', valor);
            $('.rifas-preview-numero.selecionado').css('background-color', valor);
            $('.rifas-preview-botao').css('background-color', valor);
            $('.rifas-email-preview-header').css('background-color', valor);
            $('.rifas-email-preview-details').css('border-left-color', valor);
        }
        else if (id === 'cor_secundaria') {
            $('.rifas-preview-numero.selecionado').css('border-color', valor);
        }
        else if (id === 'cor_texto') {
            $('.rifas-preview-numero.selecionado').css('color', valor);
            $('.rifas-preview-botao').css('color', valor);
            $('.rifas-email-preview-header').css('color', valor);
        }
        else if (id === 'cor_numeros_disponiveis') {
            $('.rifas-preview-numero.disponivel').css('background-color', valor);
        }
        else if (id === 'cor_numeros_vendidos') {
            $('.rifas-preview-numero.vendido').css('background-color', valor);
        }
    });
    
    // Confirmar reset de configurações
    $('#rifas-reset-config').on('click', function(e) {
        if (!confirm('<?php esc_html_e('Tem certeza que deseja restaurar todas as configurações para os valores padrão? Esta ação não pode ser desfeita.', 'rifas'); ?>')) {
            e.preventDefault();
            return false;
        }
        
        // Enviar requisição AJAX para restaurar configurações
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'rifas_reset_config',
                nonce: '<?php echo wp_create_nonce('rifas_reset_config_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php esc_html_e('Configurações restauradas com sucesso. A página será recarregada.', 'rifas'); ?>');
                    location.reload();
                } else {
                    alert('<?php esc_html_e('Erro ao restaurar configurações.', 'rifas'); ?>');
                }
            },
            error: function() {
                alert('<?php esc_html_e('Erro de conexão ao restaurar configurações.', 'rifas'); ?>');
            }
        });
    });
    
    // Limpar logs antigos
    $('#rifas-limpar-logs').on('click', function(e) {
        if (!confirm('<?php esc_html_e('Tem certeza que deseja limpar os logs antigos?', 'rifas'); ?>')) {
            e.preventDefault();
            return false;
        }
        
        // Enviar requisição AJAX para limpar logs
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'rifas_limpar_logs',
                nonce: '<?php echo wp_create_nonce('rifas_limpar_logs_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php echo esc_js(sprintf(__('Logs antigos removidos com sucesso. %d registros foram excluídos.', 'rifas'), 0)); ?>'.replace('0', response.data.total_removido));
                } else {
                    alert('<?php esc_html_e('Erro ao limpar logs.', 'rifas'); ?>');
                }
            },
            error: function() {
                alert('<?php esc_html_e('Erro de conexão ao limpar logs.', 'rifas'); ?>');
            }
        });
    });
});
</script>