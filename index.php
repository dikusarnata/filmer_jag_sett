<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/functions.php";

$message = "";
$editFilm = null;

// CREATE - dodaj film
if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["add_film"])) {

    $namn = trim($_POST["namn"] ?? "");

    if ($namn === "") {
        $message = "Ange filmens namn.";
    } else {
        $sql = "INSERT INTO filmer
                (namn, beskrivning, datum, kategori, betyg, regissor)
                VALUES (:namn, :beskrivning, :datum, :kategori, :betyg, :regissor)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":namn" => $namn,
            ":beskrivning" => trim($_POST["beskrivning"] ?? ""),
            ":datum" => ($_POST["datum"] ?? "") ?: null,
            ":kategori" => trim($_POST["kategori"] ?? ""),
            ":betyg" => ($_POST["betyg"] ?? "") !== ""
                ? $_POST["betyg"] : null,
            ":regissor" => trim($_POST["regissor"] ?? "")
        ]);

        header("Location: index.php?added=1");
        exit;
    }
}

// DELETE - ta bort film
if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["delete_film"])) {

    $id = filter_input(INPUT_POST, "film_id", FILTER_VALIDATE_INT);

    if ($id && $id > 0) {
        $stmt = $pdo->prepare(
            "DELETE FROM filmer WHERE film_id = :id"
        );
        $stmt->execute([":id" => $id]);
    }

    header("Location: index.php?deleted=1");
    exit;
}

// UPDATE - spara andringar
if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["update_film"])) {

    $id = filter_input(INPUT_POST, "film_id", FILTER_VALIDATE_INT);
    $namn = trim($_POST["namn"] ?? "");

    if (!$id || $id < 1) {
        $message = "Ogiltigt film-ID.";
    } elseif ($namn === "") {
        $message = "Ange filmens namn.";
    } else {
        $sql = "UPDATE filmer SET
                    namn = :namn,
                    beskrivning = :beskrivning,
                    datum = :datum,
                    kategori = :kategori,
                    betyg = :betyg,
                    regissor = :regissor
                WHERE film_id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":namn" => $namn,
            ":beskrivning" => trim($_POST["beskrivning"] ?? ""),
            ":datum" => ($_POST["datum"] ?? "") ?: null,
            ":kategori" => trim($_POST["kategori"] ?? ""),
            ":betyg" => ($_POST["betyg"] ?? "") !== ""
                ? $_POST["betyg"] : null,
            ":regissor" => trim($_POST["regissor"] ?? ""),
            ":id" => $id
        ]);

        header("Location: index.php?updated=1");
        exit;
    }
}

// READ - hamta film for redigering
if (isset($_GET["edit"])) {
    $id = filter_input(INPUT_GET, "edit", FILTER_VALIDATE_INT);

    if ($id && $id > 0) {
        $stmt = $pdo->prepare(
            "SELECT * FROM filmer WHERE film_id = :id"
        );
        $stmt->execute([":id" => $id]);
        $editFilm = $stmt->fetch();
    }
}

if (isset($_GET["added"])) {
    $message = "Filmen har lagts till!";
} elseif (isset($_GET["updated"])) {
    $message = "Filmen har uppdaterats!";
} elseif (isset($_GET["deleted"])) {
    $message = "Filmen har tagits bort!";
}

function h($value) {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

$stmt = $pdo->query("SELECT * FROM filmer ORDER BY datum DESC, film_id DESC");
$films = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Filmer jag sett</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<!-- NAVBAR -->
<nav class="navbar navbar-dark bg-dark shadow">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="index.php">
            🎬 Filmer jag sett
        </a>
        <span class="navbar-text">
            Min filmsamling
        </span>
    </div>
</nav>

<div class="container py-5">

    <h2 class="fw-bold mb-4">Alla filmer 🎥</h2>

    <?php if (!$films): ?>
        <div class="alert alert-info">
            Inga filmer har lagts till ännu.
        </div>
    <?php else: ?>

    <div class="card border-0 shadow-sm mb-5">
        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">

                    <thead class="table-dark">
                        <tr>
                            <th>Namn</th>
                            <th>Beskrivning</th>
                            <th>Datum</th>
                            <th>Kategori</th>
                            <th>Betyg</th>
                            <th>Regissör</th>
                            <th>Åtgärder</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($films as $film): ?>
                        <tr>
                            <td class="fw-semibold">
                                <?= h($film["namn"]) ?>
                            </td>

                            <td>
                                <?= h($film["beskrivning"]) ?>
                            </td>

                            <td>
                                <?= h($film["datum"]) ?>
                            </td>

                            <td>
                                <span class="badge bg-info text-dark">
                                    <?= h($film["kategori"]) ?>
                                </span>
                            </td>

                            <td>
                                <?= h($film["betyg"]) ?>
                                <?php if ($film["betyg"]): ?>
                                    ⭐
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= h($film["regissor"]) ?>
                            </td>

                            <td class="text-nowrap">

                                <a href="index.php?edit=<?= (int)$film["film_id"] ?>"
                                   class="btn btn-sm btn-warning">
                                    Redigera
                                </a>

                                <form method="post" action="index.php"
                                      class="d-inline"
                                      onsubmit="return confirm('Vill du verkligen ta bort filmen?');">

                                    <input type="hidden" name="film_id"
                                           value="<?= (int)$film["film_id"] ?>">

                                    <button type="submit" name="delete_film"
                                            class="btn btn-sm btn-danger">
                                        Ta bort
                                    </button>
                                </form>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>

                </table>
            </div>

        </div>
    </div>

    <?php endif; ?>

    <h1 class="text-center fw-bold mb-4">
        Filmer jag sett 🎬
    </h1>

    <?php if ($message !== ""): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm"
             role="alert">
            <?= h($message) ?>

            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">

            <div class="card border-0 shadow">

                <div class="card-header bg-primary text-white py-3">
                    <h2 class="h4 mb-0">
                        <?= $editFilm ? "Redigera film ✏️" : "Lägg till film ➕" ?>
                    </h2>
                </div>

                <div class="card-body p-4">

                    <form method="post" action="index.php">

                        <?php if ($editFilm): ?>
                            <input type="hidden" name="film_id"
                                   value="<?= h($editFilm["film_id"]) ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="namn" class="form-label">
                                Namn *
                            </label>

                            <input id="namn" name="namn"
                                   class="form-control"
                                   required
                                   value="<?= h($editFilm["namn"] ?? "") ?>">
                        </div>

                        <div class="mb-3">
                            <label for="beskrivning" class="form-label">
                                Beskrivning
                            </label>

                            <textarea id="beskrivning" name="beskrivning"
                                      class="form-control"
                                      rows="3"><?= h($editFilm["beskrivning"] ?? "") ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="datum" class="form-label">
                                Datum
                            </label>

                            <input type="date" id="datum" name="datum"
                                   class="form-control"
                                   value="<?= h($editFilm["datum"] ?? "") ?>">
                        </div>

                        <div class="mb-3">
                            <label for="kategori" class="form-label">
                                Kategori
                            </label>

                            <input id="kategori" name="kategori"
                                   class="form-control"
                                   value="<?= h($editFilm["kategori"] ?? "") ?>">
                        </div>

                        <div class="mb-3">
                            <label for="betyg" class="form-label">
                                Betyg
                            </label>

                            <select id="betyg" name="betyg"
                                    class="form-select">

                                <option value="">Välj betyg</option>

                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?= $i ?>"
                                        <?= (int)($editFilm["betyg"] ?? 0) === $i ? "selected" : "" ?>>
                                        <?= str_repeat("⭐", $i) ?>
                                    </option>
                                <?php endfor; ?>

                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="regissor" class="form-label">
                                Regissör
                            </label>

                            <input id="regissor" name="regissor"
                                   class="form-control"
                                   value="<?= h($editFilm["regissor"] ?? "") ?>">
                        </div>

                        <div class="d-flex gap-2">

                            <?php if ($editFilm): ?>
                                <button type="submit" name="update_film"
                                        class="btn btn-success">
                                    Spara ändringar
                                </button>

                                <a href="index.php"
                                   class="btn btn-outline-secondary">
                                    Avbryt
                                </a>

                            <?php else: ?>
                                <button type="submit" name="add_film"
                                        class="btn btn-primary">
                                    Lägg till film
                                </button>
                            <?php endif; ?>

                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

    <footer class="text-center text-secondary mt-5">
        <p>🎬 Min filmsamling</p>
        <p>Skapad med Bootstrap 5.3</p>
    </footer>

</div>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>