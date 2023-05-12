<?php
// obter os valores dos campos local e date
$local = $_GET['local'];
$date = $_GET['date'];

// código para buscar os dados no banco de dados usando $local e $date
// e exibir os resultados na página
?>
<!DOCTYPE html>
<html lang="pt_br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    
    <title>Chicago - Beach Data Set</title>

	<link rel="shortcut icon" href="img/favicon.ico">
	<link rel="stylesheet" href="css/bootstrap.css" type="text/css">
	<link rel="stylesheet" href="css/font-awesome.css" type="text/css">
	<link rel="stylesheet" href="css/style.css" type="text/css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css">

    <script src="js/jquery.js"></script>
	<script src="js/bootstrap.js"></script>
	<script src="js/jquery.easing.js"></script>
	<script src="js/canvas.js"></script>
	<script src="js/style.js"></script>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <script>
    if (window.location.href.endsWith("search.php")) {
	    window.location.href = "index.php";
    } 
    </script>
</head>

<body id="page-top" data-spy="scroll" data-target=".navbar-custom">
  
    <nav class="navbar navbar-custom-search navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header page-scroll">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
                    <i class="fa fa-bars"></i>
                </button>
                <a class="navbar-brand" href="index.php#page-top">
                    <img src="img/chicago-nav.svg" alt="Logo">
                </a>
            </div>


            <div class="collapse navbar-collapse navbar-right navbar-main-collapse">
                <ul class="nav navbar-nav">

                    <li class="hidden">
                        <a href="index.php#page-top"></a>
                    </li>
                    <li class="page-scroll">
                        <a href="index.php#about-fix">Sobre</a>
                    </li>
                    <li class="page-scroll">
                        <a href="index.php#contact">Contato</a>
                    </li>
                </ul>
            </div>

        </div>

    </nav>

    <section class="intro-search">
        <canvas id="canvas"></canvas>
            <div class="intro-body">
                <div class="container">
                    <div class="row">
                        <div class="col-md-8 col-md-offset-2">
                            <?php
                                if(!empty($local) && !empty($date)) {
                                    $db_host = getenv('DB_HOST');
                                    $db_name = getenv('DB_NAME');
                                    $db_user = getenv('DB_USER');
                                    $db_pass = getenv('DB_PASS');
                                    $dbconn = pg_connect("host=$db_host dbname=$db_name user=$db_user password=$db_pass") or die('Não foi possível conectar: ' . pg_last_error());
                                    // consulta por local e data
                                    $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = '$local' AND date(measurement_timestamp) = '$date' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp LIMIT 1";
                                
                                $result = pg_query($query) or die('A consulta falhou: ' . pg_last_error());
                                }
                                if(pg_num_rows($result) > 0) {
                                    while ($row = pg_fetch_assoc($result)) {
                                        $measurement_timestamp_date = $row['date'];
                                        $measurement_timestamp_hour = $row['hour'];
                                        echo '<div intro-text>';
                                        echo '<h2 class="cover-h2 h2-search">'. $local . '</h2>';
                                        echo '<hr size="10" width="50%" color="#FFF">';
                                        echo '<h2 class="cover-h2 h2-search">'. $measurement_timestamp_date .'</h2>';
                                        echo '</div>';
                                    };
                                } else {
                                    echo '<h2 class="cover-h2 h2-search">Nenhum dado encontrado para o local e dia selecionados.</h2>';
                                }
                                pg_free_result($result);
                                pg_close($dbconn);
                            ?>
                            
                        </div>
                    </div>
                </div>
            </div>
    </section>
   

    <section id="about" class="text-center">
            <div class="about-section about-section-search">
            <?php if(!empty($local) && !empty($date)) { 
                 echo '<h3 class="h3-search">Lembre-se sempre de verificar as condições climáticas antes de sair de casa. Mantenha-se informado, divirta-se com segurança e aproveite tudo o que as praias de Chicago têm a oferecer!</h3>';
             } ?>
   
                    <div class="container container-search">

                        <div class="col-md-6">
                  

                            <!------------------------------------------------------------------>
                            <!-------------------- Temperatura do Ar e Bulbo ------------------->
                            <!------------------------------------------------------------------>
                            <script type="text/javascript">
                            google.charts.load('current', {'packages':['corechart']});
                            google.charts.setOnLoadCallback(drawVisualization);
                            var bulb = 'Bulbo Úmido'

                            function drawVisualization() {
                                // Some raw data (not necessarily accurate)
                                var data = google.visualization.arrayToDataTable([
                                
                                ['Celsius', 'Ar', bulb],

                                <?php
                                    if(isset($_GET['search'])) {
                                        if(isset($_GET['local']) || isset($_GET['date'])) {
                                            $local = $_GET['local'];
                                            $date = $_GET['date'];
                                            
                                            $db_host = getenv('DB_HOST');
                                            $db_name = getenv('DB_NAME');
                                            $db_user = getenv('DB_USER');
                                            $db_pass = getenv('DB_PASS');
                                            $dbconn = pg_connect("host=$db_host dbname=$db_name user=$db_user password=$db_pass") or die('Não foi possível conectar: ' . pg_last_error());
                                        
                                            if(!empty($local) && !empty($date)) {
                                                // consulta por local e data
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,air_temperature,wet_bulb_temperature, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = '$local' AND date(measurement_timestamp) = '$date' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            } elseif(!empty($local)) {
                                                // consulta por local
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,air_temperature,wet_bulb_temperature, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = '$local' AND measurement_timestamp::date = (
                                                SELECT COALESCE(MAX(measurement_timestamp::date), CURRENT_DATE - INTERVAL '1 DAY')
                                                FROM beach_weather_stations
                                                WHERE station_name = 'Oak Street Weather Station'
                                                ) AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            } elseif(!empty($date)) {
                                                // consulta por data
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,air_temperature,wet_bulb_temperature, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = 'Oak Street Weather Station' AND date(measurement_timestamp) = '$date' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            } else {
                                                // consulta sem filtro
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,air_temperature,wet_bulb_temperature, station_name,to_char(measurement_timestamp, 'HH24h') AS hour FROM beach_weather_stations WHERE measurement_timestamp::date = (
                                                SELECT COALESCE(MAX(measurement_timestamp::date), CURRENT_DATE - INTERVAL '1 DAY')
                                                FROM beach_weather_stations
                                                WHERE station_name = 'Oak Street Weather Station'
                                                ) AND station_name = 'Oak Street Weather Station' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            }


                                            $result = pg_query($query) or die('A consulta falhou: ' . pg_last_error());
                                        }
                                        if(pg_num_rows($result) > 0) {
                                            while ($row = pg_fetch_assoc($result)) {
                                                $station_name = $row['station_name'];
                                                $measurement_timestamp = $row['hour'];
                                                $measurement_timestamp_date = $row['date'];
                                                $air_temperature = $row['air_temperature'];
                                                $wet_bulb_temperature = $row['wet_bulb_temperature'] ?? 0;
                                                echo "['$measurement_timestamp', $air_temperature, $wet_bulb_temperature],";
                                            };
                                        } else {
                                        echo "<p>Nenhum dado encontrado para o local e dia selecionados.</p>";
                                        }
                                        pg_free_result($result);
                                        pg_close($dbconn);
                                    }
                                ?>
                                ]);
                                var options = {
                                title : 'Temperatura do ar',
                                titleTextStyle: {fontSize: 16, textAlign: 'center', color: '#383838'},
                                vAxis: {title: 'Temperatura (°C)'},
                                hAxis: {title: 'Horário'},
                                seriesType: 'bars',
                                series: {5: {type: 'line'}}
                                };

                                var chart = new google.visualization.ComboChart(document.getElementById('tempChart'));
                                chart.draw(data, options);

                            }

                            </script>
                            <div id="tempChart" class="chart"></div>


                            <!------------------------------------------------------------------>
                            <!------------------------------------------------------------------>
                        </div>
                        <div class="col-md-6">
                            <!------------------------------------------------------------------>
                            <!-------------------------- Umidade do Ar ------------------------->
                            <!------------------------------------------------------------------>
                            <script type="text/javascript">
                            google.charts.load('current', {'packages':['corechart']});
                            google.charts.setOnLoadCallback(drawVisualization2);

                            function drawVisualization2() {
                                // Some raw data (not necessarily accurate)
                                var data = google.visualization.arrayToDataTable([
                                
                                ['%', 'Umidade do Ar'],

                                <?php
                                    if(isset($_GET['search'])) {
                                        if(isset($_GET['local']) || isset($_GET['date'])) {
                                            $local = $_GET['local'];
                                            $date = $_GET['date'];
                                            
                                            $db_host = getenv('DB_HOST');
                                            $db_name = getenv('DB_NAME');
                                            $db_user = getenv('DB_USER');
                                            $db_pass = getenv('DB_PASS');
                                            $dbconn = pg_connect("host=$db_host dbname=$db_name user=$db_user password=$db_pass") or die('Não foi possível conectar: ' . pg_last_error());
                                        
                                            if(!empty($local) && !empty($date)) {
                                                // consulta por local e data
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,humidity, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = '$local' AND date(measurement_timestamp) = '$date' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            } elseif(!empty($local)) {
                                                // consulta por local
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,humidity, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = '$local' AND measurement_timestamp::date = (
                                                SELECT COALESCE(MAX(measurement_timestamp::date), CURRENT_DATE - INTERVAL '1 DAY')
                                                FROM beach_weather_stations
                                                WHERE station_name = '$local'
                                                ) AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            } elseif(!empty($date)) {
                                                // consulta por data
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,humidity, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = 'Oak Street Weather Station' AND date(measurement_timestamp) = '$date' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            } else {
                                                // consulta sem filtro
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,humidity, station_name,to_char(measurement_timestamp, 'HH24h') AS hour FROM beach_weather_stations WHERE measurement_timestamp::date = (
                                                SELECT COALESCE(MAX(measurement_timestamp::date), CURRENT_DATE - INTERVAL '1 DAY')
                                                FROM beach_weather_stations
                                                WHERE station_name = 'Oak Street Weather Station'
                                                ) AND station_name = 'Oak Street Weather Station' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            }
                                            $result = pg_query($query) or die('A consulta falhou: ' . pg_last_error());
                                        }
                                        if(pg_num_rows($result) > 0) {
                                            while ($row = pg_fetch_assoc($result)) {
                                                $station_name = $row['station_name'];
                                                $measurement_timestamp = $row['hour'];
                                                $measurement_timestamp_date = $row['date'];
                                                $humidity = $row['humidity'] ?? 0;
                                                echo "['$measurement_timestamp', $humidity],";
                                            };
                                        } else {
                                        echo "<p>Nenhum dado encontrado para o local e dia selecionados.</p>";
                                        }
                                        pg_free_result($result);
                                        pg_close($dbconn);
                                    }
                                ?>
                                ]);
                                var options = {
                                title : 'Umidade do ar',
                                titleTextStyle: {fontSize: 16, textAlign: 'center', color: '#383838'},
                                vAxis: {title: 'Umidade do ar (%)'},
                                hAxis: {title: 'Horário'},
                                seriesType: 'scatter',
                                legend: {position: 'none'} 
                                };

                                var chart = new google.visualization.ComboChart(document.getElementById('humAirChart'));
                                chart.draw(data, options);

                            }
                            </script>
                            <div id="humAirChart" class="chart"></div>

                            <!------------------------------------------------------------------>
                            <!------------------------------------------------------------------>                        
                        </div>
                    </div>

                    <div class="container container-search">
                        <div class="col-md-6">
                            <!------------------------------------------------------------------>
                            <!------------------------ Intensidade da Chuva -------------------->
                            <!------------------------------------------------------------------>
                            <script type="text/javascript">
                            google.charts.load('current', {'packages':['corechart']});
                            google.charts.setOnLoadCallback(drawVisualization);

                            function drawVisualization() {
                                // Some raw data (not necessarily accurate)
                                var data = google.visualization.arrayToDataTable([
                                
                                ['mm', 'Intensidade da Chuva'],

                                <?php
                                    if(isset($_GET['search'])) {
                                        if(isset($_GET['local']) || isset($_GET['date'])) {
                                            $local = $_GET['local'];
                                            $date = $_GET['date'];
                                            
                                            $db_host = getenv('DB_HOST');
                                            $db_name = getenv('DB_NAME');
                                            $db_user = getenv('DB_USER');
                                            $db_pass = getenv('DB_PASS');
                                            $dbconn = pg_connect("host=$db_host dbname=$db_name user=$db_user password=$db_pass") or die('Não foi possível conectar: ' . pg_last_error());
                                        
                                            if(!empty($local) && !empty($date)) {
                                                // consulta por local e data
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,rain_intensity, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = '$local' AND date(measurement_timestamp) = '$date' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            } elseif(!empty($local)) {
                                                // consulta por local
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,rain_intensity, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = '$local' AND measurement_timestamp::date = (
                                                SELECT COALESCE(MAX(measurement_timestamp::date), CURRENT_DATE - INTERVAL '1 DAY')
                                                FROM beach_weather_stations
                                                WHERE station_name = 'Oak Street Weather Station'
                                                ) AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            } elseif(!empty($date)) {
                                                // consulta por data
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,rain_intensity, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = 'Oak Street Weather Station' AND date(measurement_timestamp) = '$date' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            } else {
                                                // consulta sem filtro
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,rain_intensity, station_name,to_char(measurement_timestamp, 'HH24h') AS hour FROM beach_weather_stations WHERE measurement_timestamp::date = (
                                                SELECT COALESCE(MAX(measurement_timestamp::date), CURRENT_DATE - INTERVAL '1 DAY')
                                                FROM beach_weather_stations
                                                WHERE station_name = 'Oak Street Weather Station'
                                                ) AND station_name = 'Oak Street Weather Station' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            }


                                            $result = pg_query($query) or die('A consulta falhou: ' . pg_last_error());
                                        }
                                        if(pg_num_rows($result) > 0) {
                                            while ($row = pg_fetch_assoc($result)) {
                                                $station_name = $row['station_name'];
                                                $measurement_timestamp = $row['hour'];
                                                $measurement_timestamp_date = $row['date'];
                                                $rain_intensity = $row['rain_intensity'] ?? 0;
                                                echo "['$measurement_timestamp', $rain_intensity],";
                                            };
                                        } else {
                                        echo "<p>Nenhum dado encontrado para o local e dia selecionados.</p>";
                                        }
                                        pg_free_result($result);
                                        pg_close($dbconn);
                                    }
                                ?>
                                ]);
                                var options = {
                                title : 'Intensidade da Chuva',
                                titleTextStyle: {fontSize: 16, textAlign: 'center', color: '#383838'},
                                vAxis: {title: 'Intensidade da Chuva (mm)'},
                                hAxis: {title: 'Horário'},
                                curveType: 'function',
                                legend: {position: 'none'} 
                                };

                                var chart = new google.visualization.LineChart(document.getElementById('intRainChart'));
                                chart.draw(data, options);

                            }

                            </script>
                            <div id="intRainChart" class="chart"></div>


                            <!------------------------------------------------------------------>
                            <!------------------------------------------------------------------>
                        </div>
                        <div class="col-md-6">
                           <!------------------------------------------------------------------>
                            <!-------------------------- Total de Chuva ------------------------>
                            <!------------------------------------------------------------------>

                            <script type="text/javascript">
                            google.charts.load('current', {'packages':['corechart']});
                            google.charts.setOnLoadCallback(drawVisualization);

                            function drawVisualization() {
                                // Some raw data (not necessarily accurate)
                                var data = google.visualization.arrayToDataTable([
                                
                                ['mm', 'Total de Chuva'],

                                <?php
                                    if(isset($_GET['search'])) {
                                        if(isset($_GET['local']) || isset($_GET['date'])) {
                                            $local = $_GET['local'];
                                            $date = $_GET['date'];
                                            
                                            $db_host = getenv('DB_HOST');
                                            $db_name = getenv('DB_NAME');
                                            $db_user = getenv('DB_USER');
                                            $db_pass = getenv('DB_PASS');
                                            $dbconn = pg_connect("host=$db_host dbname=$db_name user=$db_user password=$db_pass") or die('Não foi possível conectar: ' . pg_last_error());
                                        
                                            if(!empty($local) && !empty($date)) {
                                                // consulta por local e data
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,total_rain, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = '$local' AND date(measurement_timestamp) = '$date' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            } elseif(!empty($local)) {
                                                // consulta por local
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,total_rain, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = '$local' AND measurement_timestamp::date = (
                                                SELECT COALESCE(MAX(measurement_timestamp::date), CURRENT_DATE - INTERVAL '1 DAY')
                                                FROM beach_weather_stations
                                                WHERE station_name = 'Oak Street Weather Station'
                                                ) AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            } elseif(!empty($date)) {
                                                // consulta por data
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,total_rain, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = 'Oak Street Weather Station' AND date(measurement_timestamp) = '$date' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            } else {
                                                // consulta sem filtro
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,total_rain, station_name,to_char(measurement_timestamp, 'HH24h') AS hour FROM beach_weather_stations WHERE measurement_timestamp::date = (
                                                SELECT COALESCE(MAX(measurement_timestamp::date), CURRENT_DATE - INTERVAL '1 DAY')
                                                FROM beach_weather_stations
                                                WHERE station_name = 'Oak Street Weather Station'
                                                ) AND station_name = 'Oak Street Weather Station' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            }


                                            $result = pg_query($query) or die('A consulta falhou: ' . pg_last_error());
                                        }
                                        if(pg_num_rows($result) > 0) {
                                            while ($row = pg_fetch_assoc($result)) {
                                                $station_name = $row['station_name'];
                                                $measurement_timestamp = $row['hour'];
                                                $measurement_timestamp_date = $row['date'];
                                                $total_rain = $row['total_rain'] ?? 0;
                                                echo "['$measurement_timestamp', $total_rain],";
                                            };
                                        } else {
                                        echo "<p>Nenhum dado encontrado para o local e dia selecionados.</p>";
                                        }
                                        pg_free_result($result);
                                        pg_close($dbconn);
                                    }
                                ?>
                                ]);
                                var options = {
                                title : 'Total de Chuva',
                                titleTextStyle: {fontSize: 16, textAlign: 'center', color: '#383838'},
                                vAxis: {title: 'Total de Chuva (mm)'},
                                hAxis: {title: 'Horário'},
                                curveType: 'function',
                                legend: {position: 'none'} 
                                };

                                var chart = new google.visualization.LineChart(document.getElementById('totalRainChart'));
                                chart.draw(data, options);

                            }

                            </script>
                            <div id="totalRainChart" class="chart"></div>

                            <!------------------------------------------------------------------>
                            <!------------------------------------------------------------------>                        
                        </div>
                    </div>

                    <div class="container container-search">
                        <div class="col-md-6">
                            <!------------------------------------------------------------------>
                            <!-------------------------- Direção do Vento ---------------------->
                            <!------------------------------------------------------------------>

                            <script type="text/javascript">
                            google.charts.load('current', {'packages':['corechart']});
                            google.charts.setOnLoadCallback(drawVisualization);

                            function drawVisualization() {
                                // Some raw data (not necessarily accurate)
                                var data = google.visualization.arrayToDataTable([
                                
                                ['Graus', 'Hora', 'Direção do Vento'],

                                <?php
                                    if(isset($_GET['search'])) {
                                        if(isset($_GET['local']) || isset($_GET['date'])) {
                                            $timestamps = array();
                                            $local = $_GET['local'];
                                            $date = $_GET['date'];
                                            
                                            $db_host = getenv('DB_HOST');
                                            $db_name = getenv('DB_NAME');
                                            $db_user = getenv('DB_USER');
                                            $db_pass = getenv('DB_PASS');
                                            $dbconn = pg_connect("host=$db_host dbname=$db_name user=$db_user password=$db_pass") or die('Não foi possível conectar: ' . pg_last_error());
                                        
                                            if(!empty($local) && !empty($date)) {
                                                // consulta por local e data
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,wind_direction, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = '$local' AND date(measurement_timestamp) = '$date' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY hour";
                                            } elseif(!empty($local)) {
                                                // consulta por local
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,wind_direction, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = '$local' AND measurement_timestamp::date = (
                                                SELECT COALESCE(MAX(measurement_timestamp::date), CURRENT_DATE - INTERVAL '1 DAY')
                                                FROM beach_weather_stations
                                                WHERE station_name = 'Oak Street Weather Station'
                                                ) AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY hour";
                                            } elseif(!empty($date)) {
                                                // consulta por data
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,wind_direction, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = 'Oak Street Weather Station' AND date(measurement_timestamp) = '$date' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY hour";
                                            } else {
                                                // consulta sem filtro
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,wind_direction, station_name,to_char(measurement_timestamp, 'HH24h') AS hour FROM beach_weather_stations WHERE measurement_timestamp::date = (
                                                SELECT COALESCE(MAX(measurement_timestamp::date), CURRENT_DATE - INTERVAL '1 DAY')
                                                FROM beach_weather_stations
                                                WHERE station_name = 'Oak Street Weather Station'
                                                ) AND station_name = 'Oak Street Weather Station' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY hour";
                                            }


                                            $result = pg_query($query) or die('A consulta falhou: ' . pg_last_error());
                                        }
                                        if(pg_num_rows($result) > 0) {
                                            while ($row = pg_fetch_assoc($result)) {
                                                $station_name = $row['station_name'];
                                                $measurement_timestamp = $row['hour'];
                                                $measurement_timestamp_date = $row['date'];
                                                $wind_direction = $row['wind_direction'] ?? 0;
                                                $hour = substr($measurement_timestamp, 0, -1);
                                                $timestamps[] = $hour;

                                                echo "['$wind_direction °', $hour, $wind_direction],";

                                            };
                                        } else {
                                        echo "<p>Nenhum dado encontrado para o local e dia selecionados.</p>";
                                        }
                                        pg_free_result($result);
                                        pg_close($dbconn);
                                    }
                                ?>
                                ]);
                                var timestamps = <?php echo json_encode($timestamps); ?>; 
                                var options = {
                                title : 'Direção do Vento',
                                titleTextStyle: {fontSize: 16, textAlign: 'center', color: '#383838'},
                                vAxis: {title: 'Graus (°)'},
                                hAxis: { title: 'Horário', ticks: timestamps},
                                format: 'h',
                                colorAxis: {colors: ['yellow', 'red']},
                                bubble: {textStyle: {fontSize: 11}},
                                legend: {position: 'none'} 
                                };


                                var chart = new google.visualization.BubbleChart(document.getElementById('windDirecChart'));
                                chart.draw(data, options);

                            }

                            </script>
                            <div id="windDirecChart" class="chart"></div>


                            <!------------------------------------------------------------------>
                            <!------------------------------------------------------------------>
                        </div>
                        <div class="col-md-6">
                           <!------------------------------------------------------------------>
                            <!---------- Velocidade do Vento e Velocidade Máxima do Vento ------>
                            <!------------------------------------------------------------------>

                            <script type="text/javascript">
                            google.charts.load('current', {'packages':['corechart']});
                            google.charts.setOnLoadCallback(drawVisualization);

                            function drawVisualization() {
                                // Some raw data (not necessarily accurate)
                                var data = google.visualization.arrayToDataTable([
                                
                                ['m/s', 'Velocidade', 'Máx. Velocidade'],

                                <?php
                                    if(isset($_GET['search'])) {
                                        if(isset($_GET['local']) || isset($_GET['date'])) {
                                            $local = $_GET['local'];
                                            $date = $_GET['date'];
                                            
                                            $db_host = getenv('DB_HOST');
                                            $db_name = getenv('DB_NAME');
                                            $db_user = getenv('DB_USER');
                                            $db_pass = getenv('DB_PASS');
                                            $dbconn = pg_connect("host=$db_host dbname=$db_name user=$db_user password=$db_pass") or die('Não foi possível conectar: ' . pg_last_error());
                                        
                                            if(!empty($local) && !empty($date)) {
                                                // consulta por local e data
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,wind_speed,maximum_wind_speed, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = '$local' AND date(measurement_timestamp) = '$date' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            } elseif(!empty($local)) {
                                                // consulta por local
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,wind_speed,maximum_wind_speed, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = '$local' AND measurement_timestamp::date = (
                                                SELECT COALESCE(MAX(measurement_timestamp::date), CURRENT_DATE - INTERVAL '1 DAY')
                                                FROM beach_weather_stations
                                                WHERE station_name = 'Oak Street Weather Station'
                                                ) AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            } elseif(!empty($date)) {
                                                // consulta por data
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,wind_speed,maximum_wind_speed, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = 'Oak Street Weather Station' AND date(measurement_timestamp) = '$date' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            } else {
                                                // consulta sem filtro
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,wind_speed,maximum_wind_speed, station_name,to_char(measurement_timestamp, 'HH24h') AS hour FROM beach_weather_stations WHERE measurement_timestamp::date = (
                                                SELECT COALESCE(MAX(measurement_timestamp::date), CURRENT_DATE - INTERVAL '1 DAY')
                                                FROM beach_weather_stations
                                                WHERE station_name = 'Oak Street Weather Station'
                                                ) AND station_name = 'Oak Street Weather Station' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            }


                                            $result = pg_query($query) or die('A consulta falhou: ' . pg_last_error());
                                        }
                                        if(pg_num_rows($result) > 0) {
                                            while ($row = pg_fetch_assoc($result)) {
                                                $station_name = $row['station_name'];
                                                $measurement_timestamp = $row['hour'];
                                                $measurement_timestamp_date = $row['date'];
                                                $wind_speed = $row['wind_speed'] ?? 0;
                                                $maximum_wind_speed = $row['maximum_wind_speed'] ?? 0;
                                                echo "['$measurement_timestamp', $wind_speed, $maximum_wind_speed],";
                                            };
                                        } else {
                                        echo "<p>Nenhum dado encontrado para o local e dia selecionados.</p>";
                                        }
                                        pg_free_result($result);
                                        pg_close($dbconn);
                                    }
                                ?>
                                ]);
                                var options = {
                                title : 'Velocidade do Vento e Máxima Velocidade do Vento',
                                titleTextStyle: {fontSize: 16, textAlign: 'center', color: '#383838'},
                                vAxis: {title: 'Velocidade (m/s)'},
                                hAxis: {title: 'Horário'},
                                seriesType: 'bars',
                                series: {5: {type: 'line'}}
                                };

                                var chart = new google.visualization.ColumnChart(document.getElementById('windChart'));
                                chart.draw(data, options);

                            }

                            </script>
                            <div id="windChart" class="chart"></div>

                            <!------------------------------------------------------------------>
                            <!------------------------------------------------------------------>                        
                        </div>
                    </div>

                    <div class="container container-search">
                        <div class="col-md-6">
                            <!------------------------------------------------------------------>
                            <!--------------------- Pressão Atmosférica ------------------------>
                            <!------------------------------------------------------------------>

                            <script type="text/javascript">
                            google.charts.load('current', {'packages':['corechart']});
                            google.charts.setOnLoadCallback(drawVisualization);

                            function drawVisualization() {
                                // Some raw data (not necessarily accurate)
                                var data = google.visualization.arrayToDataTable([
                                
                                ['hPa', 'Pressão Atmosférica'],

                                <?php
                                    if(isset($_GET['search'])) {
                                        if(isset($_GET['local']) || isset($_GET['date'])) {
                                            $local = $_GET['local'];
                                            $date = $_GET['date'];
                                            
                                            $db_host = getenv('DB_HOST');
                                            $db_name = getenv('DB_NAME');
                                            $db_user = getenv('DB_USER');
                                            $db_pass = getenv('DB_PASS');
                                            $dbconn = pg_connect("host=$db_host dbname=$db_name user=$db_user password=$db_pass") or die('Não foi possível conectar: ' . pg_last_error());
                                        
                                            if(!empty($local) && !empty($date)) {
                                                // consulta por local e data
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,barometric_pressure, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = '$local' AND date(measurement_timestamp) = '$date' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            } elseif(!empty($local)) {
                                                // consulta por local
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,barometric_pressure, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = '$local' AND measurement_timestamp::date = (
                                                SELECT COALESCE(MAX(measurement_timestamp::date), CURRENT_DATE - INTERVAL '1 DAY')
                                                FROM beach_weather_stations
                                                WHERE station_name = 'Oak Street Weather Station'
                                                ) AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            } elseif(!empty($date)) {
                                                // consulta por data
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,barometric_pressure, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = 'Oak Street Weather Station' AND date(measurement_timestamp) = '$date' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            } else {
                                                // consulta sem filtro
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,barometric_pressure, station_name,to_char(measurement_timestamp, 'HH24h') AS hour FROM beach_weather_stations WHERE measurement_timestamp::date = (
                                                SELECT COALESCE(MAX(measurement_timestamp::date), CURRENT_DATE - INTERVAL '1 DAY')
                                                FROM beach_weather_stations
                                                WHERE station_name = 'Oak Street Weather Station'
                                                ) AND station_name = 'Oak Street Weather Station' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            }


                                            $result = pg_query($query) or die('A consulta falhou: ' . pg_last_error());
                                        }
                                        if(pg_num_rows($result) > 0) {
                                            while ($row = pg_fetch_assoc($result)) {
                                                $station_name = $row['station_name'];
                                                $measurement_timestamp = $row['hour'];
                                                $measurement_timestamp_date = $row['date'];
                                                $barometric_pressure = $row['barometric_pressure'] ?? 0;
                                                echo "['$measurement_timestamp', $barometric_pressure],";
                                            };
                                        } else {
                                        echo "<p>Nenhum dado encontrado para o local e dia selecionados.</p>";
                                        }
                                        pg_free_result($result);
                                        pg_close($dbconn);
                                    }
                                ?>
                                ]);
                                var options = {
                                title : 'Pressão Atmosférica',
                                titleTextStyle: {fontSize: 16, textAlign: 'center', color: '#383838'},
                                vAxis: {title: 'Pressão (hPa)'},
                                hAxis: {title: 'Horário'},
                                seriesType: 'bars',
                                series: {5: {type: 'line'}},
                                legend: {position: 'none'} 
                                };

                                var chart = new google.visualization.ComboChart(document.getElementById('baroChart'));
                                chart.draw(data, options);

                            }

                            </script>
                            <div id="baroChart" class="chart"></div>


                            <!------------------------------------------------------------------>
                            <!------------------------------------------------------------------>
                        </div>
                        <div class="col-md-6">
                            <!------------------------------------------------------------------>
                            <!------------------------ Radiação Solar -------------------------->
                            <!------------------------------------------------------------------>

                            <script type="text/javascript">
                            google.charts.load('current', {'packages':['corechart']});
                            google.charts.setOnLoadCallback(drawVisualization);

                            function drawVisualization() {
                                // Some raw data (not necessarily accurate)
                                var data = google.visualization.arrayToDataTable([
                                
                                ['W/m²', 'Radiação Solar'],

                                <?php
                                    if(isset($_GET['search'])) {
                                        if(isset($_GET['local']) || isset($_GET['date'])) {
                                            $local = $_GET['local'];
                                            $date = $_GET['date'];
                                            
                                            $db_host = getenv('DB_HOST');
                                            $db_name = getenv('DB_NAME');
                                            $db_user = getenv('DB_USER');
                                            $db_pass = getenv('DB_PASS');
                                            $dbconn = pg_connect("host=$db_host dbname=$db_name user=$db_user password=$db_pass") or die('Não foi possível conectar: ' . pg_last_error());
                                        
                                            if(!empty($local) && !empty($date)) {
                                                // consulta por local e data
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,solar_radiation, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = '$local' AND date(measurement_timestamp) = '$date' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            } elseif(!empty($local)) {
                                                // consulta por local
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,solar_radiation, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = '$local' AND measurement_timestamp::date = (
                                                SELECT COALESCE(MAX(measurement_timestamp::date), CURRENT_DATE - INTERVAL '1 DAY')
                                                FROM beach_weather_stations
                                                WHERE station_name = 'Oak Street Weather Station'
                                                ) AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            } elseif(!empty($date)) {
                                                // consulta por data
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,solar_radiation, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = 'Oak Street Weather Station' AND date(measurement_timestamp) = '$date' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            } else {
                                                // consulta sem filtro
                                                $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,solar_radiation, station_name,to_char(measurement_timestamp, 'HH24h') AS hour FROM beach_weather_stations WHERE measurement_timestamp::date = (
                                                SELECT COALESCE(MAX(measurement_timestamp::date), CURRENT_DATE - INTERVAL '1 DAY')
                                                FROM beach_weather_stations
                                                WHERE station_name = 'Oak Street Weather Station'
                                                ) AND station_name = 'Oak Street Weather Station' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                            }


                                            $result = pg_query($query) or die('A consulta falhou: ' . pg_last_error());
                                        }
                                        if(pg_num_rows($result) > 0) {
                                            while ($row = pg_fetch_assoc($result)) {
                                                $station_name = $row['station_name'];
                                                $measurement_timestamp = $row['hour'];
                                                $measurement_timestamp_date = $row['date'];
                                                $solar_radiation = $row['solar_radiation'] ?? 0;
                                                echo "['$measurement_timestamp', $solar_radiation],";
                                            };
                                        } else {
                                        echo "<p>Nenhum dado encontrado para o local e dia selecionados.</p>";
                                        }
                                        pg_free_result($result);
                                        pg_close($dbconn);
                                    }
                                ?>
                                ]);
                                var options = {
                                title : 'Radiação Solar',
                                titleTextStyle: {fontSize: 16, textAlign: 'center', color: '#383838'},
                                vAxis: {title: 'Unives (W/m²)'},
                                hAxis: {title: 'Horário'},
                                isStacked: true,
                                legend: {position: 'none'}
                                };

                                var chart = new google.visualization.SteppedAreaChart(document.getElementById('solarChart'));
                                chart.draw(data, options);

                            }

                            </script>
                            <div id="solarChart" class="chart"></div>

                            <!------------------------------------------------------------------>
                            <!------------------------------------------------------------------>                        
                        </div>
                    </div>

                    
                    <div class="container container-search">
                        <div class="col-md-6 battery">
                        <!------------------------------------------------------------------>
                        <!--------------------- Bateria dos Sensores ----------------------->
                        <!------------------------------------------------------------------>

                        <script type="text/javascript">
                        google.charts.load('current', {'packages':['corechart']});
                        google.charts.setOnLoadCallback(drawVisualization);

                        function drawVisualization() {
                            // Some raw data (not necessarily accurate)
                            var data = google.visualization.arrayToDataTable([
                            
                            ['Bateria dos Sensores', 'Nível da Bateria %'],

                            <?php
                                if(isset($_GET['search'])) {
                                    if(isset($_GET['local']) || isset($_GET['date'])) {
                                        $timestamps = array();
                                        $local = $_GET['local'];
                                        $date = $_GET['date'];
                                        
                                        $db_host = getenv('DB_HOST');
                                        $db_name = getenv('DB_NAME');
                                        $db_user = getenv('DB_USER');
                                        $db_pass = getenv('DB_PASS');
                                        $dbconn = pg_connect("host=$db_host dbname=$db_name user=$db_user password=$db_pass") or die('Não foi possível conectar: ' . pg_last_error());
                                    
                                        if(!empty($local) && !empty($date)) {
                                            // consulta por local e data
                                            $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,battery_life, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = '$local' AND date(measurement_timestamp) = '$date' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                        } elseif(!empty($local)) {
                                            // consulta por local
                                            $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,battery_life, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = '$local' AND measurement_timestamp::date = (
                                            SELECT COALESCE(MAX(measurement_timestamp::date), CURRENT_DATE - INTERVAL '1 DAY')
                                            FROM beach_weather_stations
                                            WHERE station_name = 'Oak Street Weather Station'
                                            ) AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                        } elseif(!empty($date)) {
                                            // consulta por data
                                            $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,battery_life, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = 'Oak Street Weather Station' AND date(measurement_timestamp) = '$date' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                        } else {
                                            // consulta sem filtro
                                            $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,battery_life, station_name,to_char(measurement_timestamp, 'HH24h') AS hour FROM beach_weather_stations WHERE measurement_timestamp::date = (
                                            SELECT COALESCE(MAX(measurement_timestamp::date), CURRENT_DATE - INTERVAL '1 DAY')
                                            FROM beach_weather_stations
                                            WHERE station_name = 'Oak Street Weather Station'
                                            ) AND station_name = 'Oak Street Weather Station' AND EXTRACT(HOUR FROM measurement_timestamp)::integer % 2 = 0 ORDER BY measurement_timestamp";
                                        }


                                        $result = pg_query($query) or die('A consulta falhou: ' . pg_last_error());
                                    }
                                    if(pg_num_rows($result) > 0) {
                                        while ($row = pg_fetch_assoc($result)) {
                                            $station_name = $row['station_name'];
                                            $measurement_timestamp = $row['hour'];
                                            $measurement_timestamp_date = $row['date'];
                                            $battery_life = $row['battery_life'] ?? 0;
                                            echo "['$measurement_timestamp' , $battery_life],";
                                        };
                                    } else {
                                    echo "<p>Nenhum dado encontrado para o local e dia selecionados.</p>";
                                    }
                                    pg_free_result($result);
                                    pg_close($dbconn);
                                }
                            ?>
                            ]);
                            var options = {
                            title : 'Bateria dos Sensores',
                            titleTextStyle: {fontSize: 16, textAlign: 'center', color: '#383838'},
                            vAxis: {title: 'Bateria (%)'},
                            hAxis: {title: 'Horário'},
                            seriesType: 'bars',
                            series: {5: {type: 'line'}}
                            };

                            var chart = new google.visualization.ColumnChart(document.getElementById('batteryChart'));
                            chart.draw(data, options);

                        }
                        
                        </script>
                        <div id="batteryChart" class="chart"></div>


                        <!------------------------------------------------------------------>
                        <!------------------------------------------------------------------>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <?php
        if ($wet_bulb_temperature === 0) {
            echo "<script> bulb = 'N/A'; </script>";
        }
    ?>



    <footer class="brand brand-search">
        <a>
            <span class="light">2023 | </span><span>Chicago</span><span class="light"> - Beach Data Set</span>
        </a>
    </footer>


    <a href="index.php" id="float-button">Nova Pesquisa</a>

</body>
</html>
