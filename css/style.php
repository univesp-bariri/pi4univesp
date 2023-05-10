<?php
/*** set the content type header ***/
/*** Without this header, it wont work ***/
header("Content-type: text/css");


$font_family = 'Arial, Helvetica, sans-serif';
$font_size = '0.7em';
$border = '1px solid';
?>

body {
    margin: 0;
    font-family: "Work Sans", Arial, sans-serif;
    font-weight: 400;
    font-size: 16px;
    line-height: 1.7;
    color: #828282;
    background: #fff;
}


table {
    border-collapse: collapse;
    border-radius: 10px;
    border-style: solid;
    width: 100%;
    border-color: #fff;
    margin: 0 auto;
    font-family: 'Roboto', sans-serif;
    color: #444444;
    font-size: 14px;
    text-align: left;
  }

  table thead th {
    padding: 10px;
    font-weight: bold;
    background-color: #2980b9;
    color: #fff;
    border: 1px solid #ddd;
  }

  table th{
    background-color: #8161d3;;
    color: #fff;
  }

  table tbody tr:nth-child(even) {
    background-color: #f2f2f2;
  }

  table tbody tr:hover {
    background-color: #ddd;
  }

  table td, table th {
    padding: 10px;
    border: 1px solid #ddd;
  }

  table td {
    text-align: center;
  }
  
  .cover {
    display: table;
    width: 100%;
    height: 10em;
    margin: 0px;
    border: 0;
    padding: 100px 0;
    text-align: center;
    background: linear-gradient(to right, rgb(195 38 38 / 50%) 0%, rgb(255 42 71 / 60%) 26%, #7e455d 100%), url(../img/oak-street-beach.jpg) no-repeat center scroll;
    background-size: cover;
  }
  
  .search {
  width: 100%;
  position: relative;
  display: flex;
  margin-top: 60px;
}

.searchTerm {
  width: 220px;
  border: 3px solid #00B4CC;
  border-right: none;
  padding: 5px;
  height: 25px;
  border-radius: 5px 0 0 5px;
  outline: none;
  color: #404040;
}

.searchTerm:focus{
  color: #00B4CC;
}

.searchButton {
  height: 40px;
  border: 1px solid #00B4CC;
  background: #00B4CC;
  text-align: center;
  color: #fff;
  border-radius: 0 5px 5px 0;
  cursor: pointer;
  font-size: 20px;
}

/*Resize the wrap to see the search bar change!*/
.wrap{
  width: 30%;
  position: relative;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.select {
  width: 300px;
  border: 3px solid #00B4CC;
  height: 40px;
  border-radius: 5px;
  outline: none;
  color: #404040;
  padding: 5px;
  background: #FFF;
  margin-right: 8px;
}

#cover-h2 {
  color: #FFF;
  margin: 0;
  font-size: 1.5em;
  font-weight: bold;
}