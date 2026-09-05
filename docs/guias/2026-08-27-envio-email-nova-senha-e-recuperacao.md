# Guia Técnico de Arquitetura e Implementação - Sistema de Envio de E-mail & Recuperação de Senha
**Data da Alteração**: `2026-08-27`  
**Título do Guia**: `2026-08-27-envio-email-nova-senha-e-recuperacao.md`  
**Módulo**: Ferramentas de Comunicação / Autenticação e Segurança  
**Público-Alvo**: Desenvolvedores de Software, Arquitetos de Soluções e Mantenedores do Código  

---

## 1. Contexto e Motivação da Implementação

Anteriormente, o sistema de autenticação contava com o fluxo básico de login e rotas de recuperação de senha, porém sem integração real para o envio de e-mails contendo as senhas redefinidas. O corpo do e-mail utilizava strings estáticas de HTML simples diretamente no código PHP e o envio não possuía proteções contra disparos repetidos ou múltiplos cliques simultâneos no formulário.

A implementação atual resolveu essas limitações através de:
- **E-mail HTML Profissional e Responsivo**: Criação de um template estilizado com a identidade visual do DevStudio (`mailBody.php` e `mailCss.css`), compatível com computadores e dispositivos móveis (smartphones/tablets).
- **Integração Robusta de E-mail (`EmailService.php`)**: Abstração do PHPMailer via servidor SMTP (Gmail) utilizando autenticação por Senha de Aplicativo e carregamento dinâmico via constante de sistema `ROOT_PATH`.
- **Fluxo Integrado de Recuperação com Banco de Dados**: Consulta de existência do usuário por e-mail, geração de senha temporária alfanumérica e atualização segura usando hash `password_hash()`.
- **Proteção Contra Múltiplas Requisições e Spam**: Trava no Front-End contra cliques duplos (desabilitação do botão com spinner animado) e Rate Limiting no Back-End (cooldown de 60 segundos por e-mail/sessão).
- **Usabilidade na Autenticação**: Adição da funcionalidade de alternância de visibilidade da senha (*mostrar/ocultar senha*) com ícone de olho (`bi-eye` / `bi-eye-slash`) nas telas de `login.php` e `cadastro.php`.

---

## 2. Visão Geral da Arquitetura do Módulo

O módulo de envio de e-mails e recuperação de senha é composto por 4 pilares:

1. **Configuração Global e Mapeamento de Arquivos (`app/config/Config.php`)**:
   - Definição da constante `ROOT_PATH` (`str_replace('\\', '/', dirname(__DIR__, 2))`), garantindo resolução de caminhos absolutos no sistema de arquivos para inclusão das bibliotecas do PHPMailer.

2. **Módulo Especialista de E-mail (`app/tools/mail/`)**:
   - `EmailService.php`: Serviço responsável por instanciar e configurar o PHPMailer via SMTP, definir cabeçalhos e disparar os e-mails.
   - `mailBody.php`: Gerador do template HTML5 universal do e-mail.
   - `mailCss.css`: Folha de estilos responsiva com regras anti-inversão de cor para leitores móveis e layout limpo.
   - `lib/PHPMailer/src/`: Biblioteca do PHPMailer para manipulação do protocolo SMTP e codificação MIME.

3. **Camada de Controle e Segurança (`app/controllers/AutenticacaoController.php`)**:
   - Validação de entrada de dados e existência da conta via `UsuarioService`.
   - Regra de Rate Limiting (60s) via `$_SESSION['last_reset_request']`.
   - Geração da senha temporária e atualização criptografada em banco.
   - Invocação do `EmailService` e gerenciamento das mensagens de feedback (`flash_sucesso` e `flash_erro`).

4. **Camada de Apresentação (`app/views/autenticacao/`)**:
   - `recuperar_senha.php`: Formulário de solicitação com bloqueio em JavaScript e spinner.
   - `login.php` e `cadastro.php`: Formulários com botão de visibilidade da senha (`password-wrapper`).

---

## 3. Detalhamento Técnico dos Componentes

### 3.1 `Config.php` e a Constante `ROOT_PATH`
Para evitar dependência de caminhos relativos instáveis (`../../library/...`), o arquivo `Config.php` define a constante global:
```php
define('ROOT_PATH', str_replace('\\', '/', dirname(__DIR__, 2)));
```
Essa constante permite que o `EmailService.php` faça a inclusão exata das bibliotecas do PHPMailer em qualquer ambiente de hospedagem:
```php
require_once __DIR__ . "/../../config/Config.php";
require_once ROOT_PATH . "/app/tools/mail/lib/PHPMailer/src/PHPMailer.php";
require_once ROOT_PATH . "/app/tools/mail/lib/PHPMailer/src/SMTP.php";
require_once ROOT_PATH . "/app/tools/mail/lib/PHPMailer/src/Exception.php";
```

### 3.2 `EmailService.php` (Serviço SMTP)
Configurado para utilizar o servidor SMTP do Gmail na porta `587` com criptografia `STARTTLS`:
- **Credenciais**: Autenticação via Senha de Aplicativo de 16 caracteres (`creatormvc@gmail.com`).
- **Codificação**: Configuração de charset para `UTF-8` e tipo da mensagem para HTML (`isHTML(true)`).
- **Corpo Alternativo**: Definição automática de `AltBody` em texto puro para leitores legados sem suporte a HTML.

### 3.3 `mailBody.php` & `mailCss.css` (Template HTML & Estilização)
- **Estrutura de Tabelas Clássicas (`<table>` e `<td>`)**: Utilização de `bgcolor="#ffffff"` e `bgcolor="#f4f6fb"` para garantir que aplicativos móveis (Gmail App iOS/Android, Apple Mail) renderizem as tabelas com o máximo de fidelidade e contraste.
- **Identificador Único (`Ref-ID`) Contra Ocultação por `[...]` no Gmail**: Injeção de uma `<div>` oculta com hash único (`md5(microtime(true) . rand())`) no rodapé do e-mail. Isso impede que o Gmail agrupe testes sucessivos na mesma conversa e oculte o rodapé sob o botão de três pontinhos `[...]`.
- **Responsividade Móvel**: Folha de estilos `mailCss.css` com media queries `@media only screen and (max-width: 600px)` transformando o botão CTA em largura total (`width: 100% !important;`) e ajustando paddings em telas pequenas.

### 3.4 Segurança Contra Cliques Duplos e Rate Limiting
Para evitar disparos múltiplos e geração repetida de senhas:
- **Front-End (`recuperar_senha.php`)**: Script JS escuta o envio do formulário, desabilita o botão (`disabled = true`) e altera seu estado para `"Enviando e-mail..."` com um spinner animado CSS.
- **Back-End (`AutenticacaoController.php`)**: Trava de cooldown de 60 segundos por e-mail:
  ```php
  $tempoCooldown = 60;
  if (isset($_SESSION['last_reset_request'][$email])) {
      $tempoDecorrido = time() - $_SESSION['last_reset_request'][$email];
      if ($tempoDecorrido < $tempoCooldown) {
          $restante = $tempoCooldown - $tempoDecorrido;
          $_SESSION['flash_erro'] = "Aguarde {$restante} segundo(s) antes de solicitar uma nova senha.";
          $this->redirect(URL_BASE . '/recuperar-senha');
      }
  }
  ```

### 3.5 Alternador de Visibilidade da Senha (`login.php` e `cadastro.php`)
Adição de um botão com ícone de olho (Bootstrap Icons) encapsulado dentro do wrapper `.password-wrapper`:
```html
<div class="password-wrapper">
    <input type="password" id="senha" name="senha" required>
    <button type="button" id="toggleSenha" class="btn-toggle-password" title="Mostrar/ocultar senha">
        <i class="bi bi-eye" id="iconeOlho"></i>
    </button>
</div>
```
O JavaScript alterna dinamicamente o atributo `type` entre `password` e `text` e a classe do ícone entre `bi-eye` e `bi-eye-slash`.

---

## 4. Diagrama do Fluxo de Execução End-to-End da Recuperação

```
[ Usuário acessa /recuperar-senha ]
             │
             │ 1. Preenche e-mail e clica "Enviar instruções"
             ▼
[ JS Front-End desabilita botão + exibe Spinner ]
             │
             │ 2. Requisição POST enviada para /recuperar-senha
             ▼
[ AutenticacaoController::solicitarRecuperacao() ]
             │
             ├──► Validar formato do e-mail
             ├──► Verificação de Cooldown (60s em SESSION) ──(Se < 60s)──► [ Redireciona com Alerta ]
             ├──► Consulta Banco: UsuarioService::getUsuarioPorEmail($email) ──(Se não existe)──► [ Alerta ]
             │
             ├──► 3. Gera nova senha temporária alfanumérica (10 chars)
             ├──► 4. Atualiza banco: UsuarioService::updateSenhaPorEmail($email, $novaSenha) [password_hash]
             ├──► 5. Instancia EmailService::enviarNovaSenha($email, $novaSenha)
             │             │
             │             ├──► Carrega mailBody.php e mailCss.css com Ref-ID único
             │             └──► Conecta no SMTP do Gmail (smtp.gmail.com:587)
             │
             ▼
[ E-mail enviado com sucesso ] ──► [ Redireciona para /login com mensagem de sucesso ]
```
