/* assets/js/rifas.js */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Elementos principais
        const $app = $('#rifas-app');
        if (!$app.length) return;
        
        const $etapa1 = $('#rifas-etapa-1');
        const $etapa2 = $('#rifas-etapa-2');
        const $etapa3 = $('#rifas-etapa-3');
        
        // Formulário etapa 1
        const $nome = $('#rifas-nome');
        const $email = $('#rifas-email');
        const $valor = $('#rifas-valor');
        const $btnContinuar = $('#rifas-btn-continuar');
        const $infoNumeros = $('#rifas-info-numeros');
        
        // Seleção de números etapa 2
        const $gridNumeros = $('#rifas-grid-numeros');
        const $maxNumeros = $('#rifas-max-numeros');
        const $numerosSelecionados = $('#rifas-numeros-selecionados');
        const $btnVoltar = $('#rifas-btn-voltar');
        const $btnFinalizar = $('#rifas-btn-finalizar');
        const $btnFiltro = $('.rifas-btn-filtro');
        const $carregarMais = $('#rifas-carregar-mais');
        
        // Confirmação etapa 3
        const $confirmacaoSucesso = $('.rifas-confirmacao-sucesso');
        const $confirmacaoErro = $('.rifas-confirmacao-erro');
        const $confirmLoading = $('.rifas-confirmacao-header .rifas-loading');
        const $confirmNome = $('#rifas-confirm-nome');
        const $confirmEmail = $('#rifas-confirm-email');
        const $confirmValor = $('#rifas-confirm-valor');
        const $confirmNumeros = $('#rifas-confirm-numeros');
        const $erroMensagem = $('#rifas-erro-mensagem');
        const $btnNovaCompra = $('#rifas-btn-nova-compra');
        const $btnTentarNovamente = $('#rifas-btn-tentar-novamente');
        
        // Variáveis de estado
        let valorPorNumero = parseFloat($valor.attr('min')) || 100;
        let numerosDisponiveis = [];
        let numerosSelecionadosArray = [];
        let maxNumerosPermitidos = 0;
        let filtroAtual = 'disponiveis';
        let paginaAtual = 1;
        let totalPaginas = 1;
        let numerosPorPagina = 100; // Quantidade de números por página
        let carregando = false;
        
        // Atualizar informações sobre quantidade de números
        $valor.on('input', function() {
            const valor = parseFloat($(this).val()) || 0;
            maxNumerosPermitidos = Math.floor(valor / valorPorNumero);
            
            if (maxNumerosPermitidos > 0) {
                $infoNumeros.html(`Com R$ ${valor.toFixed(2).replace('.', ',')} você tem direito a <strong>${maxNumerosPermitidos}</strong> ${maxNumerosPermitidos === 1 ? 'número' : 'números'}.`);
            } else {
                $infoNumeros.html('');
            }
        });
        
        // Botão continuar para etapa 2
        $btnContinuar.on('click', function() {
            // Validações
            if (!$nome.val().trim()) {
                alert('Por favor, informe seu nome.');
                $nome.focus();
                return;
            }
            
            if (!$email.val().trim() || !isValidEmail($email.val())) {
                alert('Por favor, informe um e-mail válido.');
                $email.focus();
                return;
            }
            
            const valor = parseFloat($valor.val()) || 0;
            if (valor < valorPorNumero) {
                alert(`O valor mínimo para compra é R$ ${valorPorNumero.toFixed(2).replace('.', ',')}`);
                $valor.focus();
                return;
            }
            
            // Atualizar valores na etapa 2
            maxNumerosPermitidos = Math.floor(valor / valorPorNumero);
            $maxNumeros.text(maxNumerosPermitidos);
            
            // Resetar para a primeira página
            paginaAtual = 1;
            
            // Carregar números disponíveis
            carregarNumeros(true);
            
            // Mudar para etapa 2
            $etapa1.hide();
            $etapa2.show();
            
            // Registrar evento de visualização de etapa 2 (opcional)
            if (typeof gtag === 'function') {
                gtag('event', 'visualizar_selecao_numeros', {
                    'event_category': 'rifas',
                    'event_label': 'Etapa 2 - Seleção de Números'
                });
            }
        });
        
        // Botão voltar para etapa 1
        $btnVoltar.on('click', function() {
            $etapa2.hide();
            $etapa1.show();
            numerosSelecionadosArray = [];
            $numerosSelecionados.text('0');
            $btnFinalizar.prop('disabled', true);
        });
        
        // Botão carregar mais números
        if ($carregarMais.length) {
            $carregarMais.on('click', function() {
                if (paginaAtual < totalPaginas && !carregando) {
                    paginaAtual++;
                    carregarNumeros(false);
                }
            });
        }
        
        // Botão finalizar compra
        $btnFinalizar.on('click', function() {
            if (numerosSelecionadosArray.length === 0) {
                alert('Por favor, selecione pelo menos um número para continuar.');
                return;
            }
            
            if (numerosSelecionadosArray.length > maxNumerosPermitidos) {
                alert(`Você só pode selecionar até ${maxNumerosPermitidos} números.`);
                return;
            }
            
            // Mostrar etapa de confirmação
            $etapa2.hide();
            $etapa3.show();
            $confirmacaoSucesso.hide();
            $confirmacaoErro.hide();
            $confirmLoading.show();
            
            // Enviar dados para o backend
            $.ajax({
                url: rifas_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'comprar_rifas',
                    nonce: rifas_ajax.nonce,
                    nome: $nome.val(),
                    email: $email.val(),
                    valor: parseFloat($valor.val()) || 0,
                    numeros: numerosSelecionadosArray
                },
                success: function(response) {
                    $confirmLoading.hide();
                    
                    if (response.success) {
                        // Mostrar sucesso
                        $confirmacaoSucesso.show();
                        
                        // Preencher detalhes
                        $confirmNome.text($nome.val());
                        $confirmEmail.text($email.val());
                        $confirmValor.text(parseFloat($valor.val()).toFixed(2).replace('.', ','));
                        $confirmNumeros.text(numerosSelecionadosArray.join(', '));
                        
                        // Registrar evento de compra concluída (opcional)
                        if (typeof gtag === 'function') {
                            gtag('event', 'compra_concluida', {
                                'event_category': 'rifas',
                                'event_label': 'Compra Concluída',
                                'value': parseFloat($valor.val()) || 0
                            });
                        }
                    } else {
                        // Mostrar erro
                        $confirmacaoErro.show();
                        $erroMensagem.text(response.data.message || 'Ocorreu um erro ao processar sua compra.');
                        
                        // Se houver números indisponíveis, atualizar lista
                        if (response.data.numeros_indisponiveis) {
                            paginaAtual = 1;
                            carregarNumeros(true);
                        }
                    }
                },
                error: function() {
                    $confirmLoading.hide();
                    $confirmacaoErro.show();
                    $erroMensagem.text('Erro de conexão. Por favor, tente novamente.');
                }
            });
        });
        
        // Botão nova compra
        $btnNovaCompra.on('click', function() {
            resetarFormulario();
            $etapa3.hide();
            $etapa1.show();
        });
        
        // Botão tentar novamente
        $btnTentarNovamente.on('click', function() {
            $etapa3.hide();
            $etapa2.show();
        });
        
        // Filtro de números
        $btnFiltro.on('click', function() {
            const $this = $(this);
            const filtro = $this.data('filtro');
            
            if (filtro === filtroAtual) return;
            
            $btnFiltro.removeClass('active');
            $this.addClass('active');
            
            filtroAtual = filtro;
            paginaAtual = 1;
            carregarNumeros(true);
        });
        
        // Scroll infinito para carregar mais números (opcional)
        $gridNumeros.on('scroll', function() {
            const scrollHeight = $(this).prop('scrollHeight');
            const scrollTop = $(this).scrollTop();
            const offsetHeight = $(this).height();
            
            // Se chegou próximo ao fim, carrega mais
            if ((scrollTop + offsetHeight + 50 >= scrollHeight) && paginaAtual < totalPaginas && !carregando) {
                paginaAtual++;
                carregarNumeros(false);
            }
        });
        
        // Carregar números
        function carregarNumeros(limpar = true) {
            if (carregando) return;
            
            carregando = true;
            
            if (limpar) {
                $gridNumeros.html('<div class="rifas-loading">Carregando números...</div>');
            } else {
                $gridNumeros.append('<div class="rifas-loading" id="rifas-loading-mais">Carregando mais números...</div>');
            }
            
            if ($carregarMais.length) {
                $carregarMais.prop('disabled', true).text('Carregando...');
            }
            
            $.ajax({
                url: rifas_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_numeros_rifas',
                    nonce: rifas_ajax.nonce,
                    mostrar: filtroAtual,
                    pagina: paginaAtual,
                    por_pagina: numerosPorPagina
                },
                success: function(response) {
                    carregando = false;
                    
                    if (response.success) {
                        if (limpar) {
                            $gridNumeros.empty();
                        } else {
                            $('#rifas-loading-mais').remove();
                        }
                        
                        renderizarNumeros(response.data.numeros, limpar);
                        
                        // Atualizar informações de paginação
                        totalPaginas = response.data.total_paginas || 1;
                        
                        if ($carregarMais.length) {
                            $carregarMais.prop('disabled', paginaAtual >= totalPaginas)
                                         .text(paginaAtual >= totalPaginas ? 'Não há mais números' : 'Carregar mais números');
                            
                            // Mostrar ou esconder o botão conforme necessário
                            $carregarMais.toggle(paginaAtual < totalPaginas);
                        }
                    } else {
                        if (limpar) {
                            $gridNumeros.html('<p>Erro ao carregar números.</p>');
                        } else {
                            $('#rifas-loading-mais').remove();
                        }
                        
                        if ($carregarMais.length) {
                            $carregarMais.prop('disabled', false).text('Tentar novamente');
                        }
                    }
                },
                error: function() {
                    carregando = false;
                    
                    if (limpar) {
                        $gridNumeros.html('<p>Erro de conexão. Por favor, tente novamente.</p>');
                    } else {
                        $('#rifas-loading-mais').remove();
                    }
                    
                    if ($carregarMais.length) {
                        $carregarMais.prop('disabled', false).text('Tentar novamente');
                    }
                }
            });
        }
        
        // Renderizar números
        function renderizarNumeros(numeros, limpar = true) {
            if (numeros.length === 0) {
                if (limpar) {
                    $gridNumeros.html('<p>Não há números disponíveis.</p>');
                }
                return;
            }
            
            // Reconsiderar os números selecionados
            if (limpar) {
                numerosSelecionadosArray = [];
            }
            
            const fragment = document.createDocumentFragment();
            
            numeros.forEach(function(item) {
                const numero = parseInt(item.numero);
                const status = item.status;
                const isSelecionado = numerosSelecionadosArray.includes(numero);
                let classes = 'rifas-numero';
                
                // Adicionar classe de status
                if (status === 'disponivel') {
                    classes += ' disponivel';
                    if (isSelecionado) {
                        classes += ' selecionado';
                        if (!numerosSelecionadosArray.includes(numero)) {
                            numerosSelecionadosArray.push(numero);
                        }
                    }
                } else {
                    classes += ' vendido';
                }
                
                const $numeroElement = $('<div>', {
                    class: classes,
                    'data-numero': numero,
                    text: numero
                });
                
                // Adicionar evento de clique para números disponíveis
                if (status === 'disponivel') {
                    $numeroElement.on('click', function() {
                        toggleNumero($(this), numero);
                    });
                }
                
                fragment.appendChild($numeroElement[0]);
            });
            
            if (limpar) {
                $gridNumeros.empty();
            }
            
            $gridNumeros.append(fragment);
            
            // Atualizar contador
            numerosSelecionadosArray.sort((a, b) => a - b);
            $numerosSelecionados.text(numerosSelecionadosArray.length);
            
            // Habilitar/desabilitar botão de finalizar
            $btnFinalizar.prop('disabled', numerosSelecionadosArray.length === 0);
        }
        
        // Toggle de seleção de número
        function toggleNumero($elemento, numero) {
            const isSelecionado = $elemento.hasClass('selecionado');
            
            if (isSelecionado) {
                // Remover seleção
                $elemento.removeClass('selecionado');
                numerosSelecionadosArray = numerosSelecionadosArray.filter(n => n !== numero);
            } else {
                // Verificar se já atingiu o máximo de números
                if (numerosSelecionadosArray.length >= maxNumerosPermitidos) {
                    alert(`Você só pode selecionar até ${maxNumerosPermitidos} números.`);
                    return;
                }
                
                // Adicionar seleção
                $elemento.addClass('selecionado');
                numerosSelecionadosArray.push(numero);
                numerosSelecionadosArray.sort((a, b) => a - b);
            }
            
            // Atualizar contador
            $numerosSelecionados.text(numerosSelecionadosArray.length);
            
            // Habilitar/desabilitar botão de finalizar
            $btnFinalizar.prop('disabled', numerosSelecionadosArray.length === 0);
        }
        
        // Resetar formulário
        function resetarFormulario() {
            $nome.val('');
            $email.val('');
            $valor.val('');
            $infoNumeros.html('');
            numerosSelecionadosArray = [];
            $numerosSelecionados.text('0');
            $btnFinalizar.prop('disabled', true);
            paginaAtual = 1;
            
            // Restaurar filtro padrão
            $btnFiltro.removeClass('active');
            $btnFiltro.filter('[data-filtro="disponiveis"]').addClass('active');
            filtroAtual = 'disponiveis';
        }
        
        // Validação de email
        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
        
        // Inicializar tooltips (opcional)
        if ($.fn.tooltip) {
            $('.rifas-tooltip').tooltip();
        }
        
        // Detecção de conexão offline
        window.addEventListener('online', function() {
            $('.rifas-offline-message').remove();
        });
        
        window.addEventListener('offline', function() {
            $app.prepend('<div class="rifas-offline-message">Você está offline. Algumas funcionalidades podem não funcionar corretamente.</div>');
        });
    });
    
})(jQuery);