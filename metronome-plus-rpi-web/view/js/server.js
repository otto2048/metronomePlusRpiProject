// Socket setup based on tutorial by (Javascript.info, 2022): https://javascript.info/websocket

//constants for mqtt messaging
import * as constants from "constants.js";

//include required modules
const http = require('http');
const ws = require('ws');

const awsIot = require('aws-iot-device-sdk');

//server Thing certificate information
const ca = '';
const client = '';
const endpoint = '';
const key = '';
const cert = '';
const TOPIC = '';

const wss = new ws.Server({noServer: true});

//keep track of the clients
const clients = [];

var deviceConnected = false;

//create server
function accept(req, res) {
    // all incoming requests must be websockets
    if (!req.headers.upgrade || req.headers.upgrade.toLowerCase() != 'websocket') {
        res.end();
        return; 
    }

    // can be Connection: keep-alive, Upgrade
    if (!req.headers.connection.match(/\bupgrade\b/i)) {
        res.end();
        return;
    }

    wss.handleUpgrade(req, req.socket, Buffer.alloc(0), onConnect);
}

http.createServer(accept).listen(8080);

//set up mqtt device
var device = awsIot.device({
    caPath: ca,
    clientId: client,
    host: endpoint,
    keyPath: key,
    certPath: cert,
    topic: TOPIC
});

//subscribe to topic on connect
device.on('connect', function() {
    console.log("connect");
    deviceConnected = true;
    device.subscribe(TOPIC);
});

device.on('close', function() {
    deviceConnected = false;
});

function requestRpiConn(clientRpi)
{
    //request rpi connection
    device.publish(clientRpi, JSON.stringify({ message: {
        sender: constants.SERVER_SENDER,
        operation: constants.CONNECTION_OPERATION
    }}));
}

//SOURCE: Stack Overflow, 2022
//ACCESSED FROM: https://stackoverflow.com/questions/2956966/javascript-telling-setinterval-to-only-fire-x-amount-of-times
function setIntervalX(callback, timeoutCallback, delay, repetitions)
{
    var x = 0;
    var intervalID = setInterval(function () {


       if (x++ === repetitions) {
           clearInterval(intervalID);

           timeoutCallback();
       }

       callback();


    }, delay);

    
}

//handle connections
function onConnect(ws, req) {
    ws.on('message', function (message) {
        console.log("on message");
        const clientMsg = JSON.parse(message);

        console.log(req.socket.remoteAddress);

        if (clientMsg.operation == constants.CONNECTION_OPERATION)
        {
            //record client connection
            clients[req.socket.remoteAddress] = [{rpi : "rpi/" + clientMsg.location, websocket: ws, startTempo: clientMsg.value, rpiStatus: "empty"}];

            if (!deviceConnected)
            {
                //tell client that metronome connection isn't possible
                if (clients[req.socket.remoteAddress])
                {
                    client[0].websocket.send(JSON.stringify({ message: {
                        value: "failed",
                        operation: constants.CONNECTION_OPERATION
                    }}));
                }
            }
            else
            {
                var connectionTries = 30;
                setIntervalX(function()
                {
                    if (clients[req.socket.remoteAddress])
                    {
                        if (clients[req.socket.remoteAddress][0].rpiStatus != constants.RPI_CONNECTED_STATUS && clients[req.socket.remoteAddress][0].rpiStatus != constants.RPI_PLAYING_STATUS)
                        {
                            //request status of rpi device for client
                            requestRpiConn(clients[req.socket.remoteAddress][0].rpi);
                        }
                    }
                }, function()
                {
                    //tell client that metronome connection isn't possible
                    if (clients[req.socket.remoteAddress])
                    {
                        clients[req.socket.remoteAddress][0].websocket.send(JSON.stringify({ message: {
                            value: "failed",
                            operation: constants.CONNECTION_OPERATION
                        }}));
                    }
                }, 5000, connectionTries);


            }


        }

        if (deviceConnected)
        {
            //send input to iot core, to be sent to rpi device
            if (clientMsg.operation == constants.INPUT_OPERATION)
            {
                if (clients[req.socket.remoteAddress])
                {
                    if (clients[req.socket.remoteAddress][0].rpiStatus == constants.RPI_PLAYING_STATUS)
                    {
                        device.publish(clients[req.socket.remoteAddress][0].rpi, JSON.stringify({ message: {
                            sender: constants.SERVER_SENDER,
                            value: clientMsg.value,
                            operation: clientMsg.operation
                        }}));
                    } 
                    
                }
            }
        }
        
        
    });

    //when a clients connection is lost, make sure the metronome program on the rpi ends
    ws.on('close', function()
    {
        console.log(req.socket.remoteAddress + " lost");

        if (clients[req.socket.remoteAddress])
        {
            if (clients[req.socket.remoteAddress][0].rpiStatus == constants.RPI_PLAYING_STATUS)
            {
                device.publish(clients[req.socket.remoteAddress][0].rpi, JSON.stringify({ message: {
                    sender: constants.SERVER_SENDER,
                    value: "X",
                    operation: constants.INPUT_OPERATION
                }}));
            }

            delete clients[req.socket.remoteAddress];
        }
    });

    
}

//recieve mqtt message
device.on('message', function(topic, payload) {

    //json decode message
    message = JSON.parse(payload);

    console.log('message', topic, payload.toString());

    //if the rpi sent this message
    if (message.message.sender == constants.RPI_SENDER)
    {
        var values = Object.values(clients);

        //find out which client this message is for
        var client = values.find(doc => doc[0].rpi === topic);

        //deal with connection messages
        if (message.message.operation == constants.CONNECTION_OPERATION)
        {
            if (message.message.value == constants.RPI_CONNECTED_STATUS && client[0].rpiStatus != constants.RPI_PLAYING_STATUS)
            {
                //request rpi to start playing metronome
                device.publish(topic, JSON.stringify({ message: {
                    sender: constants.SERVER_SENDER,
                    value: client[0].startTempo,
                    operation: "PLAY"
                }}));
            }

            client[0].rpiStatus = message.message.value;
        }

        //send the message to the client via web socket
        if (client)
        {
            client[0].websocket.send(JSON.stringify(message));
        }
        
    }

});