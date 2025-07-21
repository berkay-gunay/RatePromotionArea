<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;
if ($hotel_id <= 0) {
    die("Geçersiz ID");
}

$contract_id = isset($_GET["contract_id"]) ? $_GET["contract_id"] : 0;
if($contract_id < 0){
    die("Geçersiz id");
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($hotel_id <= 0) {
    die("Geçersiz ID");
}

//Kayıtlı verileri aldık. Kayıtlı veriler seçili gözüksün diye 
$sql = "SELECT * FROM rate_promotion WHERE id = ?";
$stmt = $baglanti->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$recorded = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Rate Promotion</title>
    <style>
        /*checkbox için*/
        .day-card {
            background-color: #f1f1f1;
            padding: 15px 25px;
            /* artırıldı */
            border-radius: 8px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            /* yazı biraz büyüdü */
            height: 100%;
            min-width: 130px;
            /* kutu genişliği sabitlendi */
        }

        .day-card input {
            margin-right: 10px;
            transform: scale(1.2);
            /* checkbox biraz büyüdü */
        }

        /*tablo kısmı için*/
        .table.dotted-rows td,
        .table.dotted-rows th {
            border: none;
            /* Hücre kenarlıklarını kaldır */
        }

        .table.dotted-rows tr {
            border-bottom: 1px dotted #aaa;
            /* Satır altına kesik çizgi */
        }

        .table.dotted-rows tr:last-child {
            border-bottom: none;
            /* Son satıra çizgi koyma */
        }

        .flex-container {
            display: flex;
            gap: 20px;
            /* Divler arası boşluk */
            align-items: flex-end;
            /* Input'ları hizalamak için */
        }
    </style>

</head>

<body>
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1> Edit Rate Promotion</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Rezervasyon Modülü</a></li>
                            <li class="breadcrumb-item active"> Edit Rate Promotion</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <section class="content">
            <!-- ./row -->
            <div class="row">
                <div class="col-md-12">

                    <div class="callout callout-info">
                        <div class="row no-print">
                            <div class="col-12">

                                <a href="ratepromotionlist.php?hotel_id=<?php echo $hotel_id; ?>"><button class="btn btn-danger"><i class="fa-solid fa-angle-left"></i> Geri</button></a>
                            </div>
                        </div>
                    </div>

                    <div class="card card-outline card-info">
                        


                        <div class='card-body'>

                            <form action="" method="post">

                                <div class="form-group">
                                    <h4>Promotion Code</h4>
                                    <input type="text" class="form-control" name="promotion_code" value="<?= htmlspecialchars($recorded["promo_code"]) ?>">
                                </div>


                                <input type="hidden" name="room_count" value="<?= $roomCount ?>">

                                <div class="flex-container">
                                    <div>
                                        <p>Min. Night</p>
                                        <input type="number" min="0" name="min_nights" class="form-control checkout" value="<?= htmlspecialchars($recorded["min_nights"]) ?>" required>
                                    </div>
                                    <div>
                                        <p>Day Before</p>
                                        <input type="number" min="0" name="days_before" class="form-control checkout" value="<?= htmlspecialchars($recorded["days_before"]) ?>" required>
                                    </div>
                                    <div>
                                        <input type="checkbox" name="" id="">
                                        <label for="">Valid for all Arrivals</label>
                                    </div>
                                </div>
                                <br>


                                <h4>Room Type</h4>
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <th>Room</th>
                                        <th>Rate</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT p.rate,n.room_type_name,p.room_type_id
                                        FROM rate_promotion_rooms p
                                    JOIN room_type_name n ON p.room_type_id = n.id
                                    WHERE p.rate_promotion_id=?";
                                        $stmt = $baglanti->prepare($sql);
                                        $stmt->bind_param("i", $id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        if ($result->num_rows > 0) {
                                            $roomCount = 0;
                                            while ($row = $result->fetch_assoc()) { ?>
                                                <tr>
                                                    <td>
                                                        <label><?= htmlspecialchars($row['room_type_name']) ?> </label>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="rate_room_<?= $roomCount ?>" value="<?= htmlspecialchars($row["rate"]) ?>" required>
                                                        <input type="hidden" name="room_type_id_<?= $roomCount ?>" value="<?= htmlspecialchars($row['room_type_id']) ?>">
                                                    </td>
                                                </tr>

                                                <?php $roomCount++; ?>

                                            <?php } ?>
                                        <?php } ?>

                                        <input type="hidden" name="room_count" value="<?= $roomCount?>">
                                    </tbody>
                                </table>
                                <br>

                                <h4>Days</h4>
                                <div class="container mt-3">
                                    <div class="row g-2">
                                        <div class="col-auto">
                                            <div class="day-card">
                                                <input type="checkbox" name="monday" value="1" <?php if ($recorded["monday"]) {
                                                                                                    echo "checked";
                                                                                                } ?>> Monday
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="day-card">
                                                <input type="checkbox" name="tuesday" value="1" <?php if ($recorded["tuesday"]) {
                                                                                                    echo "checked";
                                                                                                } ?>> Tuesday
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="day-card">
                                                <input type="checkbox" name="wednesday" value="1" <?php if ($recorded["wednesday"]) {
                                                                                                        echo "checked";
                                                                                                    } ?>> Wednesday
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="day-card">
                                                <input type="checkbox" name="thursday" value="1" <?php if ($recorded["thursday"]) {
                                                                                                        echo "checked";
                                                                                                    } ?>> Thursday
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="day-card">
                                                <input type="checkbox" name="friday" value="1" <?php if ($recorded["friday"]) {
                                                                                                    echo "checked";
                                                                                                } ?>> Friday
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="day-card">
                                                <input type="checkbox" name="saturday" value="1" <?php if ($recorded["saturday"]) {
                                                                                                        echo "checked";
                                                                                                    } ?>> Saturday
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="day-card">
                                                <input type="checkbox" name="sunday" value="1" <?php if ($recorded["sunday"]) {
                                                                                                    echo "checked";
                                                                                                } ?>> Sunday
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>

                                <?php
                                $sql_dates = "SELECT DISTINCT start_date, finish_date FROM contracts WHERE contract_id=? AND hotel_id = ?";
                                        $stmt_dates = $baglanti->prepare($sql_dates);
                                        $stmt_dates->bind_param("ii", $contract_id, $hotel_id);
                                        $stmt_dates->execute();
                                        $result_dates = $stmt_dates->get_result();
                                        $row_dates = $result_dates->fetch_assoc();
                                        ?>

                                <table class="table dotted-rows">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <p>Booking Period</p><input type="date" name="booking_period_start" min="<?= $row_dates["start_date"] ?>" max="<?= $row_dates["finish_date"] ?>" class="form-control checkout" value="<?= htmlspecialchars($recorded["booking_period_start"]) ?>" required>
                                            </td>
                                            <td>
                                                <p>&nbsp;</p><input type="date" name="booking_period_end" min="<?= $row_dates["start_date"] ?>" max="<?= $row_dates["finish_date"] ?>" class="form-control checkout" value="<?= htmlspecialchars($recorded["booking_period_end"]) ?>" required>
                                            </td>
                                            <td>
                                                <p>Travel Date</p><input type="date" name="travel_date_start" min="<?= $row_dates["start_date"] ?>" max="<?= $row_dates["finish_date"] ?>" class="form-control checkout" value="<?= htmlspecialchars($recorded["travel_date_start"]) ?>" required>
                                            </td>
                                            <td>
                                                <p>&nbsp;</p><input type="date" name="travel_date_end" min="<?= $row_dates["start_date"] ?>" max="<?= $row_dates["finish_date"] ?>" class="form-control checkout" value="<?= htmlspecialchars($recorded["travel_date_end"]) ?>" required>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <hr style="border-top: 1px dotted #000;">
                                <button type="submit" style="width: 100%;" class="btn btn-success"> Güncelle </button>
                            </form>


                            <?php

                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                                //$contract_id = intval($_POST['selectContract']);
                                $room_count = intval($_POST["room_count"]);
                                $promotion_code = $_POST["promotion_code"];
                                $monday = isset($_POST["monday"]) ? 1 : 0;
                                $tuesday = isset($_POST["tuesday"]) ? 1 : 0;
                                $wednesday = isset($_POST["wednesday"]) ? 1 : 0;
                                $thursday = isset($_POST["thursday"]) ? 1 : 0;
                                $friday = isset($_POST["friday"]) ? 1 : 0;
                                $saturday = isset($_POST["saturday"]) ? 1 : 0;
                                $sunday = isset($_POST["sunday"]) ? 1 : 0;
                                $booking_period_start = $_POST["booking_period_start"];
                                $booking_period_end = $_POST["booking_period_end"];
                                $travel_date_start = $_POST["travel_date_start"];
                                $travel_date_end = $_POST["travel_date_end"];
                                $days_before = $_POST["days_before"];
                                $min_nights = $_POST["min_nights"];

                                
                                //Güncelle işlemini yapıyoruz
                                $sql = "UPDATE rate_promotion 
                                SET contract_id = ?, hotel_id = ?, monday = ?, tuesday = ?, wednesday = ?, thursday = ?, friday = ?, saturday = ?, sunday = ?, promo_code = ?, booking_period_start = ?, booking_period_end = ?, travel_date_start = ?, travel_date_end = ?, days_before = ?, min_nights = ? 
                                WHERE id=?";
                                $stmt = $baglanti->prepare($sql);
                                $stmt->bind_param("iiiiiiiiisssssiii",$contract_id, $hotel_id, $monday, $tuesday, $wednesday, $thursday, $friday, $saturday, $sunday, $promotion_code, $booking_period_start, $booking_period_end, $travel_date_start, $travel_date_end, $days_before, $min_nights, $id);
                                $stmt->execute();
                                //$rate_promotion_id = $stmt->insert_id;
                                $stmt->close();

                                //Önce mevcut oda kayıtlarını silip sonra yeni seçimleri ekliyoruz.
                                $sql = "DELETE FROM rate_promotion_rooms WHERE rate_promotion_id = ?";
                                $stmt = $baglanti->prepare($sql);
                                $stmt->bind_param("i", $id);
                                $stmt->execute();
                                $stmt->close();

                                for ($i = 0; $i < $room_count; $i++) {
                                    $rate = $_POST["rate_room_{$i}"];
                                    $room_type_id = isset($_POST["room_type_id_{$i}"]) ? $_POST["room_type_id_{$i}"] : 0;

                                    if ($room_type_id !== 0) {
                                        $sql = "INSERT INTO rate_promotion_rooms(rate_promotion_id, room_type_id, rate) VALUES (?,?,?)";
                                        $stmt = $baglanti->prepare($sql);
                                        $stmt->bind_param("iii", $id, $room_type_id, $rate);
                                        $stmt->execute();
                                        $stmt->close();
                                    }
                                }

                                header("Location: ratepromotionlist.php?hotel_id=$hotel_id&success=1"); // aynı sayfa ama GET ile
                                exit;
                            }
                            ?>

                        </div>
                    </div>
                </div>
        </section>
    </div>
    <!-- /.content-wrapper -->

    <script>

    </script>
    <footer class="main-footer">
        <strong>Telif hakkı &copy; 2014-2025 <a href="https://mansurbilisim.com" target="_blank">Mansur Bilişim Ltd. Şti.</a></strong>
        Her hakkı saklıdır.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0.1
        </div>
    </footer>
</body>

</html>