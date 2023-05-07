<!DOCTYPE html>
<html>
<head>
    <title>Dados das últimas 24 horas</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

</head>
<body>
    <h1>Dados das últimas 24 horas</h1>

    <div id="tempChart" style="width: 900px; height: 500px;"></div>


    <form method="GET" action="">
        <label for="local">Local:</label>
        <select name="local" id="local">
            <option value="">Selecione um local</option>
            <?php
            $db_host = getenv('DB_HOST');
            $db_name = getenv('DB_NAME');
            $db_user = getenv('DB_USER');
            $db_pass = getenv('DB_PASS');
            $dbconn = pg_connect("host=$db_host dbname=$db_name user=$db_user password=$db_pass") or die('Não foi possível conectar: ' . pg_last_error());
            $query = "SELECT DISTINCT station_name FROM beach_weather_stations ORDER BY station_name ASC";
            $result = pg_query($query) or die('A consulta falhou: ' . pg_last_error());
            while ($row = pg_fetch_assoc($result)) {
                echo "<option value=\"" . $row['station_name'] . "\">" . $row['station_name'] . "</option>";
            }
            pg_free_result($result);
            pg_close($dbconn);
            ?>
        </select>
        
        <label for="datepicker">Data:</label>
        <input type="text" name="date" id="datepicker" autocomplete="off" placeholder="Escolha a data">

        <button type="submit" name="search">Buscar</button>
    </form>

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
                $query = "SELECT * FROM beach_weather_stations WHERE station_name = '$local' AND date(measurement_timestamp) = '$date' ORDER BY measurement_timestamp DESC LIMIT 500";
            } elseif(!empty($local)) {
                // consulta por local
                $query = "SELECT * FROM beach_weather_stations WHERE station_name = '$local' ORDER BY measurement_timestamp DESC LIMIT 500";
            } elseif(!empty($date)) {
                // consulta por data
                $query = "SELECT * FROM beach_weather_stations WHERE date(measurement_timestamp) = '$date' ORDER BY measurement_timestamp DESC LIMIT 500";
            } else {
                // consulta na tabela inteira
                $query = "SELECT * FROM beach_weather_stations ORDER BY measurement_timestamp DESC LIMIT 5";
            }


            $result = pg_query($query) or die('A consulta falhou: ' . pg_last_error());
        }
        if(pg_num_rows($result) > 0) {
            echo "<table>";
            echo "<tr>";
            echo "<th>Local</th>";
            echo "<th>Data e Hora</th>";
            echo "<th>Temperatura do Ar (°C)</th>";
            echo "<th>Temperatura do Bulbo Úmido (°C)</th>";
            echo "<th>Umidade do Ar (%)</th>";
            echo "<th>Intervalo da Chuva (mm)</th>";
            echo "<th>Total de Chuva (mm)</th>";
            echo "<th>Tipo de Precipitação</th>";
            echo "<th>Direção do Vento (graus)</th>";
            echo "<th>Velocidade do Vento (m/s)</th>";
            echo "<th>Velocidade Máxima do Vento (m/s)</th>";
            echo "<th>Pressão Barométrica (hPa)</th>";
            echo "<th>Radiação Solar (W/m²)</th>";
            echo "<th>Posição dos Sensores (graus)</th>";
            echo "<th>Bateria dos Sensores (%)</th>";
            echo "</tr>";
            while ($row = pg_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . ($row['station_name'] ?? 'N/A') . "</td>";
            echo "<td>" . (isset($row['measurement_timestamp']) ? date('d/m/Y H:i', strtotime($row['measurement_timestamp'])) : 'N/A') . "</td>";
            echo "<td>" . ($row['air_temperature'] ?? 'N/A') . "</td>";
            echo "<td>" . ($row['wet_bulb_temperature'] ?? 'N/A') . "</td>";
            echo "<td>" . ($row['humidity'] ?? 'N/A') . "</td>";
            echo "<td>" . ($row['rain_intensity'] ?? 'N/A') . "</td>";
            echo "<td>" . ($row['interval_rain'] ?? 'N/A') . "</td>";
            echo "<td>" . ($row['total_rain'] ?? 'N/A') . "</td>";
            echo "<td>" . ($row['wind_direction'] ?? 'N/A') . "</td>";
            echo "<td>" . ($row['wind_speed'] ?? 'N/A') . "</td>";
            echo "<td>" . ($row['maximum_wind_speed'] ?? 'N/A') . "</td>";
            echo "<td>" . ($row['barometric_pressure'] ?? 'N/A') . "</td>";
            echo "<td>" . ($row['solar_radiation'] ?? 'N/A') . "</td>";
            echo "<td>" . ($row['heading'] ?? 'N/A') . "</td>";
            echo "<td>" . ($row['battery_life'] ?? 'N/A') . "</td>";
            
            echo "</tr>";
            }
            echo "</table>";
            } else {
            echo "<p>Nenhum dado encontrado para o local selecionado nas últimas 24 horas.</p>";
            }
            pg_free_result($result);
            pg_close($dbconn);
        }
        ?>

    <script>
	    $( function() {
	    $( "#datepicker" ).datepicker({ dateFormat: 'yy-mm-dd' });
	    } );
    </script>

<script type="text/javascript">
  google.charts.load('current', {'packages':['corechart']});
  google.charts.setOnLoadCallback(drawVisualization);

  function drawVisualization() {
    // Some raw data (not necessarily accurate)
    var data = google.visualization.arrayToDataTable([
      ['Celsius', 'Temperatura do Ar', 'Temperatura do Bulbo Úmido'],

      <?php 
        $db_host = getenv('DB_HOST');
        $db_name = getenv('DB_NAME');
        $db_user = getenv('DB_USER');
        $db_pass = getenv('DB_PASS');
        $dbconn = pg_connect("host=$db_host dbname=$db_name user=$db_user password=$db_pass") or die('Não foi possível conectar: ' . pg_last_error());
        $query = "SELECT measurement_timestamp,air_temperature,wet_bulb_temperature, station_name,to_char(measurement_timestamp, 'HH:MI') AS hour FROM beach_weather_stations WHERE measurement_timestamp::date = CURRENT_DATE ORDER BY measurement_timestamp DESC";
        $result = pg_query($query) or die('A consulta falhou: ' . pg_last_error());

        while ($row = pg_fetch_assoc($result)) {
            $station_name = $row['station_name'];
            $measurement_timestamp = $row['hour'];
            $air_temperature = $row['air_temperature'];
            $wet_bulb_temperature = $row['wet_bulb_temperature'] ?? 0;
            echo "['$measurement_timestamp', $air_temperature, $wet_bulb_temperature],";
        };
        pg_free_result($result);
        pg_close($dbconn);
      ?>
      
    ]);

    var options = {
      title : 'Temperatura do ar nas últimas 24 horas',
      vAxis: {title: 'Temperatura (°C)'},
      hAxis: {title: 'Horário'},
      seriesType: 'bars',
      series: {5: {type: 'line'}}
    };

    var chart = new google.visualization.ComboChart(document.getElementById('tempChart'));
    chart.draw(data, options);
  }
</script>


</body>
</html>
        