<?php
require_once("config/dbconf.php");
session_start();
if(!isset($_SESSION['user'])){
    header("Location: /login.php");
    exit;
}
if(isset($_POST['reset_best'])){
    unset($_SESSION['best_score']);
}
if(empty($_SESSION['choice']) || isset($_POST['reset'])){
    $choice  =  rand(0,100);
    $_SESSION['score'] = 0;
    $_SESSION['choice'] = $choice;
}else{
    $choice = $_SESSION['choice'];
}

$response = null;
if( !isset($_POST['guess'])
    || empty($_POST['guess'])){
    $response = "Pas de nombre";
}else{
    $guess = $_POST['guess'];
    $_SESSION['score']++;
    if($guess > $choice) {
        $response = "C'est moins";
    }elseif($guess < $choice){
        $response = "C'est plus";
    }else{
        $response = "C'est gagné";
        if( !isset($_SESSION['best_score'])
            || $_SESSION['best_score'] > $_SESSION['score']){
            $_SESSION['best_score'] = $_SESSION['score'];
        }

        global $config;
        $pdo = new PDO($config['host'], $config['user'], $config['password']);
        $stmt = $pdo->prepare("UPDATE users SET best_score =:best_score
                          WHERE login = :login"
        );
        $stmt->bindParam("best_score",$_SESSION['best_score']);
        $stmt->bindParam("login",$_SESSION['user']);
        $stmt->execute();


        unset($_SESSION['choice']);

    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Des papiers dans un bol </title>
</head>
<body>
<?php
global $config;
$pdo = new PDO($config['host'], $config['user'], $config['password']);
$leaderboard = $pdo->prepare("SELECT login, best_score FROM users ORDER BY best_score LIMIT 0,1000");
$leaderboard->execute();

echo "<h3>Leaderboard</h3><table border=1px>";
echo "<tr><th>User</th><th>Score</th></tr>";
while ($leaderboardResult = $leaderboard->fetch())
{
    echo "<tr><td>".$leaderboardResult['login']."</td>";
    echo"<td>".$leaderboardResult['best_score']."</td></tr>";
}

echo "</table><br><br>"

?>
<?php echo $response;?> <br>
Nombre de coup : <?php echo $_SESSION['score']; ?><br>
<em>[Meilleur score pour <?php echo $_SESSION['user'];?>:
    <?php
    echo !isset($_SESSION['best_score'])
        ? "Pas de meilleur score"
        : $_SESSION['best_score'];
    ?>]</em>
<form method="POST">
    <input type="text" name="guess" autofocus>
    <input type="submit">
    <input type="submit" name="reset" value="reset">
    <input type="submit" name="reset_best" value="reset best">
</form>
<em>(La réponse est <?php echo $choice?>)</em>


<form method="POST" action="login.php">
    <input type="submit" name="logout" value="Logout">
</form>

</body>
</html>