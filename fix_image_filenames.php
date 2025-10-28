<?php
/**
 * Fix Image Filenames in Database
 *
 * Updates the image filenames to match actual files on server
 * Example: compound-images/509/compound_1144_img_0.webp
 *       -> compound-images/509/compound_509_img_0.webp
 */

if ($argc < 2) {
    echo "\nUsage: php fix_image_filenames.php [analyze|fix]\n\n";
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
    echo "║     Fix Image Filenames in Database                              ║\n";
    echo "╚═══════════════════════════════════════════════════════════════════╝\n\n";

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
    $already_correct = 0;

    foreach ($compounds as $compound) {
        $id = $compound['id'];
        $project = $compound['project'];
        $images = json_decode($compound['images'], true);

        if (!empty($images) && is_array($images)) {
            $needsUpdate = false;
            $newImages = [];

            foreach ($images as $img) {
                // Check if filename has wrong ID
                // Pattern: compound-images/FOLDER_ID/compound_OLD_ID_img_X.webp
                if (preg_match('/compound-images\/(\d+)\/compound_(\d+)_(.+)/', $img, $matches)) {
                    $folderId = $matches[1];
                    $filenameId = $matches[2];
                    $suffix = $matches[3]; // img_0.webp, masterplan.jpg, etc.

                    // If folder ID matches compound ID but filename ID doesn't
                    if ($folderId == $id && $filenameId != $id) {
                        $needsUpdate = true;
                        // Fix the filename to use the compound ID
                        $newImages[] = "compound-images/$id/compound_{$id}_{$suffix}";
                    } else {
                        $newImages[] = $img;
                    }
                } else {
                    $newImages[] = $img;
                }
            }

            if ($needsUpdate) {
                $to_update[] = [
                    'id' => $id,
                    'project' => $project,
                    'old_images' => $images,
                    'new_images' => $newImages
                ];
            } else {
                $already_correct++;
            }
        }
    }

    echo "📊 Analysis Results:\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    echo sprintf("  Total Compounds: %d\n", count($compounds));
    echo sprintf("  ✅ Already Correct: %d\n", $already_correct);
    echo sprintf("  🔧 Need Filename Fix: %d\n\n", count($to_update));

    if (count($to_update) > 0) {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        echo "🔧 Compounds with incorrect filenames (first 10):\n\n";
        echo sprintf("%-6s %-30s %-15s\n", "ID", "Project", "Example Change");
        echo "─────────────────────────────────────────────────────────────────\n";

        foreach (array_slice($to_update, 0, 10) as $item) {
            $oldFilename = basename($item['old_images'][0]);
            $newFilename = basename($item['new_images'][0]);

            echo sprintf(
                "%-6d %-30s %s\n",
                $item['id'],
                substr($item['project'], 0, 29),
                $oldFilename . ' → ' . $newFilename
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

                    if ($updated <= 5) {
                        echo "  ✓ Updated compound {$item['id']} ({$item['project']})\n";
                    }
                } catch (Exception $e) {
                    $errors++;
                    echo "  ✗ Error updating compound {$item['id']}: {$e->getMessage()}\n";
                }
            }

            if ($updated > 5) {
                echo "  ✓ ... and " . ($updated - 5) . " more\n";
            }

            echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            echo "✅ Update Complete!\n\n";
            echo sprintf("  Successfully updated: %d\n", $updated);
            echo sprintf("  Errors: %d\n\n", $errors);

            if ($errors === 0) {
                echo "╔═══════════════════════════════════════════════════════════════════╗\n";
                echo "║  ✅ All image filenames have been fixed successfully!             ║\n";
                echo "║  Images should now be accessible via the domain.                  ║\n";
                echo "╚═══════════════════════════════════════════════════════════════════╝\n\n";
            }
        } else {
            echo "ℹ️  This is ANALYZE mode. No changes have been made.\n";
            echo "   Run with 'fix' parameter to apply changes:\n";
            echo "   php fix_image_filenames.php fix\n\n";
        }
    } else {
        echo "✅ No fixes needed! All image filenames are correct.\n\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
