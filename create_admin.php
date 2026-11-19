<?php
/**
 * Script: Buat Akun Admin
 * 
 * Jalankan dari terminal di folder project:
 *   php create_admin.php
 *
 * Setelah berhasil, hapus file ini untuk keamanan.
 */

// Koneksi manual ke MySQL (sama seperti .env)
$host     = '127.0.0.1';
$port     = 3306;
$dbname   = 'geigaramonitoring';
$dbuser   = 'root';
$dbpass   = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $dbuser,
        $dbpass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Koneksi database berhasil.\n";
} catch (Exception $e) {
    die("❌ Gagal konek database: " . $e->getMessage() . "\n");
}

// Cek struktur tabel user
$columns = [];
$stmt = $pdo->query("DESCRIBE `user`");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $columns[] = $row['Field'];
}
echo "📋 Kolom tabel 'user': " . implode(', ', $columns) . "\n";

// Data admin yang akan dibuat
$adminUsername = 'admin';
$adminPassword = 'admin123';
$hashedPassword = password_hash($adminPassword, PASSWORD_BCRYPT);

// Cek apakah sudah ada
$check = $pdo->prepare("SELECT COUNT(*) FROM `user` WHERE `user` = ?");
$check->execute([$adminUsername]);
$exists = (int) $check->fetchColumn();

if ($exists > 0) {
    echo "⚠️  Akun '{$adminUsername}' sudah ada. Memperbarui password & role...\n";

    $sql = "UPDATE `user` SET `password` = ?, `role` = 'admin' WHERE `user` = ?";
    $pdo->prepare($sql)->execute([$hashedPassword, $adminUsername]);

    echo "✅ Akun '{$adminUsername}' diperbarui: password='admin123', role='admin'\n";
} else {
    // Insert akun baru
    $data = [];
    if (in_array('user', $columns))     $data['user']     = $adminUsername;
    if (in_array('username', $columns)) $data['username'] = $adminUsername;
    if (in_array('name', $columns))     $data['name']     = 'Administrator';
    if (in_array('email', $columns))    $data['email']    = 'admin@monitoring.local';
    if (in_array('password', $columns)) $data['password'] = $hashedPassword;
    if (in_array('role', $columns))     $data['role']     = 'admin';

    $cols   = implode(', ', array_map(fn($c) => "`{$c}`", array_keys($data)));
    $places = implode(', ', array_fill(0, count($data), '?'));

    $pdo->prepare("INSERT INTO `user` ({$cols}) VALUES ({$places})")
        ->execute(array_values($data));

    echo "✅ Akun admin berhasil dibuat!\n";
}

echo "\n";
echo "======================================\n";
echo "  LOGIN INFO\n";
echo "======================================\n";
echo "  Username : admin\n";
echo "  Password : admin123\n";
echo "  Role     : admin\n";
echo "======================================\n";
echo "\n⚠️  Hapus file ini setelah selesai:\n";
echo "   del create_admin.php\n\n";
