import socket
import time

# Create a TCP/IP socket
#sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)

# Connect the socket to the port where the server is listening
server_address = ('localhost', 8100)
#server_address = ('localhost', 9000)
print('connecting to {} port {}'.format(*server_address))
# sock.connect(server_address)

try:
    sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    sock.connect(server_address)
    for i in range(60): # numero de envios
        # Send data
        message = ''' Hola mundo'''
        print('sending {!r}'.format(message))
        sock.sendall(bytes(message, 'utf-8'))
        respuesta = sock.recv(1024)
        respuesta = respuesta.decode('utf-8')
        print(respuesta)
        time.sleep(1)
finally:
    sock.sendall(bytes('quit', 'utf-8'))
    sock.close()
    pass