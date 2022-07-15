import socket
import sys
import time

# Create a TCP/IP socket
sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)

# Connect the socket to the port where the server is listening
server_address = ('localhost', 8000)
print('connecting to {} port {}'.format(*server_address))
# abrir socket con el servidor
sock.connect(server_address)

try:

    # Send data
    message = b'99, 20001'
    
    for i in range(60):
      sock.sendall(message)
      data = sock.recv(1024)
      time.sleep(1)
      print(data)
    
    ## cerrar socket con el servidor
    sock.sendall(b'')

finally:
    print('closing socket')
    sock.close()