<!DOCTYPE html>
<html>
<head>
    <title>Dados das últimas 24 horas</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript" src="js/data.js"></script>

</head>
<body>
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

<script type="text/javascript">
  google.charts.load('current', {'packages':['corechart']});
  google.charts.setOnLoadCallback(drawVisualization);
  var bulb = 'Temperatura do Bulbo Úmido'

  function drawVisualization() {
    // Some raw data (not necessarily accurate)
    var data = google.visualization.arrayToDataTable([
    
      ['Celsius', 'Temperatura do Ar', bulb],

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
                    $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,air_temperature,wet_bulb_temperature, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = '$local' AND date(measurement_timestamp) = '$date' ORDER BY measurement_timestamp";
                } elseif(!empty($local)) {
                    // consulta por local
                    $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,air_temperature,wet_bulb_temperature, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE station_name = '$local' AND measurement_timestamp::date = CURRENT_DATE ORDER BY measurement_timestamp";
                } elseif(!empty($date)) {
                    // consulta por data
                    $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,air_temperature,wet_bulb_temperature, station_name,to_char(measurement_timestamp, 'HH24h') AS hour  FROM beach_weather_stations WHERE date(measurement_timestamp) = '$date' ORDER BY measurement_timestamp";
                } else {
                    // consulta sem filtro
                    $query = "SELECT to_char(measurement_timestamp, 'DD/MM/YYYY') as date,air_temperature,wet_bulb_temperature, station_name,to_char(measurement_timestamp, 'HH24h') AS hour FROM beach_weather_stations WHERE measurement_timestamp::date = CURRENT_DATE AND station_name = 'Oak Street Weather Station' ORDER BY measurement_timestamp";
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

    <script>
	    $( function() {
	    $( "#datepicker" ).datepicker({ dateFormat: 'yy-mm-dd' });
	    } );
    </script>

    <?php
        if ($station_name === null) {
            echo '<h2>Nenhum dado encontrado para o local e dia selecionados.</h2>';
        } else {
            echo '<h2>Dados de ' . $station_name . ' no dia ' . $measurement_timestamp_date . '</h2>';
        }
    ?>

    <?php
        if ($wet_bulb_temperature === 0) {
            echo "<script> bulb = 'N/A'; </script>";
        }
    ?>

    <div id="tempChart" style="width: 900px; height: 500px;"></div>

</body>
</html>
        