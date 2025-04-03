<?php
/**
 * Template para o formulário de compra de rifas
 *
 * @package Rifas
 */

// Impedir acesso direto
if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="rifas-app" class="rifas-container">
    <!-- Mensagem offline (inicialmente oculta) -->
    <div id="rifas-offline-message" class="rifas-offline-message" style="display: none;">
        <span class="rifas-offline-icon">⚠️</span>
        <?php esc_html_e('Você está offline. Algumas funcionalidades podem não funcionar corretamente.', 'rifas'); ?>
    </div>

    <!-- Cabeçalho -->
    <div class="rifas-header">
        <h2><?php echo esc_html($options['titulo_rifa']); ?></h2>
        <div class="rifas-descricao">
            <?php echo wp_kses_post($options['descricao_rifa']); ?>
        </div>
    </div>
    
    <!-- Etapa 1: Informações do Comprador -->
    <div id="rifas-etapa-1" class="rifas-etapa">
        <h3><?php esc_html_e('Informações do Comprador', 'rifas'); ?></h3>
        
        <div class="rifas-form">
            <div class="rifas-form-group">
                <label for="rifas-nome"><?php esc_html_e('Nome Completo', 'rifas'); ?></label>
                <input type="text" id="rifas-nome" name="rifas-nome" required>
            </div>
            
            <div class="rifas-form-group">
                <label for="rifas-email"><?php esc_html_e('E-mail', 'rifas'); ?></label>
                <input type="email" id="rifas-email" name="rifas-email" required>
                <p class="rifas-instrucao"><?php esc_html_e('Você receberá a confirmação de compra neste e-mail.', 'rifas'); ?></p>
            </div>
            
            <div class="rifas-form-group">
                <label for="rifas-valor"><?php esc_html_e('Valor (R$)', 'rifas'); ?></label>
                <input type="number" id="rifas-valor" name="rifas-valor" min="<?php echo esc_attr($options['valor_por_numero']); ?>" step="1" required>
                <p id="rifas-info-numeros" class="rifas-instrucao"></p>
                <p class="rifas-instrucao">
                    <?php printf(esc_html__('Valor por número: R$ %s', 'rifas'), number_format($options['valor_por_numero'], 2, ',', '.')); ?>
                </p>
            </div>
            
            <div class="rifas-form-actions">
                <button id="rifas-btn-continuar" class="rifas-btn rifas-btn-primary"><?php esc_html_e('Continuar', 'rifas'); ?></button>
            </div>
        </div>
    </div>
    
    <!-- Etapa 2: Seleção de Números -->
    <div id="rifas-etapa-2" class="rifas-etapa" style="display: none;">
        <h3><?php esc_html_e('Selecione seus Números', 'rifas'); ?></h3>
        
        <div class="rifas-filtros">
            <button class="rifas-btn rifas-btn-filtro active" data-filtro="disponiveis"><?php esc_html_e('Disponíveis', 'rifas'); ?></button>
            <button class="rifas-btn rifas-btn-filtro" data-filtro="vendidos"><?php esc_html_e('Vendidos', 'rifas'); ?></button>
            <button class="rifas-btn rifas-btn-filtro" data-filtro="todos"><?php esc_html_e('Todos', 'rifas'); ?></button>
        </div>
        
        <div class="rifas-info-selecao">
            <div>
                <?php esc_html_e('Números selecionados:', 'rifas'); ?> <span id="rifas-numeros-selecionados">0</span>
            </div>
            <div>
                <?php esc_html_e('Máximo de números:', 'rifas'); ?> <span id="rifas-max-numeros">0</span>
            </div>
        </div>
        
        <div id="rifas-grid-numeros" class="rifas-grid-numeros">
            <!-- Os números serão carregados via AJAX -->
        </div>
        
        <!-- Botão Carregar Mais -->
        <div class="rifas-carregar-mais">
            <button id="rifas-carregar-mais" class="rifas-btn rifas-btn-secondary" style="display: none;">
                <?php esc_html_e('Carregar mais números', 'rifas'); ?>
            </button>
        </div>
        
        <div class="rifas-form-actions">
            <button id="rifas-btn-voltar" class="rifas-btn rifas-btn-secondary"><?php esc_html_e('Voltar', 'rifas'); ?></button>
            <button id="rifas-btn-finalizar" class="rifas-btn rifas-btn-primary" disabled><?php esc_html_e('Finalizar Compra', 'rifas'); ?></button>
        </div>
    </div>
    
    <!-- Etapa 3: Confirmação -->
    <div id="rifas-etapa-3" class="rifas-etapa" style="display: none;">
        <div class="rifas-confirmacao">
            <div class="rifas-confirmacao-header">
                <h3><?php esc_html_e('Processando sua compra', 'rifas'); ?></h3>
                <div class="rifas-loading">
                    <div class="rifas-spinner"></div>
                    <p><?php esc_html_e('Aguarde enquanto processamos sua compra...', 'rifas'); ?></p>
                </div>
            </div>
            
            <!-- Sucesso -->
            <div class="rifas-confirmacao-sucesso" style="display: none;">
                <div class="rifas-icon-sucesso">
                    <svg viewBox="0 0 24 24" width="64" height="64">
                        <circle cx="12" cy="12" r="11" fill="none" stroke="currentColor" stroke-width="2" />
                        <path d="M7 13l3 3 7-7" fill="none" stroke="currentColor" stroke-width="2" />
                    </svg>
                </div>
                
                <h3><?php esc_html_e('Compra Realizada com Sucesso!', 'rifas'); ?></h3>
                
                <div class="rifas-detalhes-compra">
                    <p><strong><?php esc_html_e('Nome:', 'rifas'); ?></strong> <span id="rifas-confirm-nome"></span></p>
                    <p><strong><?php esc_html_e('E-mail:', 'rifas'); ?></strong> <span id="rifas-confirm-email"></span></p>
                    <p><strong><?php esc_html_e('Valor:', 'rifas'); ?></strong> R$ <span id="rifas-confirm-valor"></span></p>
                    <p><strong><?php esc_html_e('Números:', 'rifas'); ?></strong> <span id="rifas-confirm-numeros"></span></p>
                </div>
                
                <p class="rifas-msg-email"><?php esc_html_e('Um e-mail de confirmação foi enviado para você.', 'rifas'); ?></p>
                
                <button id="rifas-btn-nova-compra" class="rifas-btn rifas-btn-primary"><?php esc_html_e('Fazer Nova Compra', 'rifas'); ?></button>
            </div>
            
            <!-- Erro -->
            <div class="rifas-confirmacao-erro" style="display: none;">
                <div class="rifas-icon-erro">
                    <svg viewBox="0 0 24 24" width="64" height="64">
                        <circle cx="12" cy="12" r="11" fill="none" stroke="currentColor" stroke-width="2" />
                        <line x1="8" y1="8" x2="16" y2="16" stroke="currentColor" stroke-width="2" />
                        <line x1="16" y1="8" x2="8" y2="16" stroke="currentColor" stroke-width="2" />
                    </svg>
                </div>
                
                <h3><?php esc_html_e('Erro ao Processar Compra', 'rifas'); ?></h3>
                
                <p id="rifas-erro-mensagem"></p>
                
                <button id="rifas-btn-tentar-novamente" class="rifas-btn rifas-btn-primary"><?php esc_html_e('Tentar Novamente', 'rifas'); ?></button>
            </div>
        </div>
    </div>
</div>