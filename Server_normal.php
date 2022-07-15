<?php
// set some variables
$host = "localhost";
$port = 8000;
// don't timeout!
set_time_limit(10);
// create socket
$socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");
// bind socket to port
$result = socket_bind($socket, $host, $port) or die("Could not bind to socket\n");
// start listening for connections
$result = socket_listen($socket, 3) or die("Could not set up socket listener\n");

while (true) {
  // accept incoming connections
  // spawn another socket to handle communication
  $spawn = socket_accept($socket) or die("Could not accept incoming connection\n");
  echo ($spawn);
  // read client input
  $input = socket_read($spawn, 1024) or die("Could not read input\n");
  // clean up input string
  $input = trim($input);
  echo "\nClient Message : " . $input;
  $trama = explode(',', $input);

  $accion = trim($trama[0]);
  $id_unidad = trim($trama[1]);

  $respuesta = 'OK';

  if ($accion == '99') {
    # buscar en la base de datos si hay acciones para esta unidad
    if ($id_unidad == '20001') {
      $respuesta = 'REINICIAR';
    }

    if ($id_unidad == '20002') {
      $respuesta = 'ACTTUALIZAR';
    }
  }

  $msg = $respuesta;
  socket_write($spawn, $msg, strlen($msg));

  socket_close($spawn);
}
#socket_close($socket);
