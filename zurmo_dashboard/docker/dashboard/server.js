var app = require('http').createServer(handler),
  io = require('socket.io').listen(app),
  parser = new require('xml2json'),
  fs = require('fs');

// creating the server ( localhost:8000 )
app.listen(8000);

console.log('server listening on localhost:8000');

// on server started we can load our client.html page
function handler(req, res) {
  filename = req.url;
  if ( req.url == '/'){
    filename = '/gui_push.html';
  }
  console.log(filename);
  fs.readFile(__dirname + filename, function(err, data) {
    if (err) {
      console.log(err);
      res.writeHead(500);
      return res.end('Error loading file');
    }
    res.writeHead(200);
    res.end(data);
  });
}

// creating a new websocket to keep the content updated without any AJAX request
io.sockets.on('connection', function(socket) {
  console.log(__dirname);
  // watching the xml file
  fs.watchFile(__dirname + '/graph.txt', function(curr, prev) {
    // on file change we can read the new xml
    fs.readFile(__dirname + '/graph.txt', function(err, data) {
      if (err) throw err;
      // parsing the new xml data and converting them into json file
      //var json = parser.toJson(data);
      // send the new data to the client
      socket.volatile.emit('notification', data);
    });
  });

});
