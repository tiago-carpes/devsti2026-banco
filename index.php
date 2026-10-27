<?php
$host = 'localhost';
$db   = 'catalogojogos';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

$mensagem = "";
$jogoEdicao = [
    'codigo' => '',
    'nome' => '',
    'link' => '',
    'descricao' => ''
];
$editando = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'] ?? '';
    $nome = trim($_POST['nome'] ?? '');
    $link = trim($_POST['link'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if (!empty($nome)) {
        if (!empty($codigo)) {
            try {
                $stmt = $pdo->prepare("UPDATE jogos SET nome = ?, link = ?, descricao = ? WHERE codigo = ?");
                $stmt->execute([$nome, $link, $descricao, $codigo]);
                $mensagem = "Jogo atualizado com sucesso!";
            } catch (PDOException $e) {
                $mensagem = "Erro ao atualizar: " . $e->getMessage();
            }
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO jogos (nome, link, descricao) VALUES (?, ?, ?)");
                $stmt->execute([$nome, $link, $descricao]);
                $mensagem = "Jogo cadastrado com sucesso!";
            } catch (PDOException $e) {
                $mensagem = "Erro ao cadastrar: " . $e->getMessage();
            }
        }
    } else {
        $mensagem = "O campo nome é obrigatório.";
    }
}

if (isset($_GET['excluir'])) {
    $codigoExcluir = $_GET['excluir'];
    $stmt = $pdo->prepare("DELETE FROM jogos WHERE codigo = ?");
    $stmt->execute([$codigoExcluir]);
    header("Location: index.php");
    exit;
}

if (isset($_GET['editar'])) {
    $codigoEditar = $_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM jogos WHERE codigo = ?");
    $stmt->execute([$codigoEditar]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($resultado) {
        $jogoEdicao = $resultado;
        $editando = true;
    }
}

$stmt = $pdo->query("SELECT * FROM jogos ORDER BY codigo DESC");
$jogos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Jogos</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>Catálogo de Jogos</h1>

    <?php if (!empty($mensagem)): ?>
        <div class="msg"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php">
        <h2><?= $editando ? 'Editar Jogo' : 'Adicionar Novo Jogo' ?></h2>
        <input type="hidden" name="codigo" autocomplete="off" value="<?= htmlspecialchars($jogoEdicao['codigo']) ?>">

        <label for="nome">Nome do Jogo:</label>
        <input type="text" id="nome" name="nome" autocomplete="off" value="<?= htmlspecialchars($jogoEdicao['nome']) ?>" required>

        <label for="link">Link:</label>
        <input type="text" id="link" name="link" autocomplete="off" value="<?= htmlspecialchars($jogoEdicao['link']) ?>">

        <label for="descricao">Descrição:</label>
        <textarea id="descricao" name="descricao"><?= htmlspecialchars($jogoEdicao['descricao']) ?></textarea>

        <button type="submit"><?= $editando ? 'Atualizar Jogo' : 'Adicionar' ?></button>
        <?php if ($editando): ?>
            <a href="index.php" class="btn" style="background: #7f8c8d;">Cancelar</a>
        <?php endif; ?>
    </form>

    <h2>Jogos Cadastrados</h2>
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Nome</th>
                <th>Link / Descrição</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($jogos) > 0): ?>
                <?php foreach ($jogos as $jogo): ?>
                    <tr>
                        <td><?= htmlspecialchars($jogo['codigo']) ?></td>
                        <td><strong><?= htmlspecialchars($jogo['nome']) ?></strong></td>
                        <td>
                            <?php if (!empty($jogo['link'])): ?>
                                <a href="<?= htmlspecialchars($jogo['link']) ?>" target="_blank"><?= htmlspecialchars($jogo['link']) ?></a><br>
                            <?php endif; ?>
                            <small><?= nl2br(htmlspecialchars($jogo['descricao'])) ?></small>
                        </td>
                        <td>
                            <a href="index.php?editar=<?= $jogo['codigo'] ?>" class="btn btn-warning">Editar</a>
                            <a href="index.php?excluir=<?= $jogo['codigo'] ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este jogo?');">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center;">Nenhum jogo cadastrado ainda.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
