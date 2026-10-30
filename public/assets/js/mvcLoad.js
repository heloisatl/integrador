document.addEventListener("DOMContentLoaded", function () {
    restaurarValoresFormulario();
    renderizarTabelasSeExistirem();
    renderizarEstruturaArquivos();
    checarCamadas();
    carregarBanco();
    salvarConfiguracoesSession();

    let banco = document.getElementById("banco");
    if(banco){
        banco.addEventListener("change", function () {
            let nomeProjeto = document.getElementById("nomeProjeto") ? document.getElementById("nomeProjeto").value : "";
            let srv = document.getElementById("servidor") ? document.getElementById("servidor").value : "localhost";
            let usr = document.getElementById("usuario") ? document.getElementById("usuario").value : "root";
            let pass = document.getElementById("senha") ? document.getElementById("senha").value : "";
            let banco = document.getElementById("banco") ? document.getElementById("banco").value : "";

            if (nomeProjeto) sessionStorage.setItem("mvc_nomeProjeto", nomeProjeto);
            if (srv) sessionStorage.setItem("mvc_servidor", srv);
            if (usr) sessionStorage.setItem("mvc_usuario", usr);
            if (pass !== undefined) sessionStorage.setItem("mvc_senha", pass);
            if (banco){
                sessionStorage.setItem("mvc_banco", banco);
            }else{
                sessionStorage.removeItem("mvc_banco");
            }
            carregarTabelas(false);
        });
    }
});

globalThis.URL_BASE = new URL("../..", document.currentScript.src).href.replace(/\/$/, "");


function salvarConfiguracoesSession() {
    let nomeProjeto = document.getElementById("nomeProjeto") ? document.getElementById("nomeProjeto").value : "";
    let srv = document.getElementById("servidor") ? document.getElementById("servidor").value : "localhost";
    let usr = document.getElementById("usuario") ? document.getElementById("usuario").value : "root";
    let pass = document.getElementById("senha") ? document.getElementById("senha").value : "";
    let banco = document.getElementById("banco") ? document.getElementById("banco").value : "";

    if (nomeProjeto) sessionStorage.setItem("mvc_nomeProjeto", nomeProjeto);
    if (srv) sessionStorage.setItem("mvc_servidor", srv);
    if (usr) sessionStorage.setItem("mvc_usuario", usr);
    if (pass !== undefined) sessionStorage.setItem("mvc_senha", pass);
    if (banco) sessionStorage.setItem("mvc_banco", banco);
    if (sessionStorage.getItem("mvc_tabelas")=="[]") sessionStorage.removeItem("mvc_banco");
    carregarTabelas(false);
}

function restaurarValoresFormulario() {
    if (document.getElementById("nomeProjeto") && sessionStorage.getItem("mvc_nomeProjeto")) {
        document.getElementById("nomeProjeto").value = sessionStorage.getItem("mvc_nomeProjeto");
    }
    if (document.getElementById("servidor") && sessionStorage.getItem("mvc_servidor")) {
        document.getElementById("servidor").value = sessionStorage.getItem("mvc_servidor");
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

function carregarBanco() {
    salvarConfiguracoesSession();

   let selecionado = sessionStorage.getItem("mvc_banco") || (document.getElementById("banco") ? document.getElementById("banco").value : "");

    const data = new FormData();
    
    data.append("selecionado", selecionado);

    // console.log(URL_BASE);
    let xhr = new XMLHttpRequest();
    xhr.open('POST', URL_BASE + '/projetos/getDatabases', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            let elBanco = document.getElementById("banco");
            if (elBanco) {
                elBanco.innerHTML = "<option value=''>Selecione um banco</option>" + xhr.responseText;
                
                if (sessionStorage.getItem("mvc_banco")) {
                    elBanco.value = sessionStorage.getItem("mvc_banco");
                    sessionStorage.setItem("mvc_banco", elBanco.value);
                }
            }
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
    salvarConfig ? salvarConfiguracoesSession() : null;
    sessionStorage.getItem('mvc_tabelasDisabled')==null ? sessionStorage.setItem('mvc_tabelasDisabled',[]) :  null ;
    let banco = sessionStorage.getItem("mvc_banco") || (document.getElementById("banco") ? document.getElementById("banco").value : "");

    const data = new FormData();
    data.append('banco', banco);

    let xhr = new XMLHttpRequest();
    xhr.open('POST', URL_BASE + '/projetos/getTabelas', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            try {
                let res = JSON.parse(xhr.responseText);
                if (res.sucesso) {
                    sessionStorage.setItem("mvc_tabelas", JSON.stringify(res.tabelas));
                    console.log(URL_BASE + '/projetos/mvc-creator?step=configurar');
                    // console.log(window.location.href);
                    if((window.location.href == URL_BASE + '/projetos/mvc-creator?step=configurar' || window.location.href == URL_BASE + '/projetos/mvc-creator') && salvarConfig)window.location.href = URL_BASE + '/projetos/mvc-creator?step=tabelas';
                } else {
                    alert(res.mensagem);
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
            // console.log(tabelas);
            if (tabelas.length === 0 ) {
                container.innerHTML = '<p style="height:4vh;display:flex;align-items:center;background-color: #ff4949;color: #320000;border-color: #140000;border-radius: 8px;border-width:2px;border-style: solid;">Nenhuma tabela encontrada neste banco de dados.</p>';
                return;
            }
            let tabelasDesativadas = obterIdsTabelasDesativadas();
            let html = '<div style="display: flex; flex-direction: column; gap: 10px; margin: 15px 0;">';
            for(const key in tabelas){
                const element = tabelas[key];
                var check = tabelasDesativadas.includes(String(element.id_tabela)) ? '' : 'checked';
                html += `<label style="display: flex; align-items: center; gap: 10px; font-size: 15px; cursor: pointer; background: var(--surface);color: var(--text); padding: 10px 14px; border-radius: 8px;border: 1px solid var(--border);">
                    <input type="checkbox" oninput="salvarSessionTabelas()" class="cb-tabela" value="`+ element['id_tabela'] +`" `+ check +` style="width: 18px; height: 18px;">
                    <span><strong>`+ element['nome_tabelaUC'] +`</strong></span>
                </label>`;
                
            }
            
            html += '</div>';
            container.innerHTML = html;
        } catch (e) {
            container.innerHTML = '<p style="height:4vh;display:flex;align-items:center;background-color: #ff4949;color: #320000;border-color: #140000;border-radius: 8px;border-width:2px;border-style: solid;">Erro ao carregar tabelas Salvas.</p>';
        }
    } else {
        container.innerHTML = '<p style="height:4vh;display:flex;align-items:center;background-color: #e0a800;color: #382a00;border-color: #1d1600;border-radius: 8px;border-width:2px;border-style: solid;">Nenhuma tabela detectada. <a href="?step=configurar">Volte ao Passo 1</a> e selecione o banco de dados.</p>';
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
    let usr = sessionStorage.getItem("mvc_usuario") || "root";
    let pass = sessionStorage.getItem("mvc_senha") || "";
    let srv = sessionStorage.getItem("mvc_servidor") || "localhost";
    let nomeProjeto = sessionStorage.getItem("mvc_nomeProjeto") || "meu_projeto";

    if (obterTabelasSelecionadas().length === 0) {
        alert("Selecione ao menos uma tabela para gerar o projeto.");
        return;
    }

    const data = montarDadosEscolhas();
    data.append('nomeProjeto', nomeProjeto);
    data.append('usuario', usr);
    data.append('senha', pass);
    data.append('servidor', srv);

    let btn = document.getElementById("btn-gerar-final");
    if (btn) btn.innerText = "⏳ Gerando projeto...";

    let xhr = new XMLHttpRequest();
    xhr.open('POST', URL_BASE + '/projetos/gerarMvc', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            try {
                let res = JSON.parse(xhr.responseText);
                if (res.sucesso) {
                    // Guarda a lista de arquivos criados para a aba "Saída"
                    sessionStorage.setItem("mvc_ultimaSaida", JSON.stringify({
                        projeto: nomeProjeto,
                        arquivos: res.arquivos || []
                    }));
                    let aviso = (res.avisos && res.avisos.length) ? "\n\nAtenção:\n- " + res.avisos.join("\n- ") : "";
                    alert("✅ " + res.mensagem + " (" + res.total + " arquivos)" + aviso);
                    if (res.downloadUrl) {
                        window.location.href = res.downloadUrl;
                    }
                } else {
                    alert("❌ Erro ao gerar: " + res.mensagem);
                }
            } catch (e) {
                alert("Resposta inesperada do servidor ao gerar.");
                console.log(e);
                console.log(xhr.responseText);
            }
            if (btn) btn.innerText = "🚀 Gerar Todo o Sistema (.ZIP)";
        }
    };
    xhr.send(data);
}