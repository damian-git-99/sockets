<?php

do {

	try {

		$address = "localhost";
		$port = 8100;
		$connectionTime = 60;
		$timeout = array();


		/* Establecer el límite de tiempo para que se ejecute el script. */
		set_time_limit(0);
		ob_implicit_flush();
		/* Crear un socket y vincularlo al puerto. */

		if (!extension_loaded('sockets')) {
			echo "die\n";
		}

		if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
			echo "socket_create() fall�: raz�n: " . socket_strerror(socket_last_error()) . "\n";
		}

		if (socket_bind($sock, $address, $port) === false) {
			echo "socket_bind() fall�: raz�n: " . socket_strerror(socket_last_error($sock)) . "\n";
		}

		if (socket_listen($sock, 500) === false) {
			echo "socket_listen() fall�: raz�n: " . socket_strerror(socket_last_error($sock)) . "\n";
		}

		//clients array
		$clients = array();
		echo "Inicia: " . date('d-M-Y H:i:s') . "\n";

		do {

			$read = array();
			// Clientes
			$read[] = $sock;
			// mezcla los clientes que ya existian con los que se crean nuevos, y tambien mete aqui el socket del servidor
			$read = array_merge($read, $clients);
			$write = NULL;
			$except = NULL;

			/* Comprobando si hay datos para leer desde el zócalo. */
			if (socket_select($read, $write, $except, $tv_sec = 2) < 1) {
				#echo 'entro';
				//echo "socket_select \n";
				//    SocketServer::debug("Problem blocking socket_select?");
				#echo " socket_select(".socket_last_error($sock).") " . socket_strerror(socket_last_error($sock)) . "\n";
				continue;
			}

			#print_r($read);
			#print_r($sock);
			/* Comprobando si el socket está aceptando la conexión. */

			if (in_array($sock, $read)) {
				if (($msgsock = socket_accept($sock)) === false) {
					echo "socket_accept() fall�: raz�n: " . socket_strerror(socket_last_error($sock)) . "\n";
					break;
				} else {
					echo "socket_accept(" . socket_last_error($sock) . ") " . socket_strerror(socket_last_error($sock)) . "\n";
				}

				$clients[] = $msgsock;
				$key = array_keys($clients, $msgsock);;
				$timeout[$key[0]] = time();
				echo "     " . date('d-M-Y H:i:s') . "                                            " . sizeof($timeout) . "\n";
			}

			$mensaje_anterior;
			foreach ($clients as $key => $client) { // for each client 
				#system('clear');
				echo "Conexiones: " . sizeof($timeout). "\n";
				#echo 'Clientes conectados actualmente: ' . count($clients) . "\n";
				/* Comprobando si el cliente todavía está conectado. */
				if (($timeout[$key] > 0) and (time() - $timeout[$key] > $connectionTime)) {
					print "Cerrar Cliente: (" . $key . ") ";
					socket_close($client);
					unset($clients[$key]);
					unset($timeout[$key]);
				}

				/* Comprobando si el cliente está en el array de clientes. */
				if (in_array($client, $read)) {

					/* Comprobando si el cliente todavía está conectado. */
					if (isset($client)) {
						if (true === ($buf = socket_read($client, 4096))) {
							echo date('d-M-Y H:i:s') . "  Socket_read() fall�: raz�n: " . socket_strerror(socket_last_error($client)) . "\n";
							$buf = ".";
						}
					}
	
					// el cliente ya no mando datos, con lo cual lo desconectamos
					if (trim($buf) == '') {
						socket_close($client);
						unset($clients[$key]);
						unset($timeout[$key]);
						break;
					}

					/* Comprobando si el búfer está vacío. Si está vacío, continuará con la siguiente iteración. */
					if (!$buf = trim($buf)) {
						continue;
					}

					/* Cerrando la conexión. */
					if ($buf == 'quit') {
						print "Cerrar Cliente: (" . $key . ") ";
						unset($clients[$key]);
						unset($timeout[$key]);
						socket_close($client);
						echo "Conexiones: " . sizeof($timeout) . "\n";
						break;
					}

					$registro = explode(",", $buf);
					echo $buf . "\n";
					$timeout[$key] = time();

					// clean up input string
					$buf = trim($buf);
					$trama = "";

					if (strpos($buf, '|') == true) {
						$trama = explode('|', $buf);
					} else {
						$trama = explode(',', $buf);
					}

					$accion = trim($trama[0]);

					$respuesta = 'OK';
					#          0   ,    1    ,  2 ,   3   ,    4   ,    5
					#Trama = Accion,Id_Unidad,Hora,Latitud,Longitud,Geocerca_x
					#Accion 0 - 
					#Accion 1 - 
					#Accion 2 - Registrar la hora de llegada en la geocerca
					#Accion 3 - Rastrear en tiempo real y el recorrido del camion
					#Accion 4 - No hay conexion del gps
					#Accion 5 - Registro de asistencia del trabajador subida * Trama = Accion, id_pasajero, fecha, hora,velocida
					#Accion 6 - Registro de asistencia del trabajador  bajada * Trama = Accion, id_pasajero, fecha, hora,velocida
					#Accion 7 - folios finales 
					#Accion 8 - autoasignacion
					#Accion 99 - El validador Responde que le llego la accion (Reiniciar, Actualizar, Asignacion, etc)

					# validaciones del tipo de accion que realiza el validador
					if ($accion == '3') {
						# buscar en la base de datos si hay acciones para esta unidad
						$folio_geolocalizacion = trim($trama[1]);
						$folio_viaje = trim($trama[2]);
						$id_unidad = trim($trama[3]);
						$fecha = trim($trama[4]);
						$hora = trim($trama[5]);
						$latitud = trim($trama[6]);
						$longitud = trim($trama[7]);
						$velocidad = trim($trama[8]);
						$geocerca = trim($trama[9]);

						socket_write($client, $respuesta, strlen($respuesta));
						break;
					}

					if ($accion == '4') {
						# buscar en la base de datos si hay acciones para esta unidad
						$folio_geolocalizacion = trim($trama[1]);
						$folio_viaje = trim($trama[2]);
						$id_unidad = trim($trama[3]);
						$fecha = trim($trama[4]);
						$hora = trim($trama[5]);

						$respuesta = 'OK';
						$id = -1;

						socket_write($client, $respuesta, strlen($respuesta));
						break;
					}

					if ($accion == '5') {
						$folio_asistencia = trim($trama[1]);
						$folio_viaje = trim($trama[2]);
						$id_pasajero = trim($trama[3]);
						$fecha = trim($trama[4]);
						$hora = trim($trama[5]);
						$latitud = trim($trama[6]);
						$longitud = trim($trama[7]);
						$velocidad = trim($trama[8]);
						$geocerca = trim($trama[9]);
						socket_write($client, $respuesta, strlen($respuesta));
						break;
					}

					if ($accion == '6') {
						$folio_asistencia = trim($trama[1]);
						$folio_viaje = trim($trama[2]);
						$id_pasajero = trim($trama[3]);
						$fecha = trim($trama[4]);
						$hora = trim($trama[5]);
						$latitud = trim($trama[6]);
						$longitud = trim($trama[7]);
						$velocidad = trim($trama[8]);
						$geocerca = trim($trama[9]);
						socket_write($client, $respuesta, strlen($respuesta));
						break;
					}

					if ($accion == '7') {
						$id_unidad = trim($trama[1]);
						$folio_final_geolo = trim($trama[2]);
						$folio_final_asignacion = trim($trama[3]);
						$folio_final_asistencia = trim($trama[4]);
						socket_write($client, $respuesta, strlen($respuesta));
						break;
					}

					if ($accion == '8') {
						$id_unidad = trim($trama[2]);
						$id_operador = trim($trama[3]);
						$pass = trim($trama[4]);
						$id_ruta = trim($trama[5]);
						$fecha = trim($trama[6]);
						$hora_de_inicio = trim($trama[7]);
						$respuesta = '202207132001';
						socket_write($client, $respuesta, strlen($respuesta));
						break;
					}

					if ($accion == '13') {
						array_shift($trama);

						foreach ($trama as $value) {
							$values = explode(',', $value);
							$folio_geolocalizacion = trim($trama[1]);
							$id_unidad = trim($trama[2]);
							$fecha = trim($trama[3]);
							$hora = trim($trama[4]);
							$latitud = trim($trama[5]);
							$longitud = trim($trama[6]);
							$velocidad = trim($trama[7]);
							$geocerca = trim($trama[8]);
						}
					}


					if ($accion == '14') {
						array_shift($trama);

						foreach ($trama as $value) {
							$values = explode(',', $value);
							$folio_geolocalizacion = trim($trama[1]);
							$id_unidad = trim($trama[2]);
							$fecha = trim($trama[3]);
							$hora = trim($trama[4]);
							$latitud = trim($trama[5]);
							$longitud = trim($trama[6]);
							$velocidad = trim($trama[7]);
							$geocerca = trim($trama[8]);
						}
					}


					/* Actualización de la base de datos. */
					if ($accion == '99') {
						$id_unidad = trim($trama[1]);
						$sql = "UPDATE Actualizacion SET check_operacion = 1, fecha_operacion = GETDATE()  WHERE id_unidad = " . $id_unidad;
						/* Ejecutando la consulta. */
					}
					# dasdsaads
					socket_write($client, $respuesta, strlen($respuesta));
				}
			}
			
			
		} while (true);
		socket_close($sock);
	} catch (\Exception $e) {
		echo "\n";
		echo $e->getMessage() . " catch out\n";
	}
} while (true);
