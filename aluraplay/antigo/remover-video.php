<?php

use Alura\Mvc\Repository\VideoRepository;

$dbPath = __DIR__ . '/banco.sqlite';
$pdo = new PDO("sqlite:$dbPath");

$id = $_GET['id'];
// $sql = 'DELETE FROM videos WHERE id = ?;';
// $statement = $pdo->prepare($sql);
// $statement->bindValue(1, $id);

$repository = new VideoRepository($pdo);

if ($repository->remove($id) === false) {
  header('Location: /?sucesso=0');
} else {
  header('Location: /?sucesso=1');
}
