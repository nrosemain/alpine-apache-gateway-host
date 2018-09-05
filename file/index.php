<?php
$path       = '/work/vhost'; # change to suit your needs
$info = array(); $x=0;

if($_POST['create']){
    $servername = $_POST['servername'];
    $location = $_POST['location'];
    $linfo = $_POST['linfo'];
    $port = (!empty($_POST['port'])) ? $_POST['port'] : '80';

    $res = file_get_contents($path) . "\n\n";

    if(!empty($servername) && !empty($location) && !empty($linfo)){

        if($location === "localhost"){
            $res .= "<VirtualHost *:$port>\n";
            $res .= "    ServerName $servername\n";
            $res .= "    DocumentRoot \"$linfo\"\n";
            $res .= "    <Directory \"$linfo\">\n";
            $res .= "        Options Indexes FollowSymLinks\n";
            $res .= "        AllowOverride All\n";
            $res .= "        Require all granted\n";
            $res .= "    </Directory>\n";
            $res .= "</VirtualHost>\n";
            echo "lol";

            if(file_put_contents($path.'/'.$servername.'.conf', $res) == false)
                echo "error";

        }elseif ($location === "distant"){
            $res .= "<VirtualHost *:$port>\n";
            $res .= "    ServerName $servername\n";
            $res .= "    ProxyPass / http://$linfo/\n";
            $res .= "    ProxyPassReverse / http://$linfo/\n";
            $res .= "    ProxyPreserveHost On\n";
            $res .= "    ServerSignature Off\n";
            $res .= "</VirtualHost>\n";

            if(file_put_contents($path.'/'.$servername.'.conf', $res) == false)
                echo "error";
        }
    }
}

if($_POST['delete']){
    foreach ($_POST['select'] as $v){
        unlink($path.'/'.$v.'.conf');
    }
}

if($_POST['reboot']){
    shell_exec("echo 'reboot apache' > /run/script/command_php.txt");
}

$a_directory = scandir($path);
$a_conf_files = array_diff($a_directory, array('..', '.'));
foreach($a_conf_files as $conf_file){
    $Thisfile   = fopen($path .'/'.$conf_file, 'r') or false;

    if($Thisfile !== false){
        while(!feof($Thisfile)){
            $line = fgets($Thisfile);
            $line = trim($line);

            // CHECK IF STRING BEGINS WITH ServerAlias
            $tokens = explode(' ',$line, 2);

            if(!empty($tokens)){
                if($tokens[0] == "ServerName" ||
                    $tokens[0] == "DocumentRoot" ||
                    $tokens[0] == "ProxyPass" ||
                    $tokens[0] == "ProxyPassReverse" ||
                    $tokens[0] == "ProxyPreserveHost" ||
                    $tokens[0] == "ServerSignature"
                ) {
                    $info[$x][$tokens[0]] = $tokens[1];
                }

            }else{
                echo "Error...";
            }
        }

        fclose($Thisfile);
        $x++;
    }

}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Manage VirtualHost</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div id="header" class="shadow"><h2>Manage VirtualHost</h2></div>
<?php if(sizeof($info) > 0): ?>
<div class="container" style="color: #004ba0"> VirtualHost already defined</div>
<form method="post">
<?php foreach ($info as $d): ?>
    <div class="container shadow bg-white">
        <div class="size-3">ServerName: <a href="http://<?php echo $d['ServerName']; ?>"><?php echo $d['ServerName']; ?></a></div><!--
     --><div class="size-6">
            <?php if(!empty($d['DocumentRoot'])): ?>
                Directory : <?php echo $d['DocumentRoot']; ?>
            <?php else: ?>
                IP : <a href="<?php echo explode(' ',$d['ProxyPass'])[1]; ?>"><?php echo explode(' ',$d['ProxyPass'])[1]; ?></a>
            <?php endif; ?>
        </div><!--
     --><div class="size-1" style="text-align:center"><input type="checkbox" name="select[]" value="<?php echo $d['ServerName']; ?>" class="material-checkbox"/></div>
    </div>
<?php endforeach;?>
<div class="container shadow bg-white" style="text-align:center;">
    <div class="size-10"><input type="submit" name="delete" value="Suppress checked virtual host"></div>
</div>
</form>
<?php endif; ?>
<div class="container" style="color: #004ba0"> Add VirtualHost</div>
<div class="container shadow bg-white">
    <form method="post">
        <div class="size-5"> Name of the Virtual Host <br/> No Diacritical characters (éçën) - No Space - No underscore</div><!--
     --><div class="size-5"><input type="text" name="servername" required></div><!--
     --><div class="size-5"> Port of the Virtual Host</div><!--
     --><div class="size-5"><input type="number" name="port"></div><!--
     --><div class="size-5">Location</div><!--
     --><div class="size-5"><label for="rlocalhost">Local</label> <input id="rlocalhost" style="width: 10px" type="radio" value="localhost" name="location" checked> <label for="rdistant">Distant</label>
            <input id="rdistant" style="width: 10px" type="radio" value="distant" name="location">
        </div><!--
     --><div id="linfo" class="size-5"> Absolute path of the Virtual Host folder</div><!--
     --><div class="size-5"><input type="text" name="linfo" required></div><!--
     --><div class="size-10" style="text-align:center;"><input type="submit" name="create" value="create new virtual host"></div>
    </form>
</div>
<div class="container" style="color: #004ba0"> Reboot apache</div>
<form method="post">
    <div class="container" style="text-align:center;">
        <div class="size-10">
            <input type="submit" name="reboot" value="Wait between 10-15 sec before reload this page">
        </div>
    </div>
</form>
<script>
    function RadioClicked() {
        if (this.value == "localhost") {
            document.getElementById("linfo").innerHTML = "Absolute path of the Virtual Host folder"
        }
        if (this.value == "distant") {
            document.getElementById("linfo").innerHTML = "Address IP of the intra website"
        }

    }

    window.onload=function() {
        var radios = document.getElementsByName('location');
        for (var i = 0; i < radios.length; i++)
            radios[i].onclick=RadioClicked;
    }
</script>
</body>
</html>
