<?php
require_once 'includes/config.php';

echo "<h2>AuditAI Database Fix Tool</h2>";

try {
    // 1. Add scores column
    $pdo->exec("ALTER TABLE leads ADD COLUMN IF NOT EXISTS scores TEXT AFTER score");
    echo "✅ Column 'scores' checked/added.<br>";

    // 2. Add metadata column
    $pdo->exec("ALTER TABLE leads ADD COLUMN IF NOT EXISTS metadata TEXT AFTER scores");
    echo "✅ Column 'metadata' checked/added.<br>";

    // 3. Update status ENUM
    // Note: Standard MySQL doesn't support ADD IF NOT EXISTS for ENUM changes easily,
    // so we just try to modify it.
    $pdo->exec("ALTER TABLE leads MODIFY COLUMN status ENUM('Pending', 'Preparing', 'Ready', 'Contacted', 'Qualified', 'Failed') DEFAULT 'Pending'");
    echo "✅ Column 'status' ENUM updated.<br>";

    echo "<br><p style='color: green; font-weight: bold;'>Database fix completed successfully! You can now delete this file and try auditing again.</p>";
    echo "<a href='index.php'>Go back to Dashboard</a>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
