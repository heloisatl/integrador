function carregarBanco() {
    document.getElementById("carregando").innerHTML = "Carregando..";
    let usr = document.getElementById("usuario").value;
    let srv = document.getElementById("servidor").value;
    let sen = document.getElementById("senha").value;
    let url = 'creator.php?id=0&usuario=' + usr + '&servidor=' + srv + '&senha=' + sen;
    let xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4) {
            if (xhr.status == 200)
                document.getElementById("banco").innerHTML = xhr.responseText;
            document.getElementById("carregando").innerHTML = "";
        }
    }
    xhr.send();
}