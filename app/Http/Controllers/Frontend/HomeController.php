<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Redirect;
use Laravel\Sanctum\PersonalAccessToken;
use DateTime;
use Exception;
use PDO;
use PDOException;

class HomeController extends Controller
{
   
   
    public function transfer_database(){
$source_db = 'agriculture';
$dest_db = 'agriculture_laravel';

$host = 'localhost';
$user = 'root';
$password = '';

try {
    // Source DB connection
    $source_dsn = "mysql:host=$host;dbname=$source_db;charset=utf8mb4";
    $source_pdo = new PDO($source_dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Destination DB connection
    $dest_dsn = "mysql:host=$host;dbname=$dest_db;charset=utf8mb4";
    $dest_pdo = new PDO($dest_dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Get all tables from source database
    $tablesStmt = $source_pdo->prepare("
        SELECT table_name
        FROM information_schema.tables
        WHERE table_schema = :source_db
    ");
    $tablesStmt->execute(['source_db' => $source_db]);
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

    $now = date('Y-m-d H:i:s');

    foreach ($tables as $table) {
        // Get columns from source table
        $srcColsStmt = $source_pdo->prepare("
            SELECT column_name 
            FROM information_schema.columns
            WHERE table_schema = :source_db AND table_name = :table
        ");
        $srcColsStmt->execute(['source_db' => $source_db, 'table' => $table]);
        $source_columns = $srcColsStmt->fetchAll(PDO::FETCH_COLUMN);

        // Get columns from destination table
        $destColsStmt = $dest_pdo->prepare("
            SELECT column_name
            FROM information_schema.columns
            WHERE table_schema = :dest_db AND table_name = :table
        ");
        $destColsStmt->execute(['dest_db' => $dest_db, 'table' => $table]);
        $dest_columns = $destColsStmt->fetchAll(PDO::FETCH_COLUMN);

        $add_now_created = false;
        $add_now_updated = false;

        $insert_cols = $source_columns;

        if (in_array('created_at', $dest_columns) && !in_array('created_at', $source_columns)) {
            $insert_cols[] = 'created_at';
            $add_now_created = true;
        }
        if (in_array('updated_at', $dest_columns) && !in_array('updated_at', $source_columns)) {
            $insert_cols[] = 'updated_at';
            $add_now_updated = true;
        }

        // Fetch all data from source table
        $dataStmt = $source_pdo->prepare("SELECT * FROM `$table`");
        $dataStmt->execute();
        $rows = $dataStmt->fetchAll();

        if (empty($rows)) {
            echo "Table $table is empty, skipping.\n";
            continue;
        }

        // Prepare insert statement
        $placeholders = array_fill(0, count($source_columns), '?');
        if ($add_now_created) {
            $placeholders[] = '?';
        }
        if ($add_now_updated) {
            $placeholders[] = '?';
        }

        $insert_cols_str = implode(", ", array_map(function($col){ return "`$col`"; }, $insert_cols));
        $placeholders_str = implode(", ", $placeholders);

        $insertSql = "INSERT INTO `$table` ($insert_cols_str) VALUES ($placeholders_str)";
        $insertStmt = $dest_pdo->prepare($insertSql);

        // Insert each row into destination
        foreach ($rows as $row) {
            $data = array_values($row);

            if ($add_now_created) {
                $data[] = $now;
            }
            if ($add_now_updated) {
                $data[] = $now;
            }

            try {
                $insertStmt->execute($data);
            } catch (Exception $e) {
                echo "Error inserting row in table $table: " . $e->getMessage() . "\n";
            }
        }

        echo "Copied data for table $table\n";
    }

} catch (PDOException $e) {
    die("DB error: " . $e->getMessage());
}
}
    // ============================= START INDEX ============================ 
    public function index(Request $req)
    {
     
        return view('welcome')->withTitle('');
    }
    public function about(Request $req)
    {
     
        return view('about')->withTitle('');
    }
    public function contact(Request $req)
    {
     
        return view('contact')->withTitle('');
    }
    
    public function doctor(Request $req)
    {
     
        return view('doctor')->withTitle('');
    }
    public function farmer(Request $req)
    {
     
        return view('farmer')->withTitle('');
    }
    public function gallery(Request $req)
    {
     
        return view('gallery')->withTitle('');
    }
    public function privacy_policy(Request $req)
    {
     
        return view('privacy_policy')->withTitle('');
    }
    public function privacy(Request $req)
    {
     
        return view('privacy')->withTitle('');
    }
    public function refund (Request $req)
    {
     
        return view('refund-cancellation-policy')->withTitle('');
    }
    public function services (Request $req)
    {
     
        return view('services')->withTitle('');
    }
    public function shipping_delivery (Request $req)
    {
     
        return view('shipping_delivery')->withTitle('');
    }
    public function terms_and_conditions (Request $req)
    {
     
        return view('terms_and_conditions')->withTitle('');
    }
    public function vendor (Request $req)
    {
     
        return view('vendor')->withTitle('');
    }
}
