// Socket setup based on tutorial by (Javascript.info, 2022): https://javascript.info/websocket

//constants for mqtt messaging
import * as constants from "./constants.js";

//constants to send as input to metronome
const playString = "P";
const upTempoString = "U";
const downTempoString = "D";
const upTenTempoString = "U10";
const downTenTempoString = "D10";
const playingString = "playing";
const pausedString = "paused";

//metronome buttons
var playBtn = document.getElementById("play-btn");
var upTempoBtn = document.getElementById("up-btn");
var downTempoBtn = document.getElementById("down-btn");
var upTenTempoBtn = document.getElementById("up-10-btn");
var downTenTempoBtn = document.getElementById("down-10-btn");

//client information
var Uname;
var PiClientId;

var socket;

var connected = false;

window.onload = preparePage();

$(document).ready(function(){
    $("#load-metronome-modal").modal('show');
});

function preparePage()
{
    //get user details from SESSION
    getUserDetails();

    //connect to WSS
    socket = new WebSocket("");

    //set up socket
    socket.onopen = function(e) {
        console.log("Connection established");

        //request starting metronome
        var obj = new Object();
        obj.operation = constants.CONNECTION_OPERATION;
        obj.value = document.getElementById("bpm-value").innerText;
        obj.location = PiClientId;

        socket.send(JSON.stringify(obj));
    };

    socket.onmessage = function(event) {
        //get message
        var message = JSON.parse(event.data);
        console.log(message);

        //check the operation of the message
        if (message.message.operation == constants.CONNECTION_OPERATION)
        {
            //check if we can use metronome yet
            if (message.message.value == "playing")
            {
                //enable buttons
                playBtn.disabled = false;
                playBtn.ariaDisabled = false;
                upTempoBtn.disabled = false;
                upTempoBtn.ariaDisabled = false;
                downTempoBtn.disabled = false;
                downTempoBtn.ariaDisabled = false;
                upTenTempoBtn.disabled = false;
                upTenTempoBtn.ariaDisabled = false;
                downTenTempoBtn.disabled = false;
                downTenTempoBtn.ariaDisabled = false;

                connected = true;

                $("#load-metronome-modal").modal('hide');
            }
            else
            {
                //output that connection isn't possible
                document.getElementById("metronome-load-status").innerHTML = message.message.value;

                document.getElementById("connecting").removeChild(document.getElementById("spinner"));
            }
        }
        else
        {
            //check if message is a number (if number, is a bpm)
            if (!isNaN(message.message.value))
            {
                //update bpm
                document.getElementById("bpm-value").innerHTML = message.message.value;

                //update save link
                var saveBtn = document.getElementById("save-btn");

                if (saveBtn)
                {
                    saveBtn.setAttribute("href", "createSong.php?currentTempo="+message.message.value);
                }
            }
            else if (message.message.value.trim() == pausedString)
            {
                //change play/pause symbol
                document.getElementById("play-pause-btn").classList = "mdi mdi-play";

            }
            else if (message.message.value.trim() == playingString)
            {
                //change play/pause symbol
                document.getElementById("play-pause-btn").classList = "mdi mdi-pause";

            }
        }

        
    };
    
    //add event listener to play button
    playBtn.addEventListener("click", function()
    {
        if (connected)
        {
            sendInput(playString);
        }
    });

    //add event listener to minus button
    downTempoBtn.addEventListener("click", function()
    {
        if (connected)
        {
            sendInput(downTempoString);
        }
    });

    //add event listener to plus button
    upTempoBtn.addEventListener("click", function()
    {
        if (connected)
        {
            sendInput(upTempoString);
        }
    });

    //add event listener to minus 10 button
    downTenTempoBtn.addEventListener("click", function()
    {
        if (connected)
        {
            sendInput(downTenTempoString);
        }
    });

    //add event listener to plus 10 button
    upTenTempoBtn.addEventListener("click", function()
    {
        if (connected)
        {
            sendInput(upTenTempoString);
        }
    });

    //add event listener to load button (if its there)
    var loadBtn = document.getElementById("load-btn");

    if (loadBtn)
    {
        loadBtn.addEventListener("click", loadModal);
    }

    //add event listener to save tempo button (if its there)
    var saveTempoBtn = document.getElementById("save-tempo-btn");

    if (saveTempoBtn)
    {
        saveTempoBtn.addEventListener("click", saveTempo);
    }
}

//tell socket that we want to send some input to the program
function sendInput(input)
{
    var obj = new Object();
    obj.operation = constants.INPUT_OPERATION;
    obj.value = input;
    socket.send(JSON.stringify(obj));
}

function getUserDetails()
{
    $.ajax({
        url: "../controller/ajaxScripts/getSessionData.php",
        async: false,
        type: "POST",
        dataType: 'json',
        success: function(result)
        {
            Uname = result.Uname;
            PiClientId = result.PiClientId;

            console.log(Uname);
            console.log(PiClientId);
        }
    });
}

function loadModal()
{
    //load songs
    $.ajax({
        url: "../controller/ajaxScripts/getSongs.php",
        type: "POST",
        dataType: 'json',
        success: function(result)
        {
            var songsDiv = document.createElement("div");

            if (result == 0)
            {
                //output failed message
                var message = document.createElement("p");

                message.innerHTML = "Failed to load songs";

                songsDiv.appendChild(message);
            }
            else
            {
                //add songs into modal
                for (var i = 0; i < result.length; i++)
                {
                    //elements
                    var row = document.createElement("div");
                    var buttonDiv = document.createElement("div");
                    var title = document.createElement("p");
                    var loadBtn = document.createElement("a");

                    //row attributes
                    row.classList = "row pt-2 pb-2 align-items-center border-bottom";

                    if (i == 0)
                    {
                        row.classList.add("border-top");
                    }

                    //button attributes
                    loadBtn.innerHTML = "Load";
                    loadBtn.classList = "btn btn-dark float-end";
                    loadBtn.role = "button";
                    loadBtn.href = "index.php?song="+result[i]["SongTitle"];

                    //title attributes
                    title.innerHTML = result[i]["SongTitle"];
                    title.classList = "col m-0";

                    //button div attributes
                    buttonDiv.classList = "col";

                    //append
                    buttonDiv.appendChild(loadBtn);

                    row.appendChild(title);
                    row.appendChild(buttonDiv);
                    
                    songsDiv.appendChild(row);
                }
            }

            //add div to modal body
            var modalBody = document.getElementById("modal-body");
            modalBody.replaceChildren(songsDiv);

            //show modal
            $("#load-song-modal").modal("show");
        }
    });
}

function saveTempo()
{
    //save tempo with ajax

    //append new tempo into tempos list
    $.ajax({
        url: "../controller/ajaxScripts/saveTempo.php",
        type: "GET",
        data: {title: document.getElementById("song-title").textContent.trim(), value: document.getElementById("bpm-value").textContent.trim()},
        success: function(result)
        {
            if (result != 0)
            {
                //append new tempo into tempos list
                var li = document.createElement("li");
                li.classList = "list-group-item border-dark";
                li.innerHTML =  document.getElementById("bpm-value").textContent.trim();

                document.getElementById("tempos-list").insertBefore(li, document.getElementById("tempos-list").children[1]);
            }
        }
    });
}