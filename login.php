<?php
session_start();
include 'koneksi.php';

if(isset($_POST['login'])){

    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($koneksi,
    "SELECT * FROM users
     WHERE username='$username'
     AND password='$password'");

   if(mysqli_num_rows($query) > 0){

    $data = mysqli_fetch_assoc($query);

    $_SESSION['login']=true;
    $_SESSION['id']=$data['id'];
    $_SESSION['username']=$data['username'];
    $_SESSION['nama']=$data['nama'];
    $_SESSION['jabatan']=$data['jabatan'];
    $_SESSION['email']=$data['email'];

    header("Location:index.php");
    exit;
}

        $error = "Username atau Password salah!";

    }
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Login ElectroStock</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    .logo-bg{
    position:absolute;
    width:120%;
    height:120%;
    display:flex;
    justify-content:center;
    align-items:center;
}

.logo-bg{
    position:fixed;
    top:0;
    left:0;
    width:1000vw;
    height:400vh;
    overflow:hidden;
}

.logo-bg img{
    position:absolute;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);

    width:270vw;
    height:400;

    opacity:.15;

    filter:blur(5px);
}
body{
    margin:0;
    font-family:'Poppins',sans-serif;

    background-image:url('logo.png');
    background-size:cover;
    background-position:center;
    background-repeat:no-repeat;

    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;

    overflow:hidden;
}

.logo-bg{
    position:absolute;
    width:100%;
    height:100%;
    display:flex;
    justify-content:center;
    align-items:center;
}

.logo-bg img{
    width:550px;
    opacity:.3;
}

body::before{
    content:'';
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;

    background:rgba(0,0,0,.35);

    z-index:1;
}
.card{
    position:relative;
    z-index:10;

    width:380px;
    padding:35px;

    background:rgba(14, 82, 230, 0.25);
    backdrop-filter:blur(20px);

    border:1px solid rgba(255, 255, 255, 0.84);
    border-radius:20px;

    box-shadow:0 20px 50px rgba(12, 14, 134, 0.99);
}
h2{
    color:white;
    text-align:center;
    margin-bottom:5px;
}

p{
    color:rgba(26, 2, 2, 0.8);
    text-align:center;
    color:#666;
}

input{
    width:100%;
    padding:12px;
    margin-bottom:15px;

    background:rgba(255,255,255,.1);
    border:1px solid rgba(255,255,255,.2);

    border-radius:10px;
    box-sizing:border-box;

    color:white;
    
}
input::placeholder{
    color:rgba(255,255,255,.7);
}
button{
    width:100%;
    padding:14px;
    border:none;
    border-radius:12px;

    background:#2563eb;
    color:white;
    font-weight:bold;

    cursor:pointer;
    transition:.3s;
}
button:hover{
    background:#1d4ed8;
    transform:translateY(-3px);
}

button:hover{
    background:#1e40af;
}

.error{
    background:#fee2e2;
    color:#dc2626;
    padding:10px;
    border-radius:10px;
    margin-bottom:15px;
}

</style>
</head>

<body>

<div class="logo-bg">
    <img src="logo.png">
</div>
<div class="card">
<h2>⚡ ElectroStock</h2>
<p>Inventory Management System</p>


<?php if(isset($error)){ ?>
<div class="error">
<?php echo $error; ?>
</div>
<?php } ?>

<form method="POST">

<input
type="text"
name="username"
placeholder="Username"
required>

<input
type="password"
name="password"
placeholder="Password"
required>

<button type="submit" name="login">
LOGIN
</button>

</form>

</div>

</body>
</html>