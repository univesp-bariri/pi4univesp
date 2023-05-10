<!DOCTYPE html>
<html>
<head>
    <title>Dados Diários</title>
    <link rel="stylesheet" type="text/css" href="css/style.php">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

</head>
<body>

  <div class="cover">
    <img src="img/chicago.svg" alt="Logo">
    <p id="cover-h2">Beach Data Set</p>
    <div class="wrap">
      <form method="GET" action="">
        <div class="search">
          <select name="local" id="local" class="select" value="<?php echo $_POST['station_name']; ?>">
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
          
          <input type="text" name="date" class="searchTerm" id="datepicker" autocomplete="off" placeholder="Escolha a data">

          <button type="submit" name="search" class="searchButton" id='search'>Buscar</button>
        </div>
      </form>
    </div>
  </div>

<!------------------------------------------------------------------>
<!-------------------- Temperatura do Ar e Bulbo ------------------->
<!------------------------------------------------------------------>
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
<!------------------------------------------------------------------>
<!------------------------------------------------------------------>


<!------------------------------------------------------------------>
<!-------------------------- Umidade do Ar ------------------------->
<!------------------------------------------------------------------>
<script type="text/javascript">
  google.charts.load('current', {'packages':['corechart']});
  google.charts.setOnLoadCallback(drawVisualization);

  function drawVisualization() {
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
      vAxis: {title: 'Umidade do ar (%)'},
      hAxis: {title: 'Horário'},
      seriesType: 'scatter',

    };

    var chart = new google.visualization.ComboChart(document.getElementById('humAirChart'));
    chart.draw(data, options);

  }
</script>
<!------------------------------------------------------------------>
<!------------------------------------------------------------------>


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
      vAxis: {title: 'Intensidade da Chuva (mm)'},
      hAxis: {title: 'Horário'},
      curveType: 'function'
    };

    var chart = new google.visualization.LineChart(document.getElementById('intRainChart'));
    chart.draw(data, options);

  }

</script>

<!------------------------------------------------------------------>
<!------------------------------------------------------------------>

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
      vAxis: {title: 'Total de Chuva (mm)'},
      hAxis: {title: 'Horário'},
      curveType: 'function'
    };

    var chart = new google.visualization.LineChart(document.getElementById('totalRainChart'));
    chart.draw(data, options);

  }

</script>

<!------------------------------------------------------------------>
<!------------------------------------------------------------------>


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
    vAxis: {title: 'Graus (°)'},
    hAxis: { title: 'Horário', ticks: timestamps},
    format: 'h',
    colorAxis: {colors: ['yellow', 'red']},
    bubble: {textStyle: {fontSize: 11}}
    };


    var chart = new google.visualization.BubbleChart(document.getElementById('windDirecChart'));
    chart.draw(data, options);

  }

</script>
<!------------------------------------------------------------------>
<!------------------------------------------------------------------>

<!------------------------------------------------------------------>
<!---------- Velocidade do Vento e Velocidade Máxima do Vento ------>
<!------------------------------------------------------------------>

<script type="text/javascript">
  google.charts.load('current', {'packages':['corechart']});
  google.charts.setOnLoadCallback(drawVisualization);

  function drawVisualization() {
    // Some raw data (not necessarily accurate)
    var data = google.visualization.arrayToDataTable([
    
      ['m/s', 'Velocidade do Vento', 'Máxima Velocidade do Vento'],

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
      vAxis: {title: 'Velocidade (m/s)'},
      hAxis: {title: 'Horário'},
      seriesType: 'bars',
      series: {5: {type: 'line'}}
    };

    var chart = new google.visualization.ColumnChart(document.getElementById('windChart'));
    chart.draw(data, options);

  }

</script>

<!------------------------------------------------------------------>
<!------------------------------------------------------------------>

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
      vAxis: {title: 'Pressão (hPa)'},
      hAxis: {title: 'Horário'},
      seriesType: 'bars',
      series: {5: {type: 'line'}}
    };

    var chart = new google.visualization.ComboChart(document.getElementById('baroChart'));
    chart.draw(data, options);

  }

</script>

<!------------------------------------------------------------------>
<!------------------------------------------------------------------>

<!------------------------------------------------------------------>
<!------------------------------------------------------------------>

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
      vAxis: {title: 'Unives (W/m²)'},
      hAxis: {title: 'Horário'},
      isStacked: true
    };

    var chart = new google.visualization.SteppedAreaChart(document.getElementById('solarChart'));
    chart.draw(data, options);

  }

</script>

<!------------------------------------------------------------------>
<!------------------------------------------------------------------>

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
      vAxis: {title: 'Bateria (%)'},
      hAxis: {title: 'Horário'},
      seriesType: 'bars',
      series: {5: {type: 'line'}}
    };

    var chart = new google.visualization.ColumnChart(document.getElementById('batteryChart'));
    chart.draw(data, options);

  }

</script>

<!------------------------------------------------------------------>
<!------------------------------------------------------------------>

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

<script>
    if (window.location.href.endsWith("index.php")) {
    // Simulate button click
        var selectElement = document.getElementById('local');
        selectElement.selectedIndex = 1;
        //document.getElementById('search').click();
        //var urlPath = window.location.pathname;
        // Set flag to prevent further clicks
    } 
</script>

    <div id="tempChart" style="width: 900px; height: 500px;"></div>
    <div id="humAirChart" style="width: 900px; height: 500px;"></div>
    <div id="intRainChart" style="width: 900px; height: 500px;"></div>
    <div id="totalRainChart" style="width: 900px; height: 500px;"></div>
    <div id="windDirecChart" style="width: 900px; height: 500px;"></div>
    <div id="windChart" style="width: 900px; height: 500px;"></div>
    <div id="baroChart" style="width: 900px; height: 500px;"></div>
    <div id="solarChart" style="width: 900px; height: 500px;"></div>
    <div id="batteryChart" style="width: 900px; height: 500px;"></div>
    

</body>
</html>
        