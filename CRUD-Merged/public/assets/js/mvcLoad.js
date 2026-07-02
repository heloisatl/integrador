function carregarBanco(){
    const URL_BASE = "http://localhost:8080";
    let usr=document.getElementById("user").value;
    let pass=document.getElementById("pass").value;
    let srv=document.getElementById("server").value;
    const data = new FormData();

    data.append('user',usr);
    data.append('pass',pass);
    data.append('server',srv);
    let url = '/projetos/getDatabases';
    let xhr = new XMLHttpRequest();
    xhr.open('POST',URL_BASE+url,true);
    xhr.onreadystatechange = function() {
        if(xhr.readyState==4){
            console.log("oiasdoiosai")
            if(xhr.status==200){
                // console.log("TESTSTSE");
                document.getElementById("mvc-banco").innerHTML=xhr.responseText;
                
                
            }
        }
    }
    xhr.send(data);
    // console.log(usr+'+'+pass+'+'+srv);
}