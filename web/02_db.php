<?php
function connectDB()
{
    $config = parse_ini_file("/local/my_web_files/user/db.ini");
    $dbh = new PDO($config['dsn'], $config['username'], $config['password']);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}
function authenticate($user, $passwd) {
    try {
        $dbh = connectDB();
        $hashed = hash('sha256', $passwd);
        $statement = $dbh->prepare(
            "SELECT customer_id, username, first_name
             FROM customer
             WHERE username = :username
             AND password_hash = :password"
        );
        $statement->execute([
            ":username" => $user,
            ":password" => $hashed
        ]);
        $userData = $statement->fetch(PDO::FETCH_ASSOC);
        $dbh = null;
        // Return user data if found, otherwise false
        return $userData ? $userData : false;
    } catch (PDOException $e) {
        print "Error! " . $e->getMessage() . "<br/>";
        die();
    }
}
?>
