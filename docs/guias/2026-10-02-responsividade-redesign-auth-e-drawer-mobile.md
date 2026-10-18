# Guia Técnico de Arquitetura e Implementação - Responsividade Global, Padronização de Telas de Autenticação & Drawer Mobile Ergonômico
**Data da Alteração**: `2026-10-02`  
**Título do Guia**: `2026-10-02-responsividade-redesign-auth-e-drawer-mobile.md`  
**Módulo**: Interface do Usuário / Layout Global, Navegação Responsiva & Autenticação  
**Público-Alvo**: Desenvolvedores Front-End, Arquitetos de Soluções e Mantenedores do DevStudio  

---

## 1. Contexto e Motivação das Alterações

Ao longo da evolução do DevStudio, a adição contínua de ferramentas especializadas (como o *Gerador MVC*, *Page Maker* e a integração recente do *PHPMeuAmigo*) exigiu melhorias substanciais na coerência visual, estabilidade do ecossistema e suporte a dispositivos de diferentes resoluções:

1. **Expansão do Ecossistema na Home e Integração do PHPMeuAmigo**:
   - Atualização da métrica central na página inicial para 3 ferramentas integradas.
   - Adição do card do PHPMeuAmigo e respectivo guia rápido de uso, com paleta sólida e identidade visual alinhada às demais ferramentas.
   - Interconexão com o MVC Creator (botão "Criar novo banco" apontando para `/projetos/phpmeuamigo`).
   - Normalização de margens do rodapé e avisos permanentes no tom `#5B6AF0`.

2. **Criação da Tela de Perfil do Usuário**:
   - Desenvolvimento da interface visual de Perfil (`/perfil`), apresentando dados cadastrais, resumo de atividades, estatísticas e gerenciamento de conta, com alternância de tema escuro/claro.

3. **Padronização Visual das Telas de Autenticação**:
   - As telas de login, cadastro, recuperação e redefinição de senha apresentavam disparidades estruturais (algumas exibiam o rodapé do painel interno e não possuíam identificação da marca).
   - Inserção da logo oficial DevStudio centralizada no topo e eliminação definitiva do rodapé nas telas de autenticação.

4. **Eliminação de Estouros Horizontais e Falhas de Responsividade**:
   - Em telas pequenas, elementos com tabelas e trechos de código estendiam o container flex além da largura da tela, quebrando a viewport mobile.
   - Unificação de regras e remoção de media queries duplicadas e conflitantes.

5. **Drawer Móvel Ergonômico (Thumb-Friendly)**:
   - A navegação em smartphones e tablets apresentava itens desalinhados e o botão hambúrguer flutuando fora de posição.
   - Criação de um menu em formato gaveta deslizante (Off-Canvas/Full Overlay), com alvos táteis de 48px, ícones dedicados por ferramenta, cards de ação rápida ao alcance do polegar e preservação total da barra horizontal do desktop ($\ge 901\text{px}$).

---

## 2. Visão Geral dos Arquivos Modificados e Criados

```
integrador/
├── app/
│   ├── controllers/
│   │   ├── AutenticacaoController.php         # Roteamento e regras de autenticação
│   │   └── UsuarioController.php              # Método perfil() para renderizar a view
│   ├── views/
│   │   ├── autenticacao/
│   │   │   ├── login.php                      # Logo DevStudio, centralização e sem footer
│   │   │   ├── cadastro.php                   # Logo DevStudio, centralização e sem footer
│   │   │   ├── recuperar_senha.php            # Logo DevStudio, feedback e sem footer
│   │   │   ├── redefinir_senha.php            # Logo DevStudio e sem footer
│   │   │   └── esqueceu_senha.php             # Logo DevStudio e sem footer
│   │   ├── include/
│   │   │   ├── head.php                       # Resolução de min-width: 0 e JS do drawer
│   │   │   └── navigation.php                 # Marcação semântica com ícones e drawer mobile
│   │   ├── projetos/
│   │   │   ├── home.php                       # Card PHPMeuAmigo e guia rápido
│   │   │   └── phpmeuamigo.php                # Ajustes de rodapé e aviso permanente
│   │   └── usuarios/
│   │       └── perfil.php                     # Interface visual completa do perfil
├── docs/
│   └── guias/
│       └── 2026-10-02-responsividade-redesign-auth-e-drawer-mobile.md # Este documento
└── public/
    ├── index.php                              # Registro da rota /perfil
    └── assets/
        └── css/
            ├── perfil.css                     # Estilos exclusivos da página de perfil
            ├── phpmeuamigo.css                # Grid flexível minmax(0, 1fr) e tabela responsiva
            ├── pagina-mvc.css                 # Empilhamento de seletores e botões no mobile
            └── style.css                      # Regras do Drawer mobile e prevenção de overflow
```

---

## 3. Detalhamento Técnico das Soluções

### 3.1 Correção Global de Overflow com Flexbox (`min-width: 0`)

**Problema Identificado**: No padrão CSS Flexbox, containers flexíveis possuem `min-width: auto` por padrão. Qualquer filho com conteúdo largo (como blocos `<pre>`, tabelas de bancos de dados ou títulos longos) impede o container de encolher, forçando a página a estourar horizontalmente em telas pequenas.

**Solução Aplicada (`app/views/include/head.php` e `public/assets/css/style.css`)**:
```css
.main-content {
    flex: 1;
    min-width: 0; /* Permite que o container respeite a largura da tela */
    display: flex;
    flex-direction: column;
    padding: 32px 32px 0 32px;
    overflow-y: auto;
    background-color: var(--bg);
}
```

### 3.2 Grid Dinâmico do PHPMeuAmigo (`minmax(0, 1fr)`)

No CSS Grid, a fração `1fr` calcula implicitamente `minmax(auto, 1fr)`. Quando a tabela de colunas era renderizada, a coluna principal expandia além da tela.

**Solução Aplicada (`public/assets/css/phpmeuamigo.css`)**:
```css
.phpma-main-layout {
    display: grid;
    grid-template-columns: 280px minmax(0, 1fr);
    gap: 24px;
}

@media (max-width: 900px) {
    .phpma-main-layout {
        grid-template-columns: minmax(0, 1fr);
        gap: 16px;
    }
    .phpma-sidebar-panel {
        position: static;
    }
}
```

Para a tabela de atributos, foi implementado container com rolagem horizontal autônoma:
```css
.phpma-table-responsive {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.phpma-table {
    min-width: 620px; /* Garante legibilidade das colunas sem quebrar o layout */
}
```

---

### 3.3 Padronização das Telas de Autenticação (Auth Redesign)

As páginas `login.php`, `cadastro.php`, `recuperar_senha.php`, `redefinir_senha.php` e `esqueceu_senha.php` foram unificadas sob o mesmo padrão de design:

1. **Estrutura de Centralização Limpa**:
   - `body`: `min-height: 100vh; display: flex; align-items: center; justify-content: center;`
   - `.auth-wrapper`: Centralizado, largura máxima de 480px, padding vertical fluido.
2. **Logo DevStudio Superior**:
   - Inclusão do link institucional `.auth-brand` com a logo e tipografia `Syne`:
     ```html
     <a href="<?= URL_BASE ?>/projetos" class="auth-brand" title="DevStudio">
         <span class="logo"></span>
         <span class="auth-brand-text title-link">DevStudio</span>
     </a>
     ```
3. **Remoção do Rodapé**:
   - Supressão de `require_once __DIR__ . '/../include/footer.php';`, encerrando a view diretamente com `</body></html>`.
4. **Links de Alternância**:
   - Links discretos de rodapé permitindo circular entre Login, Criar Conta e Continuar sem login.

---

### 3.4 Arquitetura do Drawer de Navegação Mobile (Ergonomia Móvel)

#### A. Estratégia de Isolamento Desktop vs. Mobile
Para garantir que a barra superior do Desktop permaneça exatamente com o visual original, todos os novos componentes visuais para mobile foram configurados com:
```css
/* Desktop padrão (>= 901px) */
.mobile-nav-group {
    display: contents;
}

.mobile-section-label,
.nav-icon,
.nav-arrow,
.mobile-user-card {
    display: none !important;
}
```

#### B. Header Fixo e Botão Hambúrguer Morfável
O botão hambúrguer permanece estritamente ancorado no canto superior direito da barra de 60px, transformando-se suavemente em um ícone de fechar (`✕`) ao abrir o menu:

```css
/* Animação do botão hambúrguer para 'X' */
.topbar-toggle.open span:nth-child(1) {
    transform: translateY(7px) rotate(45deg);
}
.topbar-toggle.open span:nth-child(2) {
    opacity: 0;
    transform: scale(0);
}
.topbar-toggle.open span:nth-child(3) {
    transform: translateY(-7px) rotate(-45deg);
}
```

#### C. Estrutura do Drawer Móvel (`.topbar-nav.open`)
Ao ser aberto, o menu assume posição de tela cheia abaixo da topbar (`top: 60px; left: 0; right: 0; bottom: 0;`), com rolagem tátil suave:

```
┌──────────────────────────────────────────────┐
│ [Logo DevStudio]                     [ ✕ ]   │  <- Topbar Fixa (60px)
├──────────────────────────────────────────────┤
│ FERRAMENTAS & PÁGINAS                        │
│ ┌──────────────────────────────────────────┐ │
│ │ 🏠  Início                             > │ │  <- Cards táteis (min 48px)
│ └──────────────────────────────────────────┘ │
│ ┌──────────────────────────────────────────┐ │
│ │ ⚡  MVC Creator                         > │ │  <- Destaque ativo azul #5B6AF0
│ └──────────────────────────────────────────┘ │
│ ┌──────────────────────────────────────────┐ │
│ │ 📄  Page Maker                         > │ │
│ └──────────────────────────────────────────┘ │
│ ┌──────────────────────────────────────────┐ │
│ │ 🕒  Histórico                          > │ │
│ └──────────────────────────────────────────┘ │
│ ┌──────────────────────────────────────────┐ │
│ │ 📤  Saída                              > │ │
│ └──────────────────────────────────────────┘ │
│ ┌──────────────────────────────────────────┐ │
│ │ 🐬  PHPMeuAmigo                        > │ │
│ └──────────────────────────────────────────┘ │
│                                              │
│ PREFERÊNCIAS & CONTA                         │
│ ┌──────────────────────────────────────────┐ │
│ │ Alternar Tema                          🌙 │ │  <- Toggle tátil de tema
│ └──────────────────────────────────────────┘ │
│ ┌──────────────────────────────────────────┐ │
│ │ 👤  Nome do Usuário                      │ │
│ │     usuario@email.com                    │ │
│ │ ┌───────────────────┐ ┌────────────────┐ │ │
│ │ │ ⚙ Ver Perfil      │ │ 🚪 Sair        │ │ │  <- Ações ao alcance do polegar
│ │ └───────────────────┘ └────────────────┘ │ │
│ └──────────────────────────────────────────┘ │
└──────────────────────────────────────────────┘
```

#### D. Comportamento JavaScript e Fechamento Automático
Em `app/views/include/head.php`:
1. `toggleTopbarNav()` alterna a classe `.open` no menu e no botão, além de adicionar `.mobile-menu-active` ao `body` para impedir rolagem de fundo indesejada.
2. Listener global em `DOMContentLoaded` fecha automaticamente o drawer assim que qualquer link de navegação é clicado pelo usuário em telas $\le 900\text{px}$.

---

## 4. Matriz de Breakpoints Testados e Validados

| Breakpoint | Resolução Típica | Comportamento do Layout |
| :--- | :--- | :--- |
| **Mobile Pequeno** | 390 × 844 px (iPhone 12/13/14) | Topbar fixa 60px, drawer móvel full-screen, formulários e tabelas empilhados a 100%, zero estouro horizontal. |
| **Mobile Médio / Grande** | 412 × 915 px (Android Pixel / Galaxy) | Layout fluido com cards de toque ergonômico, espaçamento de 16px nas laterais, botões táteis. |
| **Tablet Vertical** | 768 × 1024 px (iPad Mini / Air) | Sidebar empilha acima do conteúdo, menu mobile disponível, grids de cartões ajustados. |
| **Desktop / Laptop** | 1280 × 800 px / 1920 × 1080 px | Sidebar lateral clássica de 240px, topbar horizontal completa com dropdown de perfil, zero impacto visual das regras mobile. |

---

## 5. Checklist de Verificação e Manutenibilidade

Ao adicionar novas páginas, ferramentas ou links de navegação no DevStudio:

- [ ] **Novas Rotas na Topbar (`navigation.php`)**:
  - Adicionar o item dentro de `.mobile-nav-group` com a tag `<i class="bi bi-... nav-icon"></i>`, a `<span class="nav-text">` e a `<i class="bi bi-chevron-right nav-arrow"></i>`.
  - Isso garante que a ferramenta apareça como texto puro no desktop e como card completo no mobile.
- [ ] **Novas Telas de Autenticação**:
  - Utilizar o container `.auth-wrapper` com a logo `.auth-brand`.
  - Não incluir `footer.php`.
- [ ] **Novas Tabelas e Grids**:
  - Envolver tabelas sempre em containers `.table-responsive` ou utilizar `grid-template-columns: minmax(0, 1fr)`.
  - Nunca utilizar `width` fixo em pixels em containers pais dentro de `.main-content`.
