<?php

function renderFilmsList($pdo) {
    $sql = "SELECT * FROM filmer ORDER BY datum DESC";
    $stmt = $pdo->query($sql);
    $films = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($films)) {
        echo "<p>Inga filmer har lagts till ännu.</p>";
        return;
    }

    echo "<table border='1' cellpadding='8'>";
    echo "<tr>
            <th>Namn</th>
            <th>Beskrivning</th>
            <th>Datum</th>
            <th>Kategori</th>
            <th>Betyg</th>
            <th>Regissör</th>
            <th>Åtgärder</th>
          </tr>";

    foreach ($films as $film) {
        $id = (int)$film['film_id'];

        echo "<tr>";
        echo "<td>" . htmlspecialchars($film['namn'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($film['beskrivning'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($film['datum'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($film['kategori'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars((string)($film['betyg'] ?? '')) . "</td>";
        echo "<td>" . htmlspecialchars($film['regissor'] ?? '') . "</td>";

        echo "<td>
                <a href='edit.php?id=$id'>Redigera</a>

                <form action='delete.php' method='post'
                      onsubmit=\"return confirm('Vill du verkligen ta bort filmen?');\"
                      style='display:inline;'>
                    <input type='hidden' name='film_id' value='$id'>
                    <button type='submit'>Ta bort</button>
                </form>
              </td>";

        echo "</tr>";
    }

    echo "</table>";
}