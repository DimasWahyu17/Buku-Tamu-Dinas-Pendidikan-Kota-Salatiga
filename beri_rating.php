<?php
$db = new SQLite3('database.sqlite');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    if ($rating >= 1 && $rating <= 5 && $id > 0) {
        $stmt = $db->prepare("UPDATE buku_tamu SET rating = :rating WHERE id = :id");
        $stmt->bindValue(':rating', $rating, SQLITE3_INTEGER);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
        header("Location: index.php?thanks=1");
        exit;
    } else {
        echo "Rating tidak valid.";
    }
}

$data = $db->querySingle("SELECT nama FROM buku_tamu WHERE id = $id", true);
if (!$data) die("Data tidak ditemukan.");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Beri Rating Layanan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom right, #e0f7fa, #b2ebf2);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .rating-container {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            text-align: center;
            width: 90%;
            max-width: 400px;
        }

        .rating-container h2 {
            margin-bottom: 20px;
            font-weight: 600;
            color: #00796B;
        }

        .stars {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .stars input[type="radio"] {
            display: none;
        }

        .stars label {
            font-size: 2rem;
            color: #ccc;
            cursor: pointer;
            transition: 0.3s;
        }

        .stars input[type="radio"]:checked ~ label,
        .stars label:hover,
        .stars label:hover ~ label {
            color: #FFD700;
        }

        .rating-container button {
            padding: 10px 20px;
            background-color: #00796B;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }

        .rating-container button:hover {
            background-color: #004d40;
        }
    </style>
</head>
<body>
    <div class="rating-container">
        <h2>Halo <?= htmlspecialchars($data['nama']) ?>,<br>berikan rating pelayanan kami</h2>
        <form method="POST">
            <div class="stars">
                <input type="radio" id="star5" name="rating" value="5" required><label for="star5">★</label>
                <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
                <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
                <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
                <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
            </div>
            <button type="submit">Kirim Rating</button>
        </form>
    </div>
</body>
</html>
