import socket
import sys
import time
HOST, PORT = "localhost", 9000
data = "99, 20004"

# Create a socket (SOCK_STREAM means a TCP socket)
with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as sock:
    # Connect to server and send data
    sock.connect((HOST, PORT))
    #sock.sendall(bytes(data + "\n", "utf-8"))

    for i in range(60):
        sock.sendall(bytes(data + "\n", "utf-8"))
        respuesta = sock.recv(1024)
        time.sleep(1)
        print(respuesta)
