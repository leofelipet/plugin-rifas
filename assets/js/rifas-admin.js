/* assets/js/rifas-admin.js */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Inicializar color picker
        $('.color-picker').wpColorPicker({
            change: function(event, ui) {
                // Atualizar preview de cor
                const $preview = $(this).closest('td').find('.color-preview');
                $preview.css('background-color', ui.color.toString());
            }
        });
        
        // Botão de detalhes da compra
        $('.rifas-detalhes-button').on('click', function(e) {
            e.preventDefault();
            
            const compraId = $(this).data('id');
            const nome = $(this).data('nome');
            const email = $(this).data('email');
            const valor = $(this).data('valor');
            const numeros = $(this).data('numeros');
            const data = $(this).data('data');
            
            // Preencher modal
            $('#rifas-modal-title').text('Detalhes da Compra #' + compraId);
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
        
        // Confirmar reset de configurações
        $('#rifas-reset-config').on('click', function(e) {
            if (!confirm('Tem certeza que deseja restaurar as configurações padrão? Esta ação não pode ser desfeita.')) {
                e.preventDefault();
            }
        });
    });
    
})(jQuery);