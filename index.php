<!DOCTYPE html>
<html lang="pt_br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    
    <title>Chicago - Beach Data Set</title>

	<link rel="shortcut icon" href="img/favicon.ico">
	<link rel="stylesheet" href="css/bootstrap.css" type="text/css">
	<link rel="stylesheet" href="css/font-awesome.css" type="text/css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css">
    <link rel="stylesheet" href="css/style.css" type="text/css">

    <script src="js/jquery.js"></script>
	<script src="js/bootstrap.js"></script>
	<script src="js/jquery.easing.js"></script>
	<script src="js/canvas.js"></script>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="js/style.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>



</head>

<body id="page-top" data-spy="scroll" data-target=".navbar-custom">
  
    <nav class="navbar navbar-custom navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header page-scroll">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
                    <i class="fa fa-bars"></i>
                </button>
                <a class="navbar-brand" href="#page-top">
                    <img src="img/chicago-nav.svg" alt="Logo">
                </a>
            </div>


            <div class="collapse navbar-collapse navbar-right navbar-main-collapse">
                <ul class="nav navbar-nav">

                    <li class="hidden">
                        <a href="#page-top"></a>
                    </li>
                    <li class="page-scroll">
                        <a href="#about-fix">Sobre</a>
                    </li>
                    <li class="page-scroll">
                        <a href="#contact">Contato</a>
                    </li>
                </ul>
            </div>

        </div>

    </nav>

    <section class="intro">
        <canvas id="canvas"></canvas>
        <div class="intro-body">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        <img src="img/chicago.svg" alt="Logo">
                        <p id="cover-h2">Beach Data Set</p>
                        <div class="wrap">
                        <div id="about-fix"></div>    
                            <form method="GET" action="search.php">
                                <div class="search">
                                <select name="local" id="local" class="select" required value="<?php echo $_POST['station_name']; ?>">
                                    <option value="">Selecione um local</option>
                                    <?php
                                    $db_host = getenv('DB_HOST');
                                    $db_name = getenv('DB_NAME');
                                    $db_user = getenv('DB_USER');
                                    $db_pass = getenv('DB_PASS');
                                    $dbconn = pg_connect("host=$db_host dbname=$db_name user=$db_user password=$db_pass") or die('Não foi possível conectar: ' . pg_last_error());
                                    $query = "SELECT DISTINCT station_name FROM beach_weather_stations ORDER BY station_name DESC";
                                    $result = pg_query($query) or die('A consulta falhou: ' . pg_last_error());
                                    while ($row = pg_fetch_assoc($result)) {
                                        echo "<option value=\"" . $row['station_name'] . "\">" . $row['station_name'] . "</option>";
                                    }
                                    pg_free_result($result);
                                    pg_close($dbconn);
                                    ?>
                                </select>
                                
                                <input type="text" name="date" class="searchTerm" id="datepicker" autocomplete="off" placeholder="Escolha a data" required>

                                <button type="submit" name="search" class="searchButton" id='search'>Buscar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
   

    <section id="about" class="text-center">
            <div class="about-section">
                    <div class="container">
                     <div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">
                            <h2>Sobre nós</h2>
                            <p id="p-title" class="p-title"><p class="pin">Chicago Beach Data Set é um aplicativo web desenvolvido pelos alunos do Eixo de Computação da UNIVESP, polo de Bariri, para a disciplina Projeto Integrador.</p> <br>
                            <p class="pin"> O site apresenta dados coletados a cada duas horas por sensores automatizados instalados nas praias de Chicago, fornecidos pela prefeitura da cidade. Esses dados incluem informações sobre temperatura, velocidade do vento, radiação solar, umidade e outras variáveis climáticas relevantes para as atividades de lazer e esportes nas praias da região. </p><br>
                            <p class="pin">Com base nesses dados, os usuários podem verificar as condições climáticas nas praias e tomar decisões acertadas sobre suas atividades.</p><br>
                            <p class="pin">Chicago Beach Data Set é uma ferramenta útil para quem deseja desfrutar das praias de Chicago com segurança e conforto.</p><br>
                        </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <section id="contact" class="text-center">
        <div class="contact-section">
        <div class="row">
        <div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 contact-box">
                <h2 class="h2-padding">Contato</h2>
                <p class="p-title p-title-contact">Para sugestões ou dúvidas, sinta-se livre para entrar em contato conosco</p>

                <form class="form" method="post">      
                    <input name="nome" type="text" class="feedback-input" placeholder="Nome" required>   
                    <input name="email" type="text" class="feedback-input" placeholder="E-mail" required>
                    <textarea name="mensagem" class="feedback-input" placeholder="Mensagem" required></textarea>
                    <button id="btn-send" type="submit">Enviar</button>
                </form>

            </div>
        </div>
            </div>
        </div>
    </section>


    <footer class="brand">
        <a class="fa">
            <span class="light">2023 | </span><span>Chicago</span><span class="light"> - Beach Data Set</span>
        </a>
    </footer>

    <script>
	    $( function() {
	    $( "#datepicker" ).datepicker({ dateFormat: 'yy-mm-dd' });
	    } );
    </script>

</body>
</html>
