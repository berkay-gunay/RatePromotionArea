<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function turkceTarih($format, $dateStr)
{
    $en = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
        'Mon',
        'Tue',
        'Wed',
        'Thu',
        'Fri',
        'Sat',
        'Sun',
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December',
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'May',
        'Jun',
        'Jul',
        'Aug',
        'Sep',
        'Oct',
        'Nov',
        'Dec'
    ];

    $tr = [
        'Pazartesi',
        'Salı',
        'Çarşamba',
        'Perşembe',
        'Cuma',
        'Cumartesi',
        'Pazar',
        'Pts',
        'Sal',
        'Çar',
        'Per',
        'Cum',
        'Cts',
        'Paz',
        'Ocak',
        'Şubat',
        'Mart',
        'Nisan',
        'Mayıs',
        'Haziran',
        'Temmuz',
        'Ağustos',
        'Eylül',
        'Ekim',
        'Kasım',
        'Aralık',
        'Oca',
        'Şub',
        'Mar',
        'Nis',
        'May',
        'Haz',
        'Tem',
        'Ağu',
        'Eyl',
        'Eki',
        'Kas',
        'Ara'
    ];

    return str_replace($en, $tr, date($format, strtotime($dateStr)));
}


$hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;
if ($hotel_id <= 0) {
    die("Geçersiz ID");
}


if (isset($_POST["ajax"]) && $_POST["ajax"] === "get_contract" && isset($_POST['contract_id'])) {
    $contract_id = $_POST['contract_id'];

    $roomCount = 0;
    $checkSql = "SELECT contract_id FROM rate_promotion WHERE hotel_id = ? AND contract_id = ?";
    $checkStmt = $baglanti->prepare($checkSql);
    $checkStmt->bind_param("ii", $hotel_id, $contract_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows > 0) {
        echo "<div class='alert alert-danger'> Bu kontrata ait kayıt bulunmaktadır. Lütfen başka kayıt seçiniz </div>";
        exit;
    } else {


        echo "<h4>Room Type</h4>";
        echo "<table class='table table-bordered table-striped'>";
        echo "<thead>";
        echo "<th>Room</th>";
        echo "<th>Rate</th>";
        echo "</thead>";
        echo " <tbody>";

        $sql = "SELECT DISTINCT c.room_id, n.room_type_name
                FROM contracts c
                JOIN room_type_name n ON c.room_id = n.id
                WHERE c.hotel_id=? AND c.contract_id=?";
        $stmt = $baglanti->prepare($sql);
        $stmt->bind_param("ii", $hotel_id, $contract_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>";
                echo "<label>" . $row['room_type_name'] . "</label>";
                echo " </td>";
                echo "<td>";
                echo "<input type='text' name='rate_room_" . $roomCount . "' required>";
                echo "<input type='hidden' name='room_type_id_" . $roomCount  . "' value='" . htmlspecialchars($row['room_id']) . "' >";
                echo "</td>";
                echo "</tr>";

                $roomCount++;
            }
        } else {
            echo "oda bulunamadı";
        }

        echo "<input type='hidden' name='room_count' value='" . $roomCount . "' >";

        echo "</tbody>";
        echo "</table>";
        
        //Tarihlere min max ekliyoruz 
        $sql_dates = "SELECT DISTINCT start_date, finish_date FROM contracts WHERE contract_id=? AND hotel_id = ?";
        $stmt_dates = $baglanti->prepare($sql_dates);
        $stmt_dates->bind_param("ii", $contract_id, $hotel_id);
        $stmt_dates->execute();
        $result_dates = $stmt_dates->get_result();
        $row_dates = $result_dates->fetch_assoc();

        echo "<table class='table dotted-rows'>
            <tbody>
                <tr>
                    <td>
                        <p>Booking Period</p><input type='date' name='booking_period_start' min='".$row_dates['start_date']."' max='".$row_dates['finish_date']."' id='booking_period_start' class='form-control checkout' required>
                    </td>
                    <td>
                        <p>&nbsp;</p><input type='date' name='booking_period_end' min='".$row_dates['start_date']."' max='".$row_dates['finish_date']."' id='booking_period_end' class='form-control checkout' required>
                    </td>
                    <td>
                        <p>Travel Date</p><input type='date' name='travel_date_start' min='".$row_dates['start_date']."' max='".$row_dates['finish_date']."' id='travel_date_start' class='form-control checkout' required>
                    </td>
                    <td>
                        <p>&nbsp;</p><input type='date' name='travel_date_end' min='".$row_dates['start_date']."' max='".$row_dates['finish_date']."' id='travel_date_end' class='form-control checkout' required>
                    </td>
                </tr>
            </tbody>
        </table>";


        exit;
    }
}

?>



<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Rate Promotion</title>
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
                        <h1> Add Rate Promotion</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Rezervasyon Modülü</a></li>
                            <li class="breadcrumb-item active">Add Rate Promotion</li>
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
                                
                                <br>

                                <?php
                                $sql = "SELECT DISTINCT contract_id, contract_name FROM contracts WHERE hotel_id=?";
                                $stmt = $baglanti->prepare($sql);
                                $stmt->bind_param("i", $hotel_id);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows > 0) { ?>
                                    <div class="form-group border p-3 rounded">
                                        <h4>Contracts</h4>
                                        <select name="selectContract" class="form-control contract-select" id="contract_name">
                                            <option value="0">Kontrat seçiniz</option>
                                            <?php while ($row = $result->fetch_assoc()) { ?>
                                                <option value="<?= htmlspecialchars($row["contract_id"]) ?>" class="form-control"><?= htmlspecialchars($row["contract_name"]) ?></option>

                                            <?php } ?>
                                        </select>
                                    </div>
                                <?php } else { ?>
                                    <div class="alert alert-danger " role='alert'> Kontrat bulunamadı</div>

                                <?php exit;
                                } ?>
                                <br>

                                <div class="form-group">
                                    <h4>Promotion Code</h4>
                                    <input type="text" class="form-control" name="promotion_code" id="">
                                </div>


                                <input type="hidden" name="room_count" value="<?= htmlspecialchars($roomCount) ?>">

                                <div class="flex-container">
                                    <div>
                                        <p>Min. Night</p>
                                        <input type="number" min="0" name="min_nights" class="form-control checkout" required>
                                    </div>
                                    <div>
                                        <p>Day Before</p>
                                        <input type="number" min="0" name="days_before" class="form-control checkout" required>
                                    </div>
                                    <div>
                                        <input type="checkbox" name="" id="">
                                        <label for="">Valid for all Arrivals</label>
                                    </div>
                                </div>
                                <br>

                                <h4>Days</h4>
                                <div class="container mt-3">
                                    <div class="row g-2">
                                        <div class="col-auto">
                                            <div class="day-card">
                                                <input type="checkbox" name="monday" value="1" checked> Monday
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="day-card">
                                                <input type="checkbox" name="tuesday" value="1" checked> Tuesday
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="day-card">
                                                <input type="checkbox" name="wednesday" value="1" checked> Wednesday
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="day-card">
                                                <input type="checkbox" name="thursday" value="1" checked> Thursday
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="day-card">
                                                <input type="checkbox" name="friday" value="1" checked> Friday
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="day-card">
                                                <input type="checkbox" name="saturday" value="1" checked> Saturday
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <div class="day-card">
                                                <input type="checkbox" name="sunday" value="1" checked> Sunday
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>

                                <div class="table-rooms"></div>
                                <br>


                                <hr style="border-top: 1px dotted #000;">
                                <button type="submit" id="saveButton" style="width: 100%;" class="btn btn-success"> Save </button>
                            </form>


                            <?php

                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                                $contract_id = intval($_POST['selectContract']);
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

                                

                                $sql = "INSERT INTO rate_promotion(contract_id, hotel_id, monday, tuesday, wednesday, thursday, friday, saturday, sunday, promo_code, booking_period_start, booking_period_end, travel_date_start, travel_date_end, days_before, min_nights) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                                $stmt = $baglanti->prepare($sql);
                                $stmt->bind_param("iiiiiiiiisssssii", $contract_id, $hotel_id, $monday, $tuesday, $wednesday, $thursday, $friday, $saturday, $sunday, $promotion_code, $booking_period_start, $booking_period_end, $travel_date_start, $travel_date_end, $days_before, $min_nights);
                                $stmt->execute();
                                $rate_promotion_id = $stmt->insert_id;
                                $stmt->close();

                                for ($i = 0; $i < $room_count; $i++) {
                                    $rate = $_POST["rate_room_{$i}"];
                                    $room_type_id = isset($_POST["room_type_id_{$i}"]) ? $_POST["room_type_id_{$i}"] : 0;

                                    if ($room_type_id !== 0) {
                                        $sql = "INSERT INTO rate_promotion_rooms(rate_promotion_id, room_type_id, rate) VALUES (?,?,?)";
                                        $stmt = $baglanti->prepare($sql);
                                        $stmt->bind_param("iii", $rate_promotion_id, $room_type_id, $rate);
                                        $stmt->execute();
                                        $stmt->close();
                                    }
                                }

                                header("Location: ratepromotionlist.php?hotel_id=$hotel_id&success=2"); // aynı sayfa ama GET ile
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
        $(document).on('change', '.contract-select', function() {

            const contractID = $(this).val();

            $.post('', {
                ajax: 'get_contract',
                contract_id: contractID
            }, function(data) {
                $('.table-rooms').html(data);

            });

        });
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