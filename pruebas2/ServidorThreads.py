from socketserver import ThreadingTCPServer, StreamRequestHandler

#https://docs.python.org/3/library/socketserver.html
#https://docs.python.org/3/library/socketserver.html#asynchronous-mixins
#https://stackoverflow.com/questions/61911301/handling-multiple-connections-in-python-with-sockets
class echohandler(StreamRequestHandler):
    def handle(self):
        print(f'Connected: {self.client_address[0]}:{self.client_address[1]}')
        while True:
            # self.rfile is a file-like object created by the handler;
            # we can now use e.g. readline() instead of raw recv() calls
            self.data = self.rfile.readline().strip()
            if not self.data:
                print(
                    f'Disconnected: {self.client_address[0]}:{self.client_address[1]}')
                break  # exits handler, framework closes socket
            print("{} wrote:".format(self.client_address[0]))
            print(self.data)
            # Likewise, self.wfile is a file-like object used to write back
            # to the client
            self.wfile.write(b'OK')


server = ThreadingTCPServer(('localhost', 9000), echohandler)
server.serve_forever()
