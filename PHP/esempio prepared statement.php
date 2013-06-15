<?php

// creazione connessione
$db = new mysqli($dbLocation, $dbUser, $dbPassword, $dbName);

// controllo connessione
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

// creazione prepared statement
if ($stmt = $db->prepare("SELECT District FROM City WHERE Name=?")) {

    // binding dei parametri
    // $password contiene la password fornita dall'utente
    $stmt->bind_param("s", $password);

    // esecuzione query
    $stmt->execute();

    // bind dei risultati 
    $stmt->bind_result($result);

    // fetch dei valori
    $stmt->fetch();

    // close statement 
    $stmt->close();
}

// chiusura connessione
$db->close();

if($result)
{
    // login effettuato con successo
}
else
{
    // login non valido
}
?>