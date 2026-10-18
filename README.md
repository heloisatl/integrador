# DevStudio

Ambiente integrado para prototipagem web, modelagem de bancos de dados relacionais e geracao automatizada de arquiteturas MVC em PHP.

---

## 1. Visao Geral da Arquitetura

O **DevStudio** e uma aplicacao web desenvolvida em PHP orientada ao padrao MVC (Model-View-Controller), sem dependencia de frameworks pesados no backend. O sistema atua como uma ferramenta de produtividade para engenharia de software e desenvolvimento web, composta por tres modulos centrais:

1. **MVC Creator**: Mecanismo de introspeccao de esquemas relacionais que gera projetos PHP completos (Controllers, Models, Repositories, Views, Router e ConnectionFactory), empacotados em arquivos ZIP para download.
2. **Page Maker**: Construtor visual de interfaces web estaticas responsivas, permitindo configuracao de cabecalhos, menus de navegacao, tipografia, paletas de cores, componentes e exportacao de HTML/CSS.
3. **PHPMeuAmigo**: Modulo de modelagem e gestao de esquemas MySQL integrado a aplicacao, com suporte a manipulacao de tabelas, atributos, chaves primarias e estrangeiras, alem de importacao de scripts SQL e sincronizacao com bancos locais.

---

## 2. Pilha Tecnologica

- **Linguagem Backend**: PHP 8.1+
- **Persistencia**: MySQL 5.7+ / MariaDB 10.4+ via PDO (PHP Data Objects)
- **Padroes Arquiteturais**: MVC nativo, Front Controller, Repository Pattern, Factory Pattern
- **Frontend**: HTML5 semantico, CSS3 com variaveis customizadas (suporte nativo a temas Dark/Light), JavaScript moderno (Fetch API para comunicacao assincrona)
- **Servico de E-mail**: PHPMailer via conexao SMTP autenticada (TLS/SSL)

---

## 3. Estrutura de Diretorios

```text
integrador/
├── app/
│   ├── config/             # Bootstrap de ambiente, constantes globais e parser de .env
│   ├── controllers/        # Controladores das rotas e regras de fluxo HTTP
│   ├── core/               # Nucleo do framework: Router, Autoload e utilitarios base
│   ├── database/           # Fabricas de conexao PDO e scripts SQL de inicializacao
│   ├── helpers/            # Funcoes auxiliares de sessao, sanitizacao e validacao
│   ├── models/             # Entidades de dominio com propriedades e metodos de acesso
│   ├── repositories/       # Camada de abstracao de banco e execucao de queries preparadas
│   ├── services/           # Regras de negocio auxiliares e integracoes
│   ├── tools/              # Ferramentas internas:
│   │   ├── gerador/        # Motores de geracao de codigo PHP MVC e compilacao ZIP
│   │   ├── mail/           # Modulo de envio de e-mails transacionais (PHPMailer)
│   │   └── SchemaInspector.php # Introspeccao de metadados de tabelas e colunas
│   └── views/              # Templates PHP renderizados no servidor
│       ├── autenticacao/   # Telas de login, cadastro e recuperacao de senha
│       ├── include/        # Fragmentos reutilizaveis (topbar, sidebar, layout base)
│       ├── projetos/       # Telas de gestao de projetos, MVC Creator, Page Maker e PHPMeuAmigo
│       └── usuarios/       # Modulo de administracao de usuarios
├── docs/                   # Documentacao tecnica e guias de implementacao
│   └── guias/              # Registros historicos de mudancas e guias tecnicos
├── public/                 # Document root exposto pelo servidor web
│   ├── assets/             # Recursos estaticos (CSS, JavaScript, imagens, icones)
│   ├── downloads/          # Diretorio temporario para geracao de arquivos compactados
│   └── index.php           # Ponto de entrada unico da aplicacao (Front Controller)
├── .env.example            # Modelo de variaveis de configuracao de ambiente
└── README.md               # Documentacao tecnica do repositorio
```

---

## 4. Requisitos de Ambiente

- **PHP**: Versao 8.1 ou superior
- **Extensoes PHP Necessarias**:
  - `pdo_mysql` (comunicacao com MySQL)
  - `mbstring` (manipulacao de strings UTF-8)
  - `zip` (compactacao de projetos no MVC Creator)
  - `openssl` (comunicacao segura SMTP)
- **Banco de Dados**: MySQL 5.7+ ou MariaDB 10.4+
- **Servidor Web**: Apache (com modulo `mod_rewrite` habilitado), Nginx ou PHP Built-in Server

---

## 5. Instalacao e Configuracao

### 5.1. Clonagem do Repositorio

```bash
git clone https://github.com/heloisatl/integrador.git
cd integrador
```

### 5.2. Variaveis de Ambiente

Copie o arquivo de exemplo de ambiente e preencha as credenciais:

```bash
cp .env.example .env
```

Parametros de configuracao disponiveis no `.env`:

| Variavel | Descricao | Valor Padrao |
|---|---|---|
| `DEV_ENVIRONMENT` | Habilita exibicao de erros no PHP (`true` ou `false`) | `true` |
| `APP_NAME` | Nome da aplicacao exibido na interface | `DevStudio` |
| `DB_HOST` | Host do servidor MySQL | `localhost` |
| `DB_NAME` | Nome da base de dados principal do sistema | `mvc_creator` |
| `DB_USER` | Usuario do MySQL | `root` |
| `DB_PASS` | Senha do usuario do MySQL | *(vazio)* |
| `SMTP_HOST` | Servidor SMTP para disparo de e-mails | `smtp.gmail.com` |
| `SMTP_PORT` | Porta de comunicacao SMTP (TLS) | `587` |
| `SMTP_USER` | Conta de autenticacao SMTP | `seu-email@gmail.com` |
| `SMTP_PASS` | Chave de aplicativo ou senha SMTP | `sua-senha-de-app` |
| `MAIL_FROM_ADDRESS` | Endereco remetente das mensagens | `seu-email@gmail.com` |
| `MAIL_FROM_NAME` | Nome de exibicao do remetente | `"Equipe DevStudio"` |

### 5.3. Inicializacao do Banco de Dados

Execute o script de inicializacao presente em `app/database/scripts/script.sql` no seu servidor MySQL:

```bash
mysql -u root -p < app/database/scripts/script.sql
```

O script criara o schema `mvc_creator`, as tabelas relacionais (`usuario`, `banco`, `tabela`, `atributo`, `projeto`, `estilo`, `log`) e inserira um usuario administrador inicial:

- **E-mail**: `admin@devstudio.com`
- **Senha padrao**: `admin123`

---

## 6. Execucao

### 6.1. Servidor Embutido do PHP

Para desenvolvimento local rapido, execute o servidor a partir da raiz do repositorio apontando o document root para a pasta `public/`:

```bash
php -S localhost:8081 -t public
```

Acesse em seu navegador: `http://localhost:8081`

### 6.2. Apache / XAMPP

Ao utilizar o XAMPP ou servidor Apache tradicional:
1. Posicione o projeto dentro de `htdocs/integrador`.
2. Assegure que o modulo `mod_rewrite` esteja ativo no `httpd.conf`.
3. O roteamento sera tratado automaticamente pelo arquivo `public/.htaccess`.

---

## 7. Modulos do Sistema

### 7.1. Autenticacao e Controle de Acesso (RBAC)
- Autenticacao via sessao PHP persistente.
- Armazenamento de senhas utilizando `password_hash()` com algoritmo BCRYPT.
- Recuperacao de credenciais com tokens de expiracao temporaria e envio de e-mails transacionais.
- Niveis de permissao:
  - `admin`: Acesso irrestrito a administracao de contas de usuarios e auditoria.
  - `usuario`: Gestao de projetos proprios, geracao de codigo e modelagem.

### 7.2. MVC Creator
- **Etapa 1 (Configuracao)**: Selecao do banco de dados relacional e informacoes basicas do projeto.
- **Etapa 2 (Tabelas)**: Selecao e inspecao das tabelas a serem mapeadas.
- **Etapa 3 (Opcoes)**: Configuracao de inclusao de views, repositorios e comentarios descritivos.
- **Etapa 4 (Estrutura)**: Previa da arvore de arquivos que serao compilados.
- **Etapa 5 (Geracao & Download)**: Construcao das classes PHP em memoria e geracao de arquivo compactado (.ZIP).

### 7.3. Page Maker
- Construtor modular com secoes dedicadas a:
  - Cabecalho e metadados.
  - Menu de navegacao e links.
  - Layout e grid de conteudo.
  - Elementos suplementares (cards, tabelas, botoes e formulários).
- Pre-visualizacao em tempo real no navegador.
- Exportacao do arquivo HTML montado e folhas de estilo CSS independentes.

### 7.4. PHPMeuAmigo
- Gerenciador visual de bases relacionais.
- Criacao, renomeacao e remocao de bancos, tabelas e atributos.
- Inspecao de propriedades de coluna (Primary Key, Not Null, Auto Increment, Unique).
- Importador de scripts `.sql` com parser e execucao de queries.
- Conector de instâncias locais para sincronizacao imediata com a aplicacao.

---

## 8. Principais Endpoints e Rotas

### Roteamento Web

| Metodo | Rota | Controlador / Acao | Acesso |
|---|---|---|---|
| `GET` | `/` ou `/projetos` | `ProjetoController@index` | Autenticado |
| `GET` | `/login` | `AutenticacaoController@login` | Publico |
| `POST` | `/logar` | `AutenticacaoController@logar` | Publico |
| `GET` | `/logout` | `AutenticacaoController@logout` | Autenticado |
| `GET` | `/cadastro` | `AutenticacaoController@cadastro` | Publico |
| `GET` | `/recuperar-senha` | `AutenticacaoController@recuperarSenha` | Publico |
| `GET` | `/usuarios` | `UsuarioController@index` | Administrador |
| `GET` | `/perfil` | `UsuarioController@perfil` | Autenticado |
| `GET` | `/projetos/mvc-creator` | `ProjetoController@mvcCreator` | Autenticado |
| `GET` | `/projetos/pagemaker` | `ProjetoController@pageMaker` | Autenticado |
| `GET` | `/projetos/historico` | `ProjetoController@historico` | Autenticado |
| `GET` | `/projetos/saida` | `ProjetoController@saida` | Autenticado |
| `GET` | `/projetos/phpmeuamigo` | `PhpMeuAmigoController@index` | Autenticado |

### API PHPMeuAmigo (AJAX / JSON)

| Metodo | Endpoint | Descricao |
|---|---|---|
| `GET` | `/phpmeuamigo/bancos` | Retorna a listagem de bancos de dados do usuario |
| `POST` | `/phpmeuamigo/bancos/salvar` | Cria ou atualiza uma definicao de banco |
| `POST` | `/phpmeuamigo/bancos/excluir` | Remove um banco e suas dependencias em cascata |
| `GET` | `/phpmeuamigo/tabelas?banco={id}` | Lista as tabelas pertencentes a um banco |
| `POST` | `/phpmeuamigo/tabelas/salvar` | Cria ou edita o nome de uma tabela |
| `POST` | `/phpmeuamigo/tabelas/excluir` | Remove uma tabela |
| `GET` | `/phpmeuamigo/atributos?tabela={id}` | Lista os atributos/colunas de uma tabela |
| `POST` | `/phpmeuamigo/atributos/salvar` | Salva definicao de coluna (tipo, chaves e restricoes) |
| `POST` | `/phpmeuamigo/atributos/excluir` | Remove uma coluna |
| `POST` | `/phpmeuamigo/importar-sql` | Processa e executa script SQL enviado |
| `POST` | `/phpmeuamigo/conectar-mysql-local` | Testa conectividade com instancia local |
| `POST` | `/phpmeuamigo/importar-banco-local` | Importa metadados de schema ativo no MySQL local |

---

## 9. Diretrizes de Seguranca e Boas Praticas

- Todas as operacoes de persistencia utilizam Prepared Statements via PDO, prevenindo SQL Injection.
- O arquivo `.env` contem credenciais sensiveis e esta incluido no `.gitignore`, nunca devendo ser comitado no controle de versao.
- As senhas nunca sao armazenadas em texto puro, sendo processadas por funcoes de hashing criptografico unidirecional.
- O diretorio raiz publico do servidor web deve apontar estritamente para `public/`, impedindo exposicao direta dos codigos-fonte das camadas `app/` e arquivos de configuracao.
