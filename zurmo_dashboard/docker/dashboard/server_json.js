var app = require('http').createServer(handler),
  io = require('socket.io').listen(app),
  parser = new require('xml2json'),
  fs = require('fs');
require('string.prototype.endswith');

var etcd_host = '127.0.0.1'
var etcd_peers=process.env.ETCDCTL_PEERS;
if (etcd_peers != null){
    etcd_host = etcd_peers.substring(0,etcd_peers.indexOf(':'));
}
console.log("Using etcd_host: " + etcd_host);

// creating the server ( localhost:8000 )
app.listen(8000);
console.log('server listening on localhost:8000');

var http = require('http');
var json_topology;

var ls_options = {
  host: etcd_host,
  port: '4001',
  path: '/v2/keys/services/?recursive=true'
};
ls_callback = function(response) {
  var str = '';

  response.on('data', function (chunk) {
    str += chunk;
  });

  response.on('end', function () {
    // convert to json     
    json_resp = JSON.parse(str);
    // transform into json topology for graph
    //console.log(json_resp);
    try {
        json_topology = generate_json_topology(json_resp);
        console.log("updated topology");
        // notify websocket so that change is pushed to clients
        io.emit('notification', { for: 'everyone' });    
        console.log("notification sent");
    } catch (err) {
        console.log(err)
        console.log("failed parsing etcd json, probably inconsistent, will retry");
    }
    // start watching again
    http.request(watch_options, watch_callback).end();
  });

  response.on('error', function(err) {
      sys.debug('unable to connect to etcd: connection error');
  });
}

//The url we want is: '/v2/keys/services/?recursive=true&wait=true'
var watch_options = {
  host: etcd_host,
  port: '4001',
  path: '/v2/keys/services/?recursive=true&wait=true'
};
watch_callback = function(response) {
  var str = '';

   response.on('data', function (chunk) {
        str += chunk;
      });

  response.on('end', function () {
    // convert to json     
    json_resp = JSON.parse(str);
    // check if its just a TTL change
    //console.log(json_resp);
    // actually we just check if it's an update
    if ( json_resp.action == "update"){
      // if it is, just repeat request
      http.request(watch_options, watch_callback).end();
    } else if ( json_resp.action == "set" && json_resp.prevNode != null && json_resp.node != null && json_resp.node.value == json_resp.prevNode.value){
            // if it's a set operation check if value has changed
            //just repeat request
            http.request(watch_options, watch_callback).end();       
    } else {
        // if it's not get the whole etcd service tree
        console.log("etcd service tree updated");
        var d = require('domain').create();
        d.on('error', function(err){
            // TODO: handle this and print a message on screen
            // handle the error safely
            console.log(err);
            console.log("failed parsing JSON response");
            // retry
            http.request(ls_options, ls_callback).end();
        })

        // catch the uncaught errors in this asynchronous or synchronous code block
        d.run(function(){
            // the asynchronous or synchronous code that we want to catch thrown errors on
            http.request(ls_options, ls_callback).end();
            console.log("etcd service tree parsed");
        })
        
    }
  });

  response.on('error', function(err) {
     sys.debug('unable to connect to etcd: watch');
  });
}

var d = require('domain').create();
 

d.on('error', function(err){
    // this means we cannot connect to etcd
    // TODO: handle this and print a message on screen
    // handle the error safely
    console.log(err)
    console.log("cannot connect to etcd")
})

// catch the uncaught errors in this asynchronous or synchronous code block
d.run(function(){
    // the asynchronous or synchronous code that we want to catch thrown errors on
    // generate initial topology json file
    http.request(ls_options, ls_callback).end();   
})


function generate_json_topology(etcd) {
    // start with empty topology
    json = {};
    hosts_by_id = {};
    nodes_array = [];
    nodes = {};
    types = {};
    
    for(var i=0;i<etcd.node.nodes.length;i++){
        ntype = etcd.node.nodes[i];
        type = ntype.key.substring(10);
        types[type] = [];
        //console.log(ntype);
        //for (var ntype in etcd.node.nodes){
        for(var j=0;j<ntype.nodes.length;j++){
            comp = ntype.nodes[j];
            //console.log("comp", comp);
            // create json node for component
            node = {fullName: comp.key, group: i, type: ntype.key.substring(10)};
            // add host if present
            if ( comp.nodes != null){
                for (var k in comp.nodes){
                    if (comp.nodes[k].key.endsWith('host')){
                        node.host = comp.nodes[k].value;
                        hosts_by_id[comp.nodes[k].value] = 1;
                    } 
                }
            }
            types[type].push(node.fullName);
            nodes[node.fullName] = node;
            nodes_array.push(node.fullName);
            
        } 
    }
    json.types = types;
    json.components = nodes;
    json.nodes_array = nodes_array;
    json.hosts = Object.keys(hosts_by_id).sort();
    //console.log(json.components);
    res = adjust_for_zurmo(json);
    return JSON.stringify(res);
}

function adjust_for_zurmo(json){
    // zurmo specific code   
    //console.log(json.types);    
    // TODO: add component links in etcd
    // convert into format that D3 can understand
    json.vms = json.hosts.map(function(d, i) { return { name: "vm" + d.substring(26,28) }; });    
    json.nodes = [];
    var i = 0;
    for(var key in json.components){
            node = json.components[key];            
            node.position = i;
            node.name = node.type;            
            if ( node.vm == null && json.hosts.indexOf(node.host) != -1){
                node.vm = json.hosts.indexOf(node.host);
            } else {
                // FIXME: we're assigning to first VM if host is not there
                node.vm = 0;
            }
            json.nodes.push(node);
            i++;
    }
    //console.log(json.nodes);
    //console.log(json.components);
    json.links = [];
    for(var key in json.types['webserver']){
        for (var j=0; j<json.types['loadbalancer'].length; j++){
            link = {"source": json.components[json.types['loadbalancer'][j]].position, "target": json.components[json.types['webserver'][key]].position,  "value": 1 };
            json.links.push(link);
        }
        link = {"source": json.components[json.types['webserver'][key]].position, "target": json.components[json.types['database'][0]].position, "value": 1 };
        json.links.push(link);
        for (var i=0; i<json.types['cache'].length; i++){
            link = {"source": json.components[json.types['webserver'][key]].position, "target": json.components[json.types['cache'][i]].position, "value": 1 };
            json.links.push(link);
        }        
    }
    //console.log(json.links);
    return json;
}


// on server started we can load our client.html page
function handler(req, res) {
  filename = req.url;
  if ( req.url == '/'){
    filename = '/gui_push_d3.html';
  } else if (req.url == '/topology.json') {    
    res.writeHead(200);
    res.end(json_topology);
    return;
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
  console.log('user connected to websocket');
});
