<?php
include('dbcon.php');
include('error_handler.php');
include('header.php');

$keystorePath = 'C:\xampp\htdocs\certmanager\keystore\.keystore'; // keystore path
$keystorePassword = 'password'; // keystore password
$logFile = 'esb.log'; // Log file path
$jsonFile = 'esbcertparse.json'; // JSON file to store parsed details
$keytoolPath = 'C:\Program Files\Microsoft\jdk-11.0.24.8-hotspot\bin\keytool.exe'; // Path to keytool

// Command to execute
$keytoolCommand = "\"$keytoolPath\" -list -v -keystore \"$keystorePath\" -storepass \"$keystorePassword\"";

// Execute keytool command and overwrite the esb.log file
$output = shell_exec($keytoolCommand);

// Write output to esb.log, overwriting the file each time
file_put_contents($logFile, $output);

$parsedDetails = [];

// Parse the log file
if (file_exists($logFile)) {
    $logContents = file($logFile);
    $entry = [];
    $validUntilFound = false; // Track if we found the expiration date
    $domainFound = false; // Track if we found the domain

    foreach ($logContents as $line) {
        $line = trim($line);

        // Extract domain (Owner)
        if (strpos($line, 'Owner: CN=') === 0) {
            // Extract the domain name after 'Owner: '
            $fullOwner = trim(substr($line, strlen('Owner: '))); // Get the full Owner string
            preg_match('/CN=([^,]+)/', $fullOwner, $matches); // Use regex to extract CN
            $entry['domain'] = !empty($matches) ? $matches[1] : 'Unknown'; // Set domain to CN if found
            $domainFound = true; // Indicate that we found the domain
        }

        // Extract alias name
        if (strpos($line, 'Alias name:') === 0) {
            if (!empty($entry)) {
                // Save previous entry if exists
                $parsedDetails[] = $entry;
            }
            $entry = ['alias' => trim(substr($line, strlen('Alias name:')))];
            $validUntilFound = false; // Reset for each new entry
        }

        // Extract creation date
        if (strpos($line, 'Creation date:') === 0) {
            $entry['creation_date'] = trim(substr($line, strlen('Creation date:')));
        }

        // Extract valid dates
        if (strpos($line, 'Valid from:') === 0) {
            // Save the "Valid from" date if needed
            $entry['valid_from'] = trim(substr($line, strlen('Valid from:')));
            $validUntilFound = true; // Indicate that we are expecting the "until" line next
        }

        // Extract expiration date
        if ($validUntilFound && strpos($line, 'until:') !== false) {
            // Get the expiration date string
            $expirationDateString = trim(substr($line, strpos($line, 'until:') + strlen('until:')));

            // Convert to DateTime and format it
            $dateTime = DateTime::createFromFormat('D M d H:i:s T Y', $expirationDateString);
            if ($dateTime) {
                $entry['expiration_date'] = $dateTime->format('Y-m-d H:i:s'); // Change to desired format
            } else {
                $entry['expiration_date'] = 'Unknown';
            }

            $validUntilFound = false; // Reset after finding the expiration date
        }

        // Extract entry type
        if (strpos($line, 'Entry type:') === 0) {
            $entry['type'] = trim(substr($line, strlen('Entry type:')));
        }
    }

    // Save the last entry if exists
    if (!empty($entry)) {
        $parsedDetails[] = $entry;
    }

    // Save parsed details to JSON for easy retrieval
    file_put_contents($jsonFile, json_encode($parsedDetails, JSON_PRETTY_PRINT));
} else {
    echo "Log file not found!";
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">ESB Keystore</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table m-0">
                        <thead>
                            <tr>
                                <th>#</th> <!-- Updated to # -->
                                <th>Cert Domain</th> <!-- Changed from Cert Name to Cert Domain -->
                                <th>Creation Date</th>
                                <th>Expiration Date</th>
                                <th>Cert Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Load parsed details from the JSON file
                            $parsedDetails = json_decode(file_get_contents($jsonFile), true);
                            if ($parsedDetails) {
                                foreach ($parsedDetails as $index => $cert) {
                                    // Calculate the expiration status
                                    $now = new DateTime();
                                    $expirationDate = $cert['expiration_date'];
                                    $expiry = new DateTime($expirationDate);
                                    $interval = $now->diff($expiry);
                                    
                                    // Determine status class
                                    if ($interval->invert) {
                                        $statusClass = 'text-bg-danger'; // Expired
                                    } elseif ($interval->days <= 30) {
                                        $statusClass = 'text-bg-warning'; // Expiring soon
                                    } else {
                                        $statusClass = 'text-bg-success'; // Valid
                                    }

                                    echo "<tr>
                                        <td>" . ($index + 1) . "</td> <!-- Changed to display just numbers -->
                                        <td>" . htmlspecialchars($cert['domain'], ENT_QUOTES) . "</td> 
                                        <td>" . htmlspecialchars($cert['creation_date'], ENT_QUOTES) . "</td>
                                        <td><span class=\"badge {$statusClass}\">" . htmlspecialchars($expirationDate, ENT_QUOTES) . "</span></td> <!-- Status applied to Expiration Date -->
                                        <td>" . htmlspecialchars($cert['type'], ENT_QUOTES) . "</td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No certificates found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
