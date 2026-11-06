document.addEventListener("DOMContentLoaded", function () {
    isVisitante = estaEmModoVisitante();
    restaurarValoresFormulario();
    renderizarTabelasSeExistirem();
    renderizarEstruturaArquivos();
    checarCamadas();
    if(!isVisitante)carregarBanco();
    salvarConfiguracoesSession(false);

    let banco = document.getElementById("banco");
    let srv = document.getElementById("servidor");
    let porta = document.getElementById("porta");
    let usr = document.getElementById("usuario");
    let pass = document.getElementById("senha");
    if(srv) srv.addEventListener("change",salvarConfiguracoesSession(false));
    if(porta) porta.addEventListener("change",salvarConfiguracoesSession(false));
    if(usr) usr.addEventListener("change",salvarConfiguracoesSession(false));
    if(pass) pass.addEventListener("change",salvarConfiguracoesSession(false));
    if (banco) {
        banco.addEventListener("change", function () {
            let nomeProjeto = document.getElementById("nomeProjeto") ? document.getElementById("nomeProjeto").value : "";
            let srv = document.getElementById("servidor") ? document.getElementById("servidor").value : "localhost";
            let porta = document.getElementById("porta") ? document.getElementById("porta").value : "3306";
            let usr = document.getElementById("usuario") ? document.getElementById("usuario").value : "root";
            let pass = document.getElementById("senha") ? document.getElementById("senha").value : "";
            let bancoVal = document.getElementById("banco") ? document.getElementById("banco").value : "";

            if (nomeProjeto) sessionStorage.setItem("mvc_nomeProjeto", nomeProjeto);
            if (srv) sessionStorage.setItem("mvc_servidor", srv);
            if (porta) sessionStorage.setItem("mvc_porta", porta);
            if (usr) sessionStorage.setItem("mvc_usuario", usr);
            if (pass !== undefined) sessionStorage.setItem("mvc_senha", pass);

            if (bancoVal) {
                if (sessionStorage.getItem("mvc_banco") !== bancoVal) {
                    sessionStorage.setItem("mvc_tabelasDisabled", "[]");
                }
                sessionStorage.setItem("mvc_banco", bancoVal);
                carregarTabelas(false);
            } else {
                sessionStorage.removeItem("mvc_banco");
                sessionStorage.removeItem("mvc_tabelas");
            }
        });
    }
});

globalThis.URL_BASE = new URL("../..", document.currentScript.src).href.replace(/\/$/, "");

function estaEmModoVisitante() {
    if (typeof window.MODO_VISITANTE === "boolean") {
        return window.MODO_VISITANTE;
    }
    const wrapper = document.getElementById("mvcCreatorWrapper");
    return wrapper ? wrapper.getAttribute("data-visitante") === "1" : false;
}

function mostrarErro(mensagem, tipo = "erro") {
    const alerta = document.getElementById("alerta-erro");
    const spanMensagem = document.getElementById("alerta-mensagem");
    if (!alerta || !spanMensagem) return;

    spanMensagem.textContent = String(mensagem || "");
    alerta.className = "alerta alerta-" + tipo;
}

function fecharAlerta() {
    const alerta = document.getElementById("alerta-erro");
    const spanMensagem = document.getElementById("alerta-mensagem");
    if (!alerta) return;

    alerta.classList.add("oculto");
    if (spanMensagem) spanMensagem.textContent = "";
}

function escaparAtributoHtml(valor) {
    return String(valor ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function salvarConfiguracoesSession(dispararTabelas = false) {
    let nomeProjeto = document.getElementById("nomeProjeto") ? document.getElementById("nomeProjeto").value : "";
    let srv = document.getElementById("servidor") ? document.getElementById("servidor").value : (sessionStorage.getItem("mvc_servidor") || "localhost");
    let porta = document.getElementById("porta") ? document.getElementById("porta").value : (sessionStorage.getItem("mvc_porta") || "3306");
    let usr = document.getElementById("usuario") ? document.getElementById("usuario").value : (sessionStorage.getItem("mvc_usuario") || "root");
    let pass = document.getElementById("senha") ? document.getElementById("senha").value : (sessionStorage.getItem("mvc_senha") || "");
    let banco = document.getElementById("banco") ? document.getElementById("banco").value : "";

    if (nomeProjeto) sessionStorage.setItem("mvc_nomeProjeto", nomeProjeto);
    if (srv) sessionStorage.setItem("mvc_servidor", srv);
    if (porta) sessionStorage.setItem("mvc_porta", porta);
    if (usr) sessionStorage.setItem("mvc_usuario", usr);
    if (pass !== undefined) sessionStorage.setItem("mvc_senha", pass);
    if (banco) sessionStorage.setItem("mvc_banco", banco);
    if (sessionStorage.getItem("mvc_tabelas") === "[]") sessionStorage.removeItem("mvc_banco");

    if (dispararTabelas && banco) {
        carregarTabelas(false);
    }
}

function restaurarValoresFormulario() {
    if (document.getElementById("nomeProjeto") && sessionStorage.getItem("mvc_nomeProjeto")) {
        document.getElementById("nomeProjeto").value = sessionStorage.getItem("mvc_nomeProjeto");
    }
    if (document.getElementById("servidor") && sessionStorage.getItem("mvc_servidor")) {
        document.getElementById("servidor").value = sessionStorage.getItem("mvc_servidor");
    }
    if (document.getElementById("porta") && sessionStorage.getItem("mvc_porta")) {
        document.getElementById("porta").value = sessionStorage.getItem("mvc_porta");
    }
    if (document.getElementById("usuario") && sessionStorage.getItem("mvc_usuario")) {
        document.getElementById("usuario").value = sessionStorage.getItem("mvc_usuario");
    }
    if (document.getElementById("senha") && sessionStorage.getItem("mvc_senha") !== null) {
        document.getElementById("senha").value = sessionStorage.getItem("mvc_senha");
    }
    if (document.getElementById("cfg-comentarios")) {
        document.getElementById("cfg-comentarios").checked = obterComentariosDidaticos();
    }
}

function aplicarBancoSelecionadoNoSelect(elBanco, selecionadoMemoria) {
    if (!elBanco) return;
    if (selecionadoMemoria) {
        const existeOpcao = Array.from(elBanco.options).some(opt => opt.value === selecionadoMemoria);
        if (existeOpcao) {
            elBanco.value = selecionadoMemoria;
            sessionStorage.setItem("mvc_banco", elBanco.value);
            carregarTabelas(false);
            return;
        }
        elBanco.value = "";
        sessionStorage.removeItem("mvc_banco");
        sessionStorage.removeItem("mvc_tabelas");
    }
}

function carregarBanco() {
    let elBanco = document.getElementById("banco");
    if (!elBanco) return;

    salvarConfiguracoesSession(false);

    let selecionado = sessionStorage.getItem("mvc_banco") || elBanco.value || "";
    let srv = sessionStorage.getItem("mvc_servidor") || "localhost";
    let porta = sessionStorage.getItem("mvc_porta") || "3306";
    let usr = sessionStorage.getItem("mvc_usuario") || "root";
    let pass = sessionStorage.getItem("mvc_senha") || "";

    // Modo Visitante: reaproveita o endpoint de leitura de bancos locais do PHPMeuAmigo (SHOW DATABASES via PDO)
    if (estaEmModoVisitante()) {
        const data = new FormData();
        data.append("host", srv);
        data.append("porta", porta);
        data.append("usuario", usr);
        data.append("senha", pass);

        let xhr = new XMLHttpRequest();
        xhr.open("POST", URL_BASE + "/phpmeuamigo/conectar-mysql-local", true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) return;
            if (xhr.status === 200) {
                try {
                    const res = JSON.parse(xhr.responseText);
                    if (res.sucesso && Array.isArray(res.bancos)) {
                        let html = "<option value=''>Selecione um banco</option>";
                        res.bancos.forEach(function (nomeBanco) {
                            const escaped = escaparAtributoHtml(nomeBanco);
                            html += `<option value="${escaped}">${escaped}</option>`;
                        });
                        elBanco.innerHTML = html;
                        aplicarBancoSelecionadoNoSelect(elBanco, selecionado);
                        return;
                    }
                    elBanco.innerHTML = "<option value=''>Nenhum banco local encontrado</option>";
                    if (res.mensagem) {
                        mostrarErro(res.mensagem, "aviso");
                    }
                } catch (e) {
                    console.error("Erro ao processar listagem de bancos locais:", e);
                    elBanco.innerHTML = "<option value=''>Erro ao carregar bancos locais</option>";
                }
            }
        };
        xhr.send(data);
        return;
    }

    // Modo Autenticado: carrega os bancos associados ao usuário logado
    const data = new FormData();
    data.append("selecionado", selecionado);

    let xhr = new XMLHttpRequest();
    xhr.open("POST", URL_BASE + "/projetos/getDatabases", true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            elBanco.innerHTML = "<option value=''>Selecione um banco</option>" + xhr.responseText;
            aplicarBancoSelecionadoNoSelect(elBanco, selecionado);
        }
    };
    xhr.send(data);
}

function salvarSessionTabelas(){

    const Tabelas = document.getElementsByClassName('cb-tabela');
    var tabelasDesativadas = [];
    for(const element of Tabelas){ 
        !element.checked ? tabelasDesativadas.push({"id_tabela": element.value,"checked":element.checked}) : null;   
    }
    

    sessionStorage.setItem("mvc_tabelasDisabled",JSON.stringify(tabelasDesativadas));

}

// Retorna os IDs (string) das tabelas desmarcadas, tolerando valor ausente/vazio/inválido no sessionStorage.
function obterIdsTabelasDesativadas() {
    try {
        const bruto = sessionStorage.getItem("mvc_tabelasDisabled");
        const lista = bruto ? JSON.parse(bruto) : [];
        return Array.isArray(lista) ? lista.map(t => String(t.id_tabela)) : [];
    } catch (e) {
        return [];
    }
}

// Retorna somente as tabelas detectadas que continuam selecionadas pelo usuário.
function obterTabelasSelecionadas() {
    try {
        const tabelas = JSON.parse(sessionStorage.getItem("mvc_tabelas")) || [];
        const desativadas = obterIdsTabelasDesativadas();
        return tabelas.filter(t => !desativadas.includes(String(t.id_tabela)));
    } catch (e) {
        return [];
    }
}
function salvarSessionCamadas(){
    const camadas = document.getElementsByClassName('mvc-option');
    var camadasDesativadas = [];
    for(const element of camadas){
        !element.checked ? camadasDesativadas.push({"gerador": element.value,"checked":element.checked}) : null;
    }
    // console.log(camadas);
    // console.log(camadasDesativadas);
    sessionStorage.setItem("mvc_camadasDisabled",JSON.stringify(camadasDesativadas));
}

function carregarTabelas(salvarConfig = true) {
    if (salvarConfig) {
        salvarConfiguracoesSession(false);
    }
    if (sessionStorage.getItem('mvc_tabelasDisabled') === null) {
        sessionStorage.setItem('mvc_tabelasDisabled', "[]");
    }

    let elBanco = document.getElementById("banco");
    let banco = (elBanco && elBanco.value) ? elBanco.value : (sessionStorage.getItem("mvc_banco") || "");

    if (!banco) {
        if (salvarConfig) {
            mostrarErro("Selecione um banco de dados antes de avançar.", "aviso");
        }
        return;
    }

    fecharAlerta();

    const data = new FormData();
    data.append('banco', banco);
    data.append('servidor', sessionStorage.getItem("mvc_servidor") || "localhost");
    data.append('porta', sessionStorage.getItem("mvc_porta") || "3306");
    data.append('usuario', sessionStorage.getItem("mvc_usuario") || "root");
    data.append('senha', sessionStorage.getItem("mvc_senha") || "");

    let xhr = new XMLHttpRequest();
    xhr.open('POST', URL_BASE + '/projetos/getTabelas', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                let res = JSON.parse(xhr.responseText);
                if (res.sucesso) {
                    sessionStorage.setItem("mvc_tabelas", JSON.stringify(res.tabelas));
                    if (
                        (window.location.href === URL_BASE + '/projetos/mvc-creator?step=configurar' ||
                         window.location.href === URL_BASE + '/projetos/mvc-creator') &&
                        salvarConfig
                    ) {
                        window.location.href = URL_BASE + '/projetos/mvc-creator?step=tabelas';
                    }
                } else {
                    mostrarErro(res.mensagem || "Erro ao carregar as tabelas do banco.");
                }
            } catch (e) {
                console.error("Erro no parse JSON", e);
            }
        }
    };
    xhr.send(data);
}
function obterCamadasDesativadas() {
    try {
        const lista = JSON.parse(sessionStorage.getItem("mvc_camadasDisabled") || "[]");
        return Array.isArray(lista) ? lista.map(c => String(c.gerador)) : [];
    } catch (e) {
        return [];
    }
}

function obterSemBancoLocal() {
    return sessionStorage.getItem("mvc_semBancoLocal") === "1";
}

// RN 06: sem banco local ativo, config.php e database.sql são sempre gerados (marcados e travados).
function aplicarRegraSemBancoLocal() {
    const semBanco = obterSemBancoLocal();
    for (const element of document.getElementsByClassName('mvc-option')) {
        if (element.value === 'config' || element.value === 'database') {
            if (semBanco) element.checked = true;
            element.disabled = semBanco;
        }
    }
}

function salvarSemBancoLocal() {
    const flag = document.getElementById("opt-sem-banco-local");
    sessionStorage.setItem("mvc_semBancoLocal", flag && flag.checked ? "1" : "0");
    aplicarRegraSemBancoLocal();
    salvarSessionCamadas();
}

function obterComentariosDidaticos() {
    const val = sessionStorage.getItem("mvc_comentarios");
    return val === null ? true : val === "1";
}

function salvarComentariosDidaticos() {
    const el = document.getElementById("opt-comentarios");
    if (el) {
        sessionStorage.setItem("mvc_comentarios", el.checked ? "1" : "0");
    }
}

function salvarConfigComentarios(ativo) {
    sessionStorage.setItem("mvc_comentarios", ativo ? "1" : "0");
    const el = document.getElementById("opt-comentarios");
    if (el) el.checked = ativo;
}

function checarCamadas(){
    const desativadas = obterCamadasDesativadas();

    for(const element of document.getElementsByClassName('mvc-option')){
        if(desativadas.includes(element.value)) element.checked = false;
    }

    const flag = document.getElementById("opt-sem-banco-local");
    if (flag) flag.checked = obterSemBancoLocal();
    aplicarRegraSemBancoLocal();

    const optComentarios = document.getElementById("opt-comentarios");
    if (optComentarios) {
        optComentarios.checked = obterComentariosDidaticos();
    }
    const cfgComentarios = document.getElementById("cfg-comentarios");
    if (cfgComentarios) {
        cfgComentarios.checked = obterComentariosDidaticos();
    }
}


function renderizarTabelasSeExistirem() {
    let container = document.getElementById("container-tabelas");
    if (!container) return;

    let tabelasJson = sessionStorage.getItem("mvc_tabelas");
    if (sessionStorage.getItem("mvc_banco")) {
        try {
            let tabelas = JSON.parse(tabelasJson);
            if (!Array.isArray(tabelas) || tabelas.length === 0) {
                mostrarErro("Nenhuma tabela encontrada neste banco de dados.");
                container.innerHTML = "";
                return;
            }
            let tabelasDesativadas = obterIdsTabelasDesativadas();
            let html = '<div style="display: flex; flex-direction: column; gap: 10px; margin: 15px 0;">';
            for (const key in tabelas) {
                const element = tabelas[key];
                var check = tabelasDesativadas.includes(String(element.id_tabela)) ? '' : 'checked';
                html += `<label style="display: flex; align-items: center; gap: 10px; font-size: 15px; cursor: pointer; background: var(--surface);color: var(--text); padding: 10px 14px; border-radius: 8px;border: 1px solid var(--border);">
                    <input type="checkbox" oninput="salvarSessionTabelas()" class="cb-tabela" value="`+ element['id_tabela'] + `" ` + check + ` style="width: 18px; height: 18px;">
                    <span><strong>`+ element['nome_tabelaUC'] + `</strong></span>
                </label>`;
            }
            html += '</div>';
            container.innerHTML = html;
        } catch (e) {
            mostrarErro("Erro ao carregar as tabelas salvas.");
            container.innerHTML = "";
        }
    } else {
        mostrarErro("Nenhuma tabela detectada. Volte ao Passo 1 e selecione o banco de dados.", "aviso");
        container.innerHTML = "";
    }
}

// Escolhas do usuário (tabelas, camadas e RN 06) enviadas tanto à pré-visualização quanto à geração.
function montarDadosEscolhas() {
    const data = new FormData();
    data.append('banco', sessionStorage.getItem("mvc_banco") || "");
    data.append('tabelasDesativadas', sessionStorage.getItem("mvc_tabelasDisabled") || "");
    data.append('camadasDesativadas', sessionStorage.getItem("mvc_camadasDisabled") || "");
    data.append('semBancoLocal', obterSemBancoLocal() ? '1' : '0');
    data.append('comentarios', obterComentariosDidaticos() ? '1' : '0');
    data.append('servidor', sessionStorage.getItem("mvc_servidor") || "localhost");
    data.append('porta', sessionStorage.getItem("mvc_porta") || "3306");
    data.append('usuario', sessionStorage.getItem("mvc_usuario") || "root");
    data.append('senha', sessionStorage.getItem("mvc_senha") || "");
    return data;
}

// A árvore vem do back-end (MotorGeracao::listarCaminhos): é exatamente o que entrará no .zip.
// A lógica de quais camadas geram quais arquivos fica só no PHP, sem duplicar regras aqui.
function renderizarEstruturaArquivos() {
    const container = document.getElementById("container-estrutura");
    if (!container) return;

    const contador = document.getElementById("contador-arquivos");
    const botaoAvancar = document.getElementById("btn-avancar-geracao");
    const listaAvisos = document.getElementById("avisos-estrutura");

    const bloquearAvanco = (bloquear) => {
        if (!botaoAvancar) return;
        botaoAvancar.setAttribute("aria-disabled", bloquear ? "true" : "false");
        botaoAvancar.style.pointerEvents = bloquear ? "none" : "";
        botaoAvancar.style.opacity = bloquear ? "0.5" : "";
    };

    if (obterTabelasSelecionadas().length === 0) {
        container.textContent = "Nenhuma tabela selecionada. Volte à etapa \"Tabelas Detectadas\" e marque ao menos uma.";
        if (contador) contador.textContent = "0 arquivos serão gerados.";
        bloquearAvanco(true);
        return;
    }

    container.textContent = "Carregando estrutura...";

    let xhr = new XMLHttpRequest();
    xhr.open('POST', URL_BASE + '/projetos/previewEstrutura', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState !== 4) return;
        try {
            if (xhr.status !== 200) throw new Error("HTTP " + xhr.status);
            const res = JSON.parse(xhr.responseText);

            if (!res.sucesso || !res.total) {
                // textContent evita interpretar a mensagem/nomes como HTML
                container.textContent = res.mensagem || "Nenhum arquivo será gerado com as opções atuais.";
                if (contador) contador.textContent = "0 arquivos serão gerados.";
                bloquearAvanco(true);
                return;
            }

            container.textContent = arvoreComoTexto(res.arquivos);
            if (contador) contador.textContent = res.total + " arquivo(s) serão gerados.";
            if (listaAvisos) {
                listaAvisos.innerHTML = "";
                (res.avisos || []).forEach(texto => {
                    const li = document.createElement("li");
                    li.textContent = texto;
                    listaAvisos.appendChild(li);
                });
            }
            bloquearAvanco(false);
        } catch (e) {
            container.textContent = "Não foi possível carregar a estrutura de arquivos.";
            console.error(e);
        }
    };
    xhr.send(montarDadosEscolhas());
}

function executarGeracaoMvc() {
    let nomeProjeto = sessionStorage.getItem("mvc_nomeProjeto") || "meu_projeto";

    if (obterTabelasSelecionadas().length === 0) {
        mostrarErro("Selecione ao menos uma tabela para gerar o projeto.", "aviso");
        return;
    }

    fecharAlerta();

    const data = montarDadosEscolhas();
    data.append('nomeProjeto', nomeProjeto);

    let btn = document.getElementById("btn-gerar-final");
    const textoPadraoBtn = estaEmModoVisitante() ? "📦 Baixar Projeto (.ZIP)" : "🚀 Gerar Todo o Sistema (.ZIP)";
    if (btn) btn.innerText = "⏳ Gerando projeto...";

    let xhr = new XMLHttpRequest();
    xhr.open('POST', URL_BASE + '/projetos/gerarMvc', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                let res = JSON.parse(xhr.responseText);
                if (res.sucesso) {
                    sessionStorage.setItem("mvc_ultimaSaida", JSON.stringify({
                        projeto: nomeProjeto,
                        arquivos: res.arquivos || []
                    }));
                    window.ultimoDownloadUrl = res.downloadUrl || "";

                    const painelConclusao = document.getElementById("mvc-conclusao-geracao");
                    const resumoConclusao = document.getElementById("mvc-conclusao-resumo");
                    if (painelConclusao) {
                        painelConclusao.style.display = "block";
                    }
                    if (resumoConclusao) {
                        resumoConclusao.textContent = `O pacote '${nomeProjeto}.zip' foi preparado com ${res.total} arquivo(s).`;
                    }

                    let aviso = (res.avisos && res.avisos.length) ? " | Atenção: " + res.avisos.join("; ") : "";
                    mostrarErro(res.mensagem + " (" + res.total + " arquivos)" + aviso, "sucesso");
                    if (res.downloadUrl) {
                        window.location.href = res.downloadUrl;
                    }
                } else {
                    mostrarErro("Erro ao gerar: " + (res.mensagem || "Falha desconhecida."));
                }
            } catch (e) {
                mostrarErro("Resposta inesperada do servidor ao gerar.");
                console.error(e);
            }
            if (btn) btn.innerText = textoPadraoBtn;
        }
    };
    xhr.send(data);
}

function baixarNovamenteZip() {
    // Em modo visitante o arquivo temporário é removido do servidor logo após o primeiro download (RN 01).
    // Portanto, aciona uma nova compilação efêmera caso o visitante clique em baixar novamente.
    if (estaEmModoVisitante() || !window.ultimoDownloadUrl) {
        executarGeracaoMvc();
        return;
    }
    window.location.href = window.ultimoDownloadUrl;
}