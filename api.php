<?php
// api.php
require_once 'db.php';
// session_start(); // Already started in db.php

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// Custom Week Logic
// Cycle: 1, 2, 3, 4, 1, ...
// Reference: Mon 09 Feb 2026 is Week 4.
// So Mon 16 Feb 2026 is Week 1.
// 19 Jan 2026 was Week 1.
function getWeekType($dateStr) {
    $targetDate = new DateTime($dateStr);
    
    // Find Monday of the target week (ISO-8601 starts Monday)
    // If today is Mon, it stays Mon. If Sun, it goes back 6 days to Mon.
    // Verify: 08.02.2026 (Sun) -> Monday is 02.02.2026.
    // 09.02.2026 (Mon) -> Monday is 09.02.2026.
    
    $monday = clone $targetDate;
    if ($targetDate->format('N') != 1) {
        $monday->modify('last monday');
    }
    
    // Reference Date: Monday of a "Week 1".
    // 16 Feb 2026 is Week 1.
    $refDate = new DateTime('2026-02-16');
    
    // Calculate difference in weeks
    $interval = $refDate->diff($monday);
    $daysDiff = $interval->days;
    $weeksDiff = floor($daysDiff / 7);
    
    if ($monday < $refDate) {
        // Before ref date
        $weeksDiff = -$weeksDiff;
    }
    
    // Calculate Cycle Position (1-4)
    // Since ref is Week 1, position is 1 + (weeksDiff % 4).
    // Handling negative modulo in PHP: ($a % $n + $n) % $n
    
    $cycleIndex = ($weeksDiff % 4 + 4) % 4; 
    // cycleIndex 0 -> Week 1 (Ref Date + 0 weeks)
    // cycleIndex 1 -> Week 2
    // cycleIndex 2 -> Week 3
    // cycleIndex 3 -> Week 4
    
    $weekNum = $cycleIndex + 1;
    
    // Odd: 1, 3. Even: 2, 4.
    return ($weekNum % 2 != 0) ? 'odd' : 'even';
}

try {
    if (!$conn) {
        throw new Exception("Database connection not established");
    }

    switch ($action) {
        case 'login':
            $input = json_decode(file_get_contents('php://input'), true);
            $username = $input['username'] ?? '';
            $password = $input['password'] ?? '';

            $query = "SELECT * FROM users_simple WHERE username = ? LIMIT 1";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);

            if ($user && $user['password'] === $password) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['is_admin'] = true;
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            }
            break;

        case 'logout':
            session_destroy();
            echo json_encode(['success' => true]);
            break;

        case 'get_schedule':
            $start = $_GET['start'] ?? date('Y-m-d');
            $days = 7; 
            
            $result = [];
            $startDate = new DateTime($start);
            
            // Adjust to Monday
            if ($startDate->format('N') != 1) {
                $startDate->modify('last monday');
            }

            for ($i = 0; $i < $days; $i++) {
                $currentDate = clone $startDate;
                $currentDate->modify("+$i days");
                $dateStr = $currentDate->format('Y-m-d');
                $dayOfWeek = $currentDate->format('N');
                $weekType = getWeekType($dateStr);
                
                if ($dayOfWeek == 7) continue; 

                // Fetch base schedule
                $queryBase = "SELECT pair_number, subject_name FROM week_template WHERE week_type = ? AND day_of_week = ?";
                $stmtBase = mysqli_prepare($conn, $queryBase);
                mysqli_stmt_bind_param($stmtBase, "si", $weekType, $dayOfWeek);
                mysqli_stmt_execute($stmtBase);
                $resBase = mysqli_stmt_get_result($stmtBase);
                
                $basePairs = [];
                while ($row = mysqli_fetch_assoc($resBase)) {
                    $basePairs[$row['pair_number']] = $row['subject_name'];
                }

                // Fetch overrides
                $queryOverride = "SELECT pair_number, new_subject_name, is_cancelled FROM schedule_overrides WHERE override_date = ?";
                $stmtOverride = mysqli_prepare($conn, $queryOverride);
                mysqli_stmt_bind_param($stmtOverride, "s", $dateStr);
                mysqli_stmt_execute($stmtOverride);
                $resOverride = mysqli_stmt_get_result($stmtOverride);
                $overrides = mysqli_fetch_all($resOverride, MYSQLI_ASSOC);

                // Merge
                $finalPairs = [];
                for ($p = 1; $p <= 6; $p++) {
                   $finalPairs[$p] = [
                       'subject' => $basePairs[$p] ?? null,
                       'is_override' => false,
                       'is_cancelled' => false
                   ];
                }

                foreach ($overrides as $ov) {
                    $p = $ov['pair_number'];
                    if ($ov['is_cancelled']) {
                        $finalPairs[$p]['subject'] = null;
                        $finalPairs[$p]['is_cancelled'] = true;
                    } else {
                        $finalPairs[$p]['subject'] = $ov['new_subject_name'];
                    }
                    $finalPairs[$p]['is_override'] = true;
                }
                
                $result[] = [
                    'date' => $dateStr,
                    'day_name' => $currentDate->format('l'),
                    'week_type' => $weekType,
                    'is_today' => ($dateStr === date('Y-m-d')),
                    'pairs' => $finalPairs
                ];
            }

            echo json_encode([
                'success' => true, 
                'schedule' => $result, 
                'is_admin' => isset($_SESSION['is_admin']),
                'week_type' => getWeekType($startDate->format('Y-m-d'))
            ]);
            break;

        case 'update_slot':
            if (!isset($_SESSION['is_admin'])) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $date = $input['date'];
            $pair = $input['pair'];
            $subject = $input['subject'];

            if (empty($date) || empty($pair)) {
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
                exit;
            }

            $isCancelled = ($subject === '' || $subject === null) ? 1 : 0;
            $newSubject = $isCancelled ? null : $subject;

            // Upsert override
            $query = "
                INSERT INTO schedule_overrides (override_date, pair_number, new_subject_name, is_cancelled)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE new_subject_name = ?, is_cancelled = ?
            ";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "sisisi", $date, $pair, $newSubject, $isCancelled, $newSubject, $isCancelled);

            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
