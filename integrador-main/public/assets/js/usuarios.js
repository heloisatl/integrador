document.addEventListener('DOMContentLoaded', () => {
    const estiloSenha = document.createElement('style');
    estiloSenha.textContent = `
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear,
        input[type="text"]::-ms-reveal,
        input[type="text"]::-ms-clear,
        input[type="password"]::-webkit-credentials-auto-fill-button {
            display: none;
        }
    `;
    document.head.appendChild(estiloSenha);

    document.querySelectorAll('[data-json-form]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            try {
                const resposta = await fetch(form.action, {
                    method: form.method || 'POST',
                    headers: { Accept: 'application/json' },
                    body: new FormData(form),
                });
                const dados = await resposta.json();

                if (dados.status === 'sucesso' && form.dataset.closeOnSuccess === 'true') {
                    const mensagem = form.closest('[role="dialog"]')?.querySelector('[data-success-message]');
                    if (mensagem) {
                        mensagem.textContent = dados.mensagem || 'Alterações concluídas com sucesso.';
                        mensagem.hidden = false;
                    }

                    setTimeout(() => {
                        if (dados.redirect) {
                            window.location.href = dados.redirect;
                        }
                    }, 1200);
                    return;
                }

                if (dados.status === 'sucesso' && dados.redirect) {
                    window.location.href = dados.redirect;
                    return;
                }

                alert(dados.mensagem || 'Não foi possível concluir a operação.');
            } catch (erro) {
                alert('Não foi possível conectar ao servidor. Tente novamente.');
            }
        });
    });

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            const icon = button.querySelector('i');
            const visivel = input.type === 'text';

            input.type = visivel ? 'password' : 'text';
            button.setAttribute('aria-label', visivel ? 'Mostrar senha' : 'Ocultar senha');
            icon.className = visivel ? 'bi bi-eye' : 'bi bi-eye-slash';
        });
    });

    document.querySelectorAll('[data-password-strength]').forEach((input) => {
        const indicador = document.getElementById(input.dataset.passwordStrength);

        const atualizarForca = () => {
            const senha = input.value;
            let forca = '';
            let cor = '';

            if (senha.length > 0 && senha.length < 8) {
                forca = 'Fraca';
                cor = '#f04438';
            } else if (senha.length >= 8) {
                const criterios = [
                    /[a-z]/.test(senha),
                    /[A-Z]/.test(senha),
                    /[0-9]/.test(senha),
                    /[^A-Za-z0-9]/.test(senha),
                ].filter(Boolean).length;

                forca = criterios >= 3 ? 'Forte' : 'Média';
                cor = criterios >= 3 ? '#12b76a' : '#f79009';
            }

            indicador.textContent = forca ? `Força: ${forca}` : '';
            indicador.style.color = cor;
        };

        input.addEventListener('input', atualizarForca);
        atualizarForca();
    });

    document.querySelectorAll('[data-password-confirmation-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const senha = form.querySelector('[data-password-value]').value;
            const confirmacao = form.querySelector('[data-password-confirmation]').value;

            if (senha !== confirmacao) {
                event.preventDefault();
                alert('As senhas não conferem.');
            }
        });
    });
});