function carregarBanco(){
    const URL_BASE = "http://localhost:8081";
    let usr=document.getElementById("usuario").value;
    let pass=document.getElementById("senha").value;
    let srv=document.getElementById("servidor").value;
    const data = new FormData();

    data.append('usuario',usr);
    data.append('senha',pass);
    data.append('servidor',srv);
    let url = '/projetos/getDatabases';
    let xhr = new XMLHttpRequest();
    xhr.open('POST',URL_BASE+url,true);
    xhr.onreadystatechange = function() {
        if(xhr.readyState==4){
            // console.log("oiasdoiosai")
            if(xhr.status==200){
                // console.log("TESTSTSE");
                // console.log(xhr.responseText);
                document.getElementById("banco").innerHTML=xhr.responseText;
                
                
            }
        }
    }
    xhr.send(data);
    // console.log(usr+'+'+pass+'+'+srv);
}


// console.log("OI")