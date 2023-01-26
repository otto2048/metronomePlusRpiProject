//nodejs program to launch metronome application as child process, get input from mqtt and send output to mqtt

//mqtt messaging constants
const PLAY_OPERATION = "PLAY";
const CONNECTION_OPERATION = "CONNECTION";
const INPUT_OPERATION = "INPUT";

const SERVER_SENDER = "server";
const RPI_SENDER = "rpi";

const RPI_CONNECTED_STATUS = "connected";
const RPI_PLAYING_STATUS = "playing";

//get aws sdk and child_process modules
const awsIot = require('aws-iot-device-sdk');
const spawn = require('child_process').spawn;

//rpi Thing certificate information
const ca = '';
const client = '';
const endpoint = '';
const key = '';
const cert = '';
const TOPIC = '';

var process;
var exited = true;
var deviceConnected = false;

//set up mqtt device
var device = awsIot.device({
    caPath: ca,
    clientId: client,
    host: endpoint,
    keyPath: key,
    certPath: cert,
    topic: TOPIC,
});

//subscribe to topic on connect
device.on('connect', function() {
    console.log('connect');
    device.subscribe(TOPIC);
    deviceConnected = true;
});

device.on('close', function() {
    deviceConnected = false;
});

device.on('message', function(topic, payload) {
    //json decode message
    message = JSON.parse(payload);

    //check if the server sent this message
    if (message.message.sender == SERVER_SENDER)
    {
        //if the operation is PLAY and the metronome application isn't running, launch the process
        if (message.message.operation == PLAY_OPERATION && exited)
        {
            process = spawn('./metronomeApplication', ['23', message.message.value]);

            exited = false;

            //send output to iot core
            process.stdout.on('data', function (data) {
                console.log('stdout: ' + data.toString());
                device.publish(TOPIC, JSON.stringify({ message: {
                    sender: RPI_SENDER,
                    value: data.toString()
                }}));
            });

            process.stderr.on('data', function (data) {
                console.log('stderr: ' + data.toString());
            });

            process.on('exit', function (code) {
                var data = 'Program exited with code ' + code.toString();
                exited = true;
                console.log(data);
            });

            //publish that the metronome application process has started
            device.publish(TOPIC, JSON.stringify({ message: {
                sender: RPI_SENDER,
                operation: CONNECTION_OPERATION,
                value: RPI_PLAYING_STATUS
            }}));
        }
        //if this is an INPUT operation, give input to the metronome application
        else if (message.message.operation == INPUT_OPERATION)
        {
            if (process)
            {
                //give this input to the child process
                process.stdin.write(message.message.value + "\n");
            }
        }
        //if this is a CONNECTION request
        else if (message.message.operation == CONNECTION_OPERATION)
        {
            //if this device is subscribed to its topic, publish that this device is connected
            if (deviceConnected)
            {
                device.publish(TOPIC, JSON.stringify({ message: {
                    sender: RPI_SENDER,
                    operation: CONNECTION_OPERATION,
                    value: RPI_CONNECTED_STATUS
                }}));
            }
        }
    }

    console.log('message', topic, payload.toString());
});