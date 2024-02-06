<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
  <?php
    $servername = 'localhost';
    $username = 'root';
    $password = 'gattinamia';
    $error = true;
    try {
        $db = new PDO("mysql:host = $servername;dbname=user", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        if (isset($_POST["confirm"])) {
            $error = update($db, $_POST["confirm"], $_POST["newName"], $_POST["newFname"], $_POST["newEmail"], $_POST["newPostcode"]);
        }
        if (isset($_POST["create"])) {
            $error = create($db, $_POST['name'], $_POST['fname'], $_POST['email'], $_POST['postcode']);
        }
        delete($db);

        $db->beginTransaction();

        $requeteSQL = $db->prepare("SELECT id, Nom, Prénom, Email, CodePostal FROM test");

        $requeteSQL->execute();
        $tableauRequete = $requeteSQL->fetchAll();
        $db->commit();

        $db = null;
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
        $db->rollback();
    }
?>
 <table>
        <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Code Postal</th>
            <td><b>Action</b></td>
        </tr>
        <?php for ($i = 0; $i < count($tableauRequete); $i++) { ?>
            <tr>
                <form method="POST">
                    <?php if (isset($_POST['update'])) {

                        if ($_POST['update'] ==  $tableauRequete[$i]["id"]) { ?>
                            <td>
                                <label for="name"></label>
                                <input type="text" name="newName" value="<?= $tableauRequete[$i]["Nom"]; ?>">
                            </td>
                            <td>
                                <label for="fname"></label>
                                <input type="text" name="newFname" value="<?= $tableauRequete[$i]["Prénom"]; ?>">
                            </td>
                            <td>
                                <label for="email"></label>
                                <input type="text" name="newEmail" value="<?= $tableauRequete[$i]["Email"]; ?>">
                            </td>
                            <td>
                                <label for="codePostal"></label>
                                <input type="text" name="newPostcode" value="<?= $tableauRequete[$i]["CodePostal"]; ?>">
                            </td>
                            <td>
                                <button type=submit name="confirm" value=<?= $tableauRequete[$i]["id"] ?>> Valider </button>

                            <?php } else { ?>
                            <td><?php echo $tableauRequete[$i]["Nom"]; ?></td>
                            <td><?php echo $tableauRequete[$i]["Prénom"]; ?></td>
                            <td><?php echo $tableauRequete[$i]["Email"]; ?></td>
                            <td><?php echo $tableauRequete[$i]["CodePostal"] ?></td>
                            <td><button type=submit name="update" value=<?php echo $tableauRequete[$i]["id"]; ?>> Modifier </button>
                            <?php }
                    } else {

                            ?>
                            <td><?php echo $tableauRequete[$i]["Nom"]; ?></td>
                            <td><?php echo $tableauRequete[$i]["Prénom"]; ?></td>
                            <td><?php echo $tableauRequete[$i]["Email"]; ?></td>
                            <td><?php echo $tableauRequete[$i]["CodePostal"] ?></td>
                            <td><button type=submit name="update" value=<?php echo $tableauRequete[$i]["id"]; ?>> Modifier </button>
                            <?php } ?>
                            </td>
                            <td>
                                <button type=submit name="delete" value=<?php echo $tableauRequete[$i]["id"]; ?>>Supprimer </button>
                            </td>
            </tr>

        <?php } ?>
        <?php if (isset($_POST['add'])) { ?>
            <tr>
                <td>
                    <label for="name"></label>
                    <input type="text" placeholder="Nom" name="name">
                </td>

                <td>
                    <label for="fname"></label>
                    <input type="text" placeholder="Prénom" name="fname">
                </td>
                <td>
                    <label for="email"></label>
                    <input type="text" placeholder="Email" name="email">
                </td>
                <td>
                    <label for="CodePostal"></label>
                    <input type="number" placeholder="Code Postal" name="postcode">

                </td>
                <td>
                    <button type="submit" name="create">Valider</button>
                </td>
            </tr>
        <?php } ?>
       
            <button class ="btn"type=submit name="add">Ajouter utilisateur</button>
       
        </form>
    </table>
    <?php
    if ($error !== true) {
        echo "<p>$error</p>";
    }

    function delete($db)
    {
        if (isset($_POST['delete'])) {
            $requeteSQL = $db->prepare("DELETE FROM test WHERE id = :id");
            $requeteSQL->execute([":id" => $_POST['delete']]);
        }
    }
    function update($db, $newId, $newName, $newFname, $newEmail, $newPostcode)
    {
        if (isset($_POST['confirm'])) {
            $requeteSQL = $db->prepare("UPDATE test SET id = '$newId', Nom=  '$newName', Prénom='$newFname', Email='$newEmail', CodePostal='$newPostcode' WHERE id = :id");
            $regex = check($newName, $newFname, $newEmail, $newPostcode);
            if ($regex === true) {
                $requeteSQL->execute([
                    ":id" => $_POST['confirm']
                ]);
            }
        }
        return $regex;
    }
    function create($db, $name, $fname, $email, $postCode)
    {
        if (isset($_POST['create'])) {
            $requeteSQL = $db->prepare("INSERT INTO test ( Nom, Prénom, Email, CodePostal)VALUES('$name', '$fname', '$email', '$postCode')");
            $regex = check($name, $fname, $email, $postCode);
            if ($regex === true) {
                $requeteSQL->execute();
            }
        }
        return $regex;
    }
    function check($name, $fname, $email, $postCode)
    {
        if (!preg_match('/^[A-Za-z]+$/', $name)) {
            return "Veuillez saisir un nom valide";
        }

        if (!preg_match('/^[A-Za-z]+$/', $fname)) {
            return "Veuillez saisir un prénom valide";
        }

        if (!preg_match('^[A-zÀ-ÿ0-9]*@[a-z]*\.[a-z]{2,5}$^', $email)) {
            return "Veuillez saisir un email valide";
        }

        if (!preg_match("/^[0-9]{5}$/", $postCode)) {
            return "Veuillez saisir un code postal valide";
        }
        return true;
    }

    ?>
</body>

</html>