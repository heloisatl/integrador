<?php

/**
 * Função para gerar o template HTML do e-mail de nova senha com a identidade visual do DevStudio.
 * 
 * @param string $novaSenha A nova senha gerada para o usuário.
 * @param string $emailDestino O e-mail do destinatário (opcional).
 * @param string $loginUrl URL para redirecionamento ao login (opcional).
 * @return string HTML completo do e-mail.
 */
function renderizarEmail($novaSenha, $emailDestino = '', $loginUrl = '') {
    $cssPath = __DIR__ . '/mailCss.css';
    $cssContent = file_exists($cssPath) ? file_get_contents($cssPath) : '';

    if (empty($loginUrl)) {
        if (defined('URL_BASE')) {
            $loginUrl = URL_BASE . '/login';
        } else {
            $loginUrl = 'http://localhost/integrador/public/login';
        }
    }

    $anoAtual = date('Y');
    $novaSenhaEscaped = htmlspecialchars($novaSenha, ENT_QUOTES, 'UTF-8');
    
    // Hash único de microtempo para evitar que o Gmail agrupe/corte o e-mail em [...] no mesmo histórico
    $uniqueRef = md5(microtime(true) . rand(1000, 9999));

    return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sua Nova Senha - DevStudio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@500;700&family=DM+Sans:wght@400;500;600;700&family=Syne:wght@700&display=swap" rel="stylesheet">
    <style>
        {$cssContent}
    </style>
</head>
<body class="body" bgcolor="#f4f6fb" style="margin: 0; padding: 0; width: 100% !important; background-color: #f4f6fb; color: #0f172a; font-family: 'DM Sans', Arial, Helvetica, sans-serif;">
    
    <!-- Tabela Principal Externa -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#f4f6fb" style="background-color: #f4f6fb; width: 100%;">
        <tr>
            <td align="center" bgcolor="#f4f6fb" style="padding: 40px 12px; background-color: #f4f6fb;">
                
                <!-- Container Card do E-mail -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff" class="email-container" style="max-width: 580px; width: 100%; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);">
                    
                    <!-- Cabeçalho / Logo -->
                    <tr>
                        <td bgcolor="#f8fafc" class="email-header" style="background-color: #f8fafc; padding: 24px 32px; border-bottom: 1px solid #e2e8f0; text-align: left;">
                            <span class="brand-title" style="font-family: 'Syne', Arial, sans-serif; font-size: 20px; font-weight: 700; color: #0f172a;">Dev<span class="brand-accent" style="color: #5b6af0;">Studio</span></span>
                            <span class="badge-tag" style="display: inline-block; background-color: #e2e8f0; color: #475569; font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 4px; margin-left: 8px; border: 1px solid #cbd5e1;">Segurança</span>
                        </td>
                    </tr>
                    
                    <!-- Corpo do E-mail -->
                    <tr>
                        <td bgcolor="#ffffff" class="email-body" style="padding: 32px; background-color: #ffffff;">
                            <h1 class="email-title" style="font-family: 'Syne', Arial, sans-serif; font-size: 22px; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 16px;">Recuperação de Senha</h1>
                            <p class="email-text" style="font-size: 15px; color: #334155; margin-bottom: 24px; line-height: 1.6;">
                                Olá! Recebemos uma solicitação de redefinição de senha para a sua conta no <strong style="color: #0f172a;">DevStudio</strong>.
                                Sua nova senha temporária foi gerada com sucesso:
                            </p>
                            
                            <!-- Card da Senha -->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#f8fafc" class="password-card" style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; margin: 24px 0;">
                                <tr>
                                    <td align="center" style="padding: 20px; background-color: #f8fafc;">
                                        <div class="password-label" style="font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 8px; font-weight: 600;">Sua Nova Senha</div>
                                        <div class="password-code" style="font-family: 'DM Mono', Consolas, Monaco, monospace; font-size: 24px; font-weight: 700; color: #5b6af0; letter-spacing: 2px; margin: 0; word-break: break-all;">{$novaSenhaEscaped}</div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Botão CTA -->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" class="btn-container" style="margin: 32px 0 24px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{$loginUrl}" class="btn-primary" target="_blank" style="display: inline-block; background-color: #5b6af0; color: #ffffff !important; font-weight: 600; font-size: 14px; padding: 12px 28px; border-radius: 8px; text-decoration: none;">Acessar o DevStudio</a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Caixa de Aviso de Segurança -->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#f8fafc" class="security-warning" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-left: 3px solid #5b6af0; border-radius: 8px; margin-top: 24px;">
                                <tr>
                                    <td style="padding: 14px 16px; font-size: 13px; color: #475569; line-height: 1.5; background-color: #f8fafc;">
                                        <strong style="color: #0f172a;">Dica de Segurança:</strong> Recomendamos que você faça login e altere esta senha temporária nas configurações do seu perfil. Caso não tenha solicitado esta alteração, por favor ignore este e-mail.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Rodapé -->
                    <tr>
                        <td bgcolor="#f8fafc" class="email-footer" style="background-color: #f8fafc; padding: 24px 32px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 12px; color: #64748b;">
                            <p style="margin: 0 0 6px 0; color: #64748b;">&copy; {$anoAtual} DevStudio. Todos os direitos reservados.</p>
                            <p style="margin: 0; color: #64748b;">Este é um e-mail automático. Por favor, não responda a esta mensagem.</p>
                        </td>
                    </tr>
                </table>
                
            </td>
        </tr>
    </table>
    
    <!-- Referencia única para impedir ocultação por [...] no Gmail -->
    <div style="display: none; max-height: 0; overflow: hidden; font-size: 1px; line-height: 1px; color: #f4f6fb; opacity: 0;">
        Ref-ID: {$uniqueRef}
    </div>
</body>
</html>
HTML;
}
