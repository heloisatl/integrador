<?php

// require_once(__DIR__ . "/../../controller/ClienteController.php");
// $clienteCont = new ClienteController();
// $clientes = $clienteCont->clienteDAO->listar();
include_once(__DIR__ . "/../include/navbar.php");
?>

<link rel="stylesheet" href="../estilo/style.css">

<div class="container my-5" blac>
    <div class="row justify-content-center">
        <div class="col-md-10">
            <h3 class="text-center mb-4" style="background: linear-gradient(-45deg, var(--bg-dark), var(--bg-darker),   var(--bg-dark)); color: white;">Clientes</h3>
            <table class="table table-bordered table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Telefone</th>
                        <th>Email</th>
                        <th style="width:140px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <!--  -->
                </tbody>
            </table>
            <div class="mt-4 text-center">  
                <a href="cadastrar.php" class="btn btn-primary btn-sm">Cadastrar Cliente</a>
                <a href="../ordem_servico/listar.php" class="btn btn-secondary btn-sm ms-2">Voltar</a>
            </div>
        </div>
    </div>
</div>  

