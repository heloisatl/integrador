function carregarBanco(){
    const URL_BASE = "http://localhost:8080";
    let usr=document.getElementById("usuario").value;
    let pass=document.getElementById("senha").value;
    let srv=document.getElementById("servidor").value;
    const data = new FormData();

    data.append('user',usr);
    data.append('pass',pass);
    data.append('server',srv);
    let url = '/nomeLegal';
    let xhr = new XMLHttpRequest();
    xhr.open('POST',URL_BASE+url,true);
    xhr.onreadystatechange = function() {
        if(xhr.readyState==4){
            
            if(xhr.status==200){
                // console.log("TESTSTSE");
                document.getElementById("database").innerHTML=xhr.responseText;
                
                
            }
        }
    }
    xhr.send(data);
    // console.log(usr+'+'+pass+'+'+srv);
}

function criaClasse(){
    const URL_BASE = "http://localhost:8080";
    let usr=document.getElementById("usuario").value;
    let pass=document.getElementById("senha").value;
    let srv=document.getElementById("servidor").value;
    let db=document.getElementById('database').value;
    const data = new FormData();

    data.append('user',usr);
    data.append('pass',pass);
    data.append('server',srv);
    data.append('database',db);
    let url ='/CriaClasse';
    let xhr = new XMLHttpRequest();
    xhr.open('POST',URL_BASE+url,true);
    xhr.onreadystatechange = function(){
        if(xhr.readyState==4){
            if(xhr.status==200){
                console.log('asdasd');
                console.log(xhr.responseText);
            }
        }
    }
    xhr.send(data);
}


// console.log('testetsetestestse');