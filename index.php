<?php

/*

REQUIREMENTS

* A custom slash command on a Slack team
* A web server running PHP5 with cURL enabled

USAGE

* Place this script on a server running PHP5 with cURL.
* Set up a new custom slash command on your Slack team:
  http://my.slack.com/services/new/slash-commands
* Under "Choose a command", enter whatever you want for
  the command. /isitup is easy to remember.
* Under "URL", enter the URL for the script on your server.
* Leave "Method" set to "Post".
* Decide whether you want this command to show in the
  autocomplete list for slash commands.
* If you do, enter a short description and usage hint.

*/
session_start();

$Unknown = 0;
$Out = 1;
$Low = 2;
$In = 3;

# Grab some of the values from the slash command, create vars for post back to Slack
$command = $_POST['command'];
$text = $_POST['text'];
$token = $_POST['token'];

# Check the token and make sure the request is from our team
if($token != getenv("SLACK_TOKEN")){ #replace this with the token from your slash command configuration page
  $msg = "The token for the slash command doesn't match. Check your script.";
  die($msg);
  echo $msg;
}

$snacks = $Unknown;

$file = 'snack.txt';
if (!file_exists($file)) {
        touch($file);
}
$handle = fopen($file, "r+");
if(flock($handle, LOCK_EX)) {
    $size = filesize($file);
    $snacks = $size === 0 ? 0 : fread($handle, $size);
    flock($handle, LOCK_UN);
}

if($text == "status")
{
        if($snacks == $In)
        {
            $reply = "Yay! Snacks everywhere!";
        } else if ($snacks == $Low) {
            $reply = "We are running low... let Karie know before it's too late..";
        } else if ($snacks == $Out) {
            $reply = "We're out... Should I file a bug?";
        } else {
            $reply = "404: Snacks not found";
        }
} else {
    $status = $Unknown;
    $reply = "404: Snacks not found";
    if($text == "in")
    {
        $status = $In;
        $reply = "Fresh snacks delivery!";
    } else if ($text == "low") {
        $status = $Low;
        $reply = "Holy cow! Someone shoud call Karie!!";
    } else if ($text == "out") {
        $status = $Out;
        $reply = "Argh. No more snacks";
    }
    if(flock($handle, LOCK_EX)) {
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, $status);
        flock($handle, LOCK_UN);
    }
}

# Send the reply back to the user.
echo $reply;
