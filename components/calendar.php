<?php
function getReservasByDate($mysqli, $date) {
    $stmt = $mysqli->prepare("
        SELECT r.*, l.nome as lab_nome, u.nome as professor_nome, h.inicio, h.fim 
        FROM reservas r 
        JOIN laboratorios l ON r.laboratorio_id = l.id 
        JOIN usuarios u ON r.usuario_id = u.id 
        JOIN horarios h ON r.horario_id = h.id 
        WHERE r.data = ? AND r.status = 'confirmada'
        ORDER BY h.inicio
    ");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getCalendarEvents($mysqli, $month, $year) {
    $firstDay = "$year-$month-01";
    $lastDay = date('Y-m-t', strtotime($firstDay));
    
    $stmt = $mysqli->prepare("
        SELECT r.data, COUNT(*) as total, 
        GROUP_CONCAT(DISTINCT l.nome) as labs 
        FROM reservas r 
        JOIN laboratorios l ON r.laboratorio_id = l.id 
        WHERE r.data BETWEEN ? AND ? 
        AND r.status = 'confirmada' 
        GROUP BY r.data
    ");
    $stmt->bind_param("ss", $firstDay, $lastDay);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Array com nomes dos meses em português
function getMesPortugues($month) {
    $meses = [
        1 => 'Janeiro',
        2 => 'Fevereiro',
        3 => 'Março',
        4 => 'Abril',
        5 => 'Maio',
        6 => 'Junho',
        7 => 'Julho',
        8 => 'Agosto',
        9 => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro'
    ];
    return $meses[$month];
}

// Função para ajustar mês e ano quando navegamos pelo calendário
function adjustMonthYear($month, $year) {
    if ($month > 12) {
        $month = 1;
        $year++;
    } elseif ($month < 1) {
        $month = 12;
        $year--;
    }
    return ['month' => $month, 'year' => $year];
}

?>

<div class="calendar-component">
    <?php
    $month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
    $year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));
    
    // Ajustar mês e ano
    $adjusted = adjustMonthYear($month, $year);
    $month = $adjusted['month'];
    $year = $adjusted['year'];
    
    $firstDay = mktime(0, 0, 0, $month, 1, $year);
    $daysInMonth = date('t', $firstDay);
    $dayOfWeek = date('w', $firstDay);
    
    $events = getCalendarEvents($mysqli, $month, $year);
    $eventsByDate = array_column($events, null, 'data');
    
    $monthName = getMesPortugues($month);
    
    // Calcular mês anterior e próximo mês
    $prevMonth = $month - 1;
    $prevYear = $year;
    if ($prevMonth < 1) {
        $prevMonth = 12;
        $prevYear--;
    }
    
    $nextMonth = $month + 1;
    $nextYear = $year;
    if ($nextMonth > 12) {
        $nextMonth = 1;
        $nextYear++;
    }
    ?>

    <div class="calendar-header d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><?php echo $monthName . ' ' . $year; ?></h4>
        <div class="btn-group">
            <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="btn btn-outline-primary">
                <i class="bi bi-chevron-left"></i>
            </a>
            <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="btn btn-outline-primary">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>

    <table class="table table-bordered calendar-table">
        <thead>
            <tr>
                <th>Dom</th>
                <th>Seg</th>
                <th>Ter</th>
                <th>Qua</th>
                <th>Qui</th>
                <th>Sex</th>
                <th>Sáb</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <?php
                for($i = 0; $i < $dayOfWeek; $i++) {
                    echo "<td></td>";
                }
                
                for($day = 1; $day <= $daysInMonth; $day++) {
                    $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $hasEvents = isset($eventsByDate[$date]);
                    $isToday = $date === date('Y-m-d');
                    
                    if(($day + $dayOfWeek - 1) % 7 == 0 && $day != 1) {
                        echo "</tr><tr>";
                    }
                    
                    $tdClass = 'calendar-day';
                    if ($hasEvents) $tdClass .= ' has-events';
                    if ($isToday) $tdClass .= ' today';
                    
                    echo "<td class='$tdClass'>";
                    echo "<div class='day-number'>$day</div>";
                    
                    if($hasEvents) {
                        $event = $eventsByDate[$date];
                        echo "<div class='event-indicator' data-bs-toggle='tooltip' title='{$event['total']} reservas'>";
                        echo "<small class='text-primary'>{$event['total']} lab(s)</small>";
                        echo "</div>";
                    }
                    
                    echo "</td>";
                }
                
                while(($day + $dayOfWeek - 1) % 7 != 0) {
                    echo "<td></td>";
                    $day++;
                }
                ?>
            </tr>
        </tbody>
    </table>
</div> 