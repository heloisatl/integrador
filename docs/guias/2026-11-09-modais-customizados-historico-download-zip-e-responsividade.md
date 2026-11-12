# Guia Técnico de Arquitetura e Implementação - Modais Customizados, Correção de Download ZIP do Histórico e Responsividade Universal

**Data da Alteração**: `2026-11-09`  
**Título do Guia**: `2026-11-09-modais-customizados-historico-download-zip-e-responsividade.md`  
**Módulos**: Histórico de Projetos / Gestão de Usuários / Perfil de Usuário / Interface Global & Responsividade  
**Público-Alvo**: Desenvolvedores de Software, Designers de Interface (UI/UX), Engenheiros de QA e Mantenedores do DevStudio  

---

## 1. Contexto e Motivação

Este guia formaliza a implementação de melhorias na experiência de uso (UX), conformidade de acessibilidade e robustez técnica no ecossistema **DevStudio**:

1. **Eliminação de Diálogos Nativos (`confirm()`)**:
   - Substituição das janelas bloqueantes do navegador por modais customizados no padrão visual DevStudio, com suporte a *backdrop blur*, foco acessível, fechamento via tecla <kbd>Esc</kbd> e clique externo.
2. **Correção do Download de Projetos Antigos no Histórico**:
   - Resolução da falha no endpoint `/projetos/historico/baixar`, que serializava metadados como arquivo `.json` em vez de fornecer o `.zip` do projeto gerado pelo MVC Creator.
   - Criação de mecanismo de reconstrução e cache automático sob demanda para projetos cujos arquivos compactados não estejam mais presentes no disco do servidor.
3. **Visibilidade de Senhas no Perfil**:
   - Inclusão de botões ergonômicos de alternância de visibilidade ("olhinho") nos campos de senha da tela de edição de perfil, reutilizando a convenção estabelecida na tela de autenticação.
4. **Padronização de Navegação e Alinhamento**:
   - Substituição do rótulo genérico "Perfil" pelo nome real do usuário conectado na barra superior.
   - Padronização do botão de encerramento de sessão com ícone contextual de saída (`bi-door-open`).
   - Uniformização de largura de formulários e saneamento de ícones na interface do Guia Rápido.
5. **Responsividade Universal e Experiência Mobile**:
   - Otimização ergonômica global no `public/assets/css/responsivo.css` com alvo de toque mínimo de 44px, prevenção de auto-zoom no iOS Safari, suporte a safe-area insets e ordenação prioritária de botões de ação para fácil alcance com o polegar.

---

## 2. Modais Customizados no Padrão DevStudio

### 2.1. Modal de Exclusão de Usuário (`usuario_list.php`)
Implementado para substituir a confirmação padrão do navegador ao excluir um usuário na listagem administrativa:
- **Card de Resumo**: Exibe avatar estilizado, nome completo, e-mail, ID e badge de perfil (`ADMIN` ou `USUARIO`).
- **Aviso de Perigo**: Bloco com borda avermelhada alertando que a exclusão é definitiva e irreversível.
- **Regra de Auto-Exclusão**: Se o administrador tentar excluir a sua própria conta conectada, o modal bloqueia a ação, oculta o aviso de exclusão, desabilita o botão de confirmação e exibe um alerta de salvaguarda com ícone `bi-shield-lock-fill`.

### 2.2. Modal de Exclusão Individual do Histórico (`historico.php`)
Acionado ao clicar no botão "Excluir do Histórico" em qualquer card de projeto:
- **Resumo do Projeto**: Exibe ícone contextual de acordo com o módulo (`bi-braces` para MVC Creator e `bi-file-earmark-code` para PageMaker), nome do projeto, identificador de arquivo/banco de dados e carimbo de data/hora de criação.
- **Aviso Irreversível**: Confirmação visualmente destacada informando a perda permanente do registro.
- **Ações**: Botões "Cancelar" (`btn-secondary`) e "Sim, Excluir do Histórico" (`btn-danger`).

### 2.3. Modal de Limpeza Total do Histórico com Concordância Inteligente
Acionado pelo botão "Limpar Histórico" na barra de ferramentas superior:
- **Tratamento de Singular e Plural**:
  - **Quando houver 1 projeto**:
    - Nome do card: `1 projeto registrado`.
    - Subtítulo: *"O projeto salvo no histórico será removido."*
    - Alerta: *"Atenção Irreversível: Tem certeza que deseja limpar permanentemente todo o histórico? **O único projeto salvo será perdido e não poderá ser recuperado.**"*
  - **Quando houver 2 ou mais projetos**:
    - Nome do card: `X projetos registrados`.
    - Subtítulo: *"Todos os projetos salvos no histórico serão removidos."*
    - Alerta: *"Atenção Irreversível: Tem certeza que deseja limpar permanentemente todo o histórico? **Todos os X projetos salvos serão perdidos e não poderão ser recuperados.**"*

---

## 3. Correção do Fluxo de Download ZIP no Histórico

### 3.1. Diagnóstico da Falha
O método `baixarProjetoHistorico()` em `ProjetoController.php` possuía tratamento específico apenas para arquivos HTML e CSS do PageMaker. Qualquer projeto oriundo do módulo MVC Creator caía em um bloco residual que forçava a serialização do array `$payload` como arquivo `.json` (`header('Content-Type: application/json')`), baixando por exemplo `aasdawsdawsd.json` de 284 bytes em vez do `.zip` compilado do sistema.

### 3.2. Arquitetura da Solução Implementada

```
Requisição HTTP: GET /projetos/historico/baixar?id=proj_xxx
                        │
                        ▼
           ProjetoController::baixarProjetoHistorico()
                        │
       ┌────────────────┴────────────────┐
       ▼                                 ▼
É PageMaker (HTML/CSS)?          É MVC Creator / Projeto Completo?
       │                                 │
       ▼                                 ▼
Entrega texto HTML/CSS           Resolve nome do arquivo ZIP
                                         │
                                         ▼
                         localizarZipProjeto($arquivo, $nome)
                                         │
                   ┌─────────────────────┴─────────────────────┐
                   ▼                                           ▼
          Arquivo ZIP encontrado?                     Arquivo ausente no disco?
                   │                                           │
                   │                                           ▼
                   │                          reconstruirZipProjetoHistorico()
                   │                          ├── Consulta banco de dados e tabelas
                   │                          ├── Executa MotorGeracao ou Core MVC
                   │                          └── Salva .zip em app/projetos/downloads/
                   │                                           │
                   └─────────────────────┬─────────────────────┘
                                         ▼
                     GeradorZip::enviarDownload($caminhoZip)
                     (Content-Type: application/zip)
```

### 3.3. Mecanismo de Reconstrução Automática (`reconstruirZipProjetoHistorico`)
Caso o arquivo físico compactado tenha sido removido por limpezas de temporários ou migração entre servidores:
1. O controlador inspeciona se o banco de dados associado ao registro ainda existe localmente no MySQL (`SHOW DATABASES LIKE ?`).
2. Se o banco e suas tabelas existirem, compila o sistema completo através do `MotorGeracao`, gerando Models, Controllers, Repositories, Views e infraestrutura.
3. Se o banco não for acessível, monta a base arquitetural MVC limpa com:
   - `app/core/Autoload.php`
   - `app/core/Controller.php`
   - `app/core/Router.php`
   - `app/database/ConnectionFactory.php`
   - `app/config/config.php`
   - `public/index.php`
   - `README.md` detalhado com dados do projeto e guia de uso.
4. Compacta o pacote utilizando o utilitário nativo ou fallback do `GeradorZip`, salva o arquivo em `app/projetos/downloads/` para os próximos acessos e despacha o download do arquivo `.zip` imediatamente.

---

## 4. Visibilidade de Senhas no Perfil

Na view `app/views/usuarios/perfil.php`:
- Os campos **Senha atual**, **Nova senha** e **Confirmar nova senha** receberam botões interativos `.perfil-toggle-senha`.
- Mantido o espaçamento à direita (`padding-right: 44px`) nos inputs para evitar sobreposição entre o texto digitado e o ícone.
- Controle em JavaScript que alterna o atributo `type` entre `password` e `text`, e troca os glifos entre `bi-eye` e `bi-eye-slash`.

---

## 5. Padronização da Barra de Navegação e Formulários

- **Nome do Usuário**: O botão de menu de perfil (`.profile-btn-name`) passa a exibir dinamicamente o nome do usuário cadastrado na sessão (`$usuarioNavbarObj->getNome()`), substituindo a palavra estática "Perfil".
- **Ícone do Botão Sair**: Adicionado o ícone `bi-door-open` no dropdown desktop e `bi-box-arrow-right` no mobile, mantendo coerência visual com o restante do sistema.
- **Largura dos Formulários**: O container principal (`.main-content`) e formulários de cadastro e edição de usuários foram ajustados para ocupar a largura total disponível do painel, mantendo proporções fluidas sem bordas truncadas.
- **Símbolos do Guia Rápido**: Substituição de caracteres tipográficos soltos por ícones Bootstrap alinhados.

---

## 6. Arquitetura de Responsividade Universal (`responsivo.css`)

O arquivo `public/assets/css/responsivo.css` atua como a folha de estilo mestre de responsividade do DevStudio:

| Recurso Responsivo | Implementação Técnica | Benefício ao Usuário |
|---|---|---|
| **Touch Targets (44px)** | `min-height: 44px;` em botões, links, selects e inputs em telas touch e `<= 768px`. | Facilidade de clique com o polegar, sem toques acidentais em elementos adjacentes. |
| **Prevenção de Zoom iOS** | `font-size: 16px !important;` em todos os campos de formulário para mobile. | O navegador Safari móvel não aplica zoom indesejado ao focar nos campos. |
| **Safe Area Insets** | `env(safe-area-inset-bottom)` no rodapé e menu gaveta móvel. | Evita sobreposição com a barra inferior dos iPhones modernos. |
| **Ordenação de Ações** | `flex-direction: column-reverse;` em rodapés de modais e formulários. | Botão de ação primária fica posicionado mais perto do polegar. |
| **Scroll de Tabelas** | `overflow-x: auto;` e `-webkit-overflow-scrolling: touch;`. | Tabelas com muitas colunas deslizam suavemente sem quebrar o layout da página. |
| **Cards do Histórico** | Grade colapsa para 1 coluna com botões empilhados em telas `<= 600px`. | Evita botões espremidos ou sobrepostos em celulares. |

---

## 7. Mapeamento de Arquivos Modificados

| Arquivo | Status | Descrição da Modificação |
|---|---|---|
| `app/controllers/ProjetoController.php` | **Modificado** | Correção do download do histórico para entrega de `.zip`, busca multilocação e reconstrução automática sob demanda com cache em disco. |
| `app/views/projetos/historico.php` | **Modificado** | Modais customizados de exclusão individual e limpeza geral, tratamento de concordância gramatical (singular/plural) e responsividade dos cards e botões. |
| `app/views/usuarios/usuario_list.php` | **Modificado** | Modal customizado de exclusão de usuário com card resumo e bloqueio de auto-exclusão do admin logado. |
| `app/views/usuarios/perfil.php` | **Modificado** | Inclusão de botões para alternar visibilidade de senhas nos três campos. |
| `app/views/include/navigation.php` | **Modificado** | Exibição do nome do usuário conectado no botão da navbar e ícone de porta no botão de sair. |
| `app/views/projetos/guia.php` | **Modificado** | Padronização dos ícones e marcadores visuais das seções. |
| `public/assets/css/responsivo.css` | **Modificado** | Refinamentos universais de ergonomia móvel, touch targets, modais, formulários e tabelas. |
| `public/assets/css/usuarios.css` | **Modificado** | Estilos dos modais de usuário e regras de responsividade para formulários e tabelas. |
| `public/assets/css/logs.css` | **Modificado** | Responsividade para cabeçalho, filtros, KPIs e tabela de auditoria. |
| `public/assets/css/form-styles.css` | **Modificado** | Largura e alinhamento de formulários no padrão do sistema. |
| `docs/guias/2026-11-09-modais-customizados-historico-download-zip-e-responsividade.md` | **Criado** | Este documento técnico detalhado. |

---

## 8. Procedimentos de Teste e Validação

1. **Download de Projetos do Histórico**:
   - Acessar `http://localhost:8081/projetos/historico`.
   - Clicar em "Baixar Projeto" em qualquer item de MVC Creator.
   - Confirmar que o arquivo baixado é um `.zip` contendo os diretórios `app/`, `public/` e `README.md`, e não um arquivo `.json`.
2. **Modal de Limpeza com 1 Projeto**:
   - Manter apenas um projeto no histórico e clicar em "Limpar Histórico".
   - Confirmar a mensagem singular: *"1 projeto registrado"*, *"O projeto salvo no histórico será removido"* e *"O único projeto salvo será perdido..."*.
3. **Modal de Exclusão de Usuários**:
   - Acessar `/usuarios` com perfil admin.
   - Clicar em "Excluir" no próprio usuário logado e verificar o bloqueio de segurança.
   - Clicar em "Excluir" em outro usuário e verificar a abertura do modal com os dados corretos.
4. **Visibilidade de Senhas**:
   - Acessar `/perfil/editar` e testar os três botões de visualização de senha.
5. **Responsividade Mobile**:
   - Abrir o DevTools emulando celular (ex.: iPhone 14 com 390px de largura).
   - Abrir o Histórico, Logs e Usuários e confirmar que todos os botões têm altura confortável (mínimo de 44px), as tabelas possuem scroll horizontal suave e os modais cabem na tela sem cortes.
