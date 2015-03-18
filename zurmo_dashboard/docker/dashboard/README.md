## Minimal GUI showing container topology for our Zurmo demo

### Still work in progress!!!

### Main idea
* Use ectdctl watch -recursive to get notified of changes in the instance graph (new/dead containers)
* Use ectdctl ls to get the full tree representation of the updated instance graph (example of result in sample_tree.txt)
* Transform the tree into a graph using etcd_tree_to_graph.sh (example of result in graph.txt)
* Use node.js and websockets to push updates to graph.txt to a browser

### Node.js part
* I changed the example from here for reference: http://www.gianlucaguarini.com/blog/nodejs-and-a-simple-push-notification-server/
* You need to install nodejs
* npm install socket.io
* npm install xml2json
* You'll need to update gui_push.html to open a websocket to the right IP (replace 'localhost' in: var socket = io.connect('http://localhost:8000');)
* run the server with: node server.js
* See the gui accessing your server with a browser on port 8000
