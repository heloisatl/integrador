document.addEventListener("DOMContentLoaded", function () {
    restaurarValoresFormulario();
    renderizarTabelasSeExistirem();
});

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
    const URL_BASE = "http://localhost:8081";
    salvarConfiguracoesSession();

    let usr = sessionStorage.getItem("mvc_usuario") || "root";
    let pass = sessionStorage.getItem("mvc_senha") || "";
    let srv = sessionStorage.getItem("mvc_servidor") || "localhost";

    const data = new FormData();
    data.append('usuario', usr);
    data.append('senha', pass);
    data.append('servidor', srv);

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

function carregarTabelas() {
    const URL_BASE = "http://localhost:8081";
    salvarConfiguracoesSession();

    let usr = sessionStorage.getItem("mvc_usuario") || "root";
    let pass = sessionStorage.getItem("mvc_senha") || "";
    let srv = sessionStorage.getItem("mvc_servidor") || "localhost";
    let banco = sessionStorage.getItem("mvc_banco") || (document.getElementById("banco") ? document.getElementById("banco").value : "");

    if (!banco) {
        alert("Por favor, selecione um banco de dados antes de continuar.");
        return;
    }

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
                    window.location.href = '?step=tabelas';
                } else {
                    alert("Erro ao buscar tabelas: " + res.mensagem);
                }
            } catch (e) {
                console.error("Erro no parse JSON", e);
            }
        }
    };
    xhr.send(data);
}

function renderizarTabelasSeExistirem() {
    let container = document.getElementById("container-tabelas");
    if (!container) return;

    let tabelasJson = sessionStorage.getItem("mvc_tabelas");
    if (tabelasJson) {
        try {
            let tabelas = JSON.parse(tabelasJson);
            if (tabelas.length === 0) {
                container.innerHTML = '<p style="color: #ff6b6b;">Nenhuma tabela encontrada neste banco de dados.</p>';
                return;
            }

            let html = '<div style="display: flex; flex-direction: column; gap: 10px; margin: 15px 0;">';
            tabelas.forEach(t => {
                html += `<label style="display: flex; align-items: center; gap: 10px; font-size: 15px; cursor: pointer; background: rgba(255,255,255,0.05); padding: 10px 14px; border-radius: 6px;">
                    <input type="checkbox" class="cb-tabela" value="${t}" checked style="width: 18px; height: 18px;">
                    <span>📊 <strong>${t}</strong></span>
                </label>`;
            });
            html += '</div>';
            container.innerHTML = html;
        } catch (e) {
            container.innerHTML = '<p style="color: #ff6b6b;">Erro ao carregar tabelas salvas.</p>';
        }
    } else {
        container.innerHTML = '<p style="color: #e0a800;">Nenhuma tabela detectada. <a href="?step=configurar">Volte ao Passo 1</a> e selecione o banco de dados.</p>';
    }
}

function executarGeracaoMvc() {
    const URL_BASE = "http://localhost:8081";

    let usr = sessionStorage.getItem("mvc_usuario") || "root";
    let pass = sessionStorage.getItem("mvc_senha") || "";
    let srv = sessionStorage.getItem("mvc_servidor") || "localhost";
    let banco = sessionStorage.getItem("mvc_banco") || "";
    let nomeProjeto = sessionStorage.getItem("mvc_nomeProjeto") || "meu_projeto";

    let checkboxes = document.querySelectorAll('.cb-tabela:checked');
    let tabelas = Array.from(checkboxes).map(cb => cb.value);

    if (tabelas.length === 0 && sessionStorage.getItem("mvc_tabelas")) {
        try {
            tabelas = JSON.parse(sessionStorage.getItem("mvc_tabelas"));
        } catch (e) {}
    }

    if (tabelas.length === 0) {
        alert("Selecione ao menos uma tabela para gerar o projeto.");
        return;
    }

    const data = new FormData();
    data.append('nomeProjeto', nomeProjeto);
    data.append('usuario', usr);
    data.append('senha', pass);
    data.append('servidor', srv);
    data.append('banco', banco);
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
            }
            if (btn) btn.innerText = "🚀 Gerar Todo o Sistema (.ZIP)";
        }
    };
    xhr.send(data);
}