<?php
/**
 * Fix Compound Image Paths
 *
 * This script updates the image paths in the database to reference
 * the correct compound ID folders.
 */

if ($argc < 2) {
    echo "\nUsage: php fix_compound_images.php [analyze|fix]\n\n";
    echo "  analyze - Show what will be changed\n";
    echo "  fix     - Apply the fixes to the database\n\n";
    exit(1);
}

$mode = $argv[1];

$host = 'localhost';
$username = 'laravel';
$password = 'laravel123';
$database = 'real_state';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "\n╔═══════════════════════════════════════════════════════════════════╗\n";
    echo "║     Compound Image Path Fix Tool                                  ║\n";
    echo "╚═══════════════════════════════════════════════════════════════════╝\n\n";

    // Get storage path
    $storage_path = '/var/www/realestate/storage/app/public/compound-images';

    // Get list of existing folders
    $folders = [];
    if (is_dir($storage_path)) {
        $dir_contents = scandir($storage_path);
        foreach ($dir_contents as $item) {
            if ($item != '.' && $item != '..' && is_dir($storage_path . '/' . $item)) {
                if (is_numeric($item)) {
                    $folders[] = (int)$item;
                }
            }
        }
    }

    echo "📁 Found " . count($folders) . " image folders in storage\n\n";

    // Get all compounds with local images
    $query = "
        SELECT id, project, images
        FROM compounds
        WHERE images IS NOT NULL
        AND images != '[]'
        AND images NOT LIKE '%https://%'
        AND images NOT LIKE '%http://%'
        ORDER BY id
    ";

    $stmt = $pdo->query($query);
    $compounds = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $to_update = [];
    $mismatches = 0;
    $matches = 0;
    $no_folder = 0;

    foreach ($compounds as $compound) {
        $id = $compound['id'];
        $project = $compound['project'];
        $images = json_decode($compound['images'], true);

        if (!empty($images) && is_array($images)) {
            $firstImage = $images[0];

            // Extract folder ID from path
            if (preg_match('/compound-images\/(\d+)\//', $firstImage, $regex_matches)) {
                $currentFolderID = $regex_matches[1];

                if ($currentFolderID != $id) {
                    $mismatches++;

                    // Check if correct folder exists
                    if (in_array($id, $folders)) {
                        // Correct folder exists, we can fix this!
                        $newImages = [];
                        foreach ($images as $img) {
                            $newImages[] = preg_replace(
                                '/compound-images\/\d+\//',
                                "compound-images/$id/",
                                $img
                            );
                        }

                        $to_update[] = [
                            'id' => $id,
                            'project' => $project,
                            'old_folder' => $currentFolderID,
                            'new_folder' => $id,
                            'old_images' => $images,
                            'new_images' => $newImages
                        ];
                    } else {
                        $no_folder++;
                        echo "⚠️  Compound $id ($project) - folder $id doesn't exist, currently references folder $currentFolderID\n";
                    }
                } else {
                    $matches++;
                }
            }
        }
    }

    echo "\n📊 Analysis Results:\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    echo sprintf("  ✅ Already Correct: %d\n", $matches);
    echo sprintf("  🔧 Can Be Fixed: %d\n", count($to_update));
    echo sprintf("  ⚠️  No Folder Found: %d\n", $no_folder);
    echo sprintf("  Total Mismatches: %d\n\n", $mismatches);

    if (count($to_update) > 0) {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        echo "🔧 Compounds that will be updated (first 10):\n\n";
        echo sprintf("%-8s %-35s %-10s %-10s\n", "ID", "Project", "Old Folder", "New Folder");
        echo "─────────────────────────────────────────────────────────────────\n";

        foreach (array_slice($to_update, 0, 10) as $item) {
            echo sprintf(
                "%-8d %-35s %-10s %-10s\n",
                $item['id'],
                substr($item['project'], 0, 34),
                $item['old_folder'],
                $item['new_folder']
            );
        }

        if (count($to_update) > 10) {
            echo sprintf("\n... and %d more\n", count($to_update) - 10);
        }

        echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

        if ($mode === 'fix') {
            echo "🚀 Applying fixes...\n\n";

            $updateStmt = $pdo->prepare("UPDATE compounds SET images = ? WHERE id = ?");

            $updated = 0;
            $errors = 0;

            foreach ($to_update as $item) {
                try {
                    $updateStmt->execute([
                        json_encode($item['new_images']),
                        $item['id']
                    ]);
                    $updated++;
                    echo "  ✓ Updated compound {$item['id']} ({$item['project']})\n";
                } catch (Exception $e) {
                    $errors++;
                    echo "  ✗ Error updating compound {$item['id']}: {$e->getMessage()}\n";
                }
            }

            echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            echo "✅ Update Complete!\n\n";
            echo sprintf("  Successfully updated: %d\n", $updated);
            echo sprintf("  Errors: %d\n\n", $errors);

            if ($errors === 0) {
                echo "╔═══════════════════════════════════════════════════════════════════╗\n";
                echo "║  ✅ All image paths have been fixed successfully!                 ║\n";
                echo "╚═══════════════════════════════════════════════════════════════════╝\n\n";
            }
        } else {
            echo "ℹ️  This is ANALYZE mode. No changes have been made.\n";
            echo "   Run with 'fix' parameter to apply changes:\n";
            echo "   php fix_compound_images.php fix\n\n";
        }
    } else {
        echo "✅ No fixes needed! All image paths are correct.\n\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
