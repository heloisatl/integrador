document.addEventListener("DOMContentLoaded", function () {
    salvarConfiguracoesSession();
    restaurarValoresFormulario();
    renderizarTabelasSeExistirem();
    checarCamadas();
    carregarBanco();
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
                elBanco.innerHTML = xhr.responseText;
                if (sessionStorage.getItem("mvc_banco")) {
                    elBanco.value = sessionStorage.getItem("mvc_banco");
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
function salvarSessionCamadas(){
    const camadas = document.getElementsByClassName('mvc-option');
    var camadasDesativadas = [];
    for(const element of camadas){
        !element.checked ? camadasDesativadas.push({"gerador": element.value,"checked":element.checked}) : null;
    }
    console.log(camadas);
    console.log(camadasDesativadas);
    sessionStorage.setItem("mvc_camadasDisabled",JSON.stringify(camadasDesativadas));
}

function carregarTabelas(salvarConfig = true) {
    salvarConfig ? salvarConfiguracoesSession() : null;
    sessionStorage.getItem('mvc_tabelasDisabled')==null ? sessionStorage.setItem('mvc_tabelasDisabled',[]) :  null ;
    let usr =  "root";
    let pass = "bancodedados";
    let srv = "localhost";
    let banco = sessionStorage.getItem("mvc_banco") || (document.getElementById("banco") ? document.getElementById("banco").value : "");

    const data = new FormData();
    data.append('usuario', usr);
    data.append('senha', pass);
    data.append('servidor', srv);
    data.append('banco', banco);

    let xhr = new XMLHttpRequest();
    xhr.open('POST', URL_BASE + '/projetos/getTabelas', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            try {
                let res = JSON.parse(xhr.responseText);
                if (res.sucesso) {
                    sessionStorage.setItem("mvc_tabelas", JSON.stringify(res.tabelas));
                    // console.log(URL_BASE + '/projetos/mvc-creator?step=configurar');
                    // console.log(window.location.href);
                    if(window.location.href == URL_BASE + '/projetos/mvc-creator?step=configurar' && salvarConfig)window.location.href = URL_BASE + '/projetos/mvc-creator?step=tabelas';
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
function checarCamadas(){
    let form = document.getElementsByClassName('mvc-option');
    if(!form) return;

    let camadasDesativadas = sessionStorage.getItem('mvc_camadasDisabled');
    // console.log(form);  

    for(const element of form){
        if(camadasDesativadas.includes(element.value)) element.checked = false;
    }
}


function renderizarTabelasSeExistirem() {
    let container = document.getElementById("container-tabelas");
    if (!container) return;

    let tabelasJson = sessionStorage.getItem("mvc_tabelas");
    if (tabelasJson) {
        try {
            let tabelas = JSON.parse(tabelasJson);
            console.log(tabelas);
            if (tabelas.length === 0 ) {
                container.innerHTML = '<p style="height:4vh;display:flex;align-items:center;background-color: #ff4949;color: #320000;border-color: #140000;border-radius: 8px;border-width:2px;border-style: solid;">Nenhuma tabela encontrada neste banco de dados.</p>';
                return;
            }
            let tabelasDesativadas = sessionStorage.getItem('mvc_tabelasDisabled');
            let html = '<div style="display: flex; flex-direction: column; gap: 10px; margin: 15px 0;">';
            for(const key in tabelas){
                const element = tabelas[key];
                var check = tabelasDesativadas ? (tabelasDesativadas.includes(element.id_tabela) ? '' : 'checked') : 'checked';
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

function executarGeracaoMvc() {
    const URL_BASE = "http://localhost:8081"; // Ajuste conforme necessário

    let usr = sessionStorage.getItem("mvc_usuario") || "root";
    let pass = sessionStorage.getItem("mvc_senha") || "";
    let srv = sessionStorage.getItem("mvc_servidor") || "localhost";
    let banco = sessionStorage.getItem("mvc_banco") || "";
    let tabelasDesativadas = sessionStorage.getItem("mvc_tabelasDisabled") || "";
    let camadasDesativadas = sessionStorage.getItem("mvc_camadasDisabled") || "";
    let nomeProjeto = sessionStorage.getItem("mvc_nomeProjeto") || "meu_projeto";

    let checkboxes = document.querySelectorAll('.cb-tabela:checked');
    let tabelas = Array.from(checkboxes).map(cb => cb.value);

    // if (tabelas.length === 0 && sessionStorage.getItem("mvc_tabelas")) {
    //     try {
    //         tabelas = JSON.parse(sessionStorage.getItem("mvc_tabelas"));
    //     } catch (e) {}
    // }

    // if (tabelas.length === 0) {
    //     alert("Selecione ao menos uma tabela para gerar o projeto.");
    //     return;
    // }
    
    const data = new FormData();
    data.append('nomeProjeto', nomeProjeto);
    data.append('usuario', usr);
    data.append('senha', pass);
    data.append('servidor', srv);
    data.append('banco', banco);
    data.append("tabelasDesativadas",tabelasDesativadas);
    data.append("camadasDesativadas",camadasDesativadas)
    tabelas.forEach(t => data.append('tabelas[]', t));

    let btn = document.getElementById("btn-gerar-final");
    if (btn) btn.innerText = "⏳ Gerando projeto...";

    let xhr = new XMLHttpRequest();
    xhr.open('POST', URL_BASE + '/projetos/gerarMvc', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            try {
                let res = JSON.parse(xhr.responseText);
                if (res.sucesso) {
                    alert("✅ " + res.mensagem);
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