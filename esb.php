<?php
//Working template for esb cert with keystore in the server

<?php
// Set variables for the keystore path and password
$keystorePath = "C:\\xampp\\htdocs\\certmanager\\keystore\\.keystore";
$keystorePassword = "password";

// Correct the PowerShell command, escaping the quotes inside the string
$command = "powershell.exe -File C:\\xampp\\htdocs\\certmanager\\getKeystoreCerts.ps1 -KeystorePath '$keystorePath' -KeystorePassword '$keystorePassword'";

// Run the command and capture the output
$output = shell_exec($command);

// Log the raw output to inspect
error_log("Raw PowerShell output: " . $output, 3, 'C:\\xampp\\htdocs\\certmanager\\errors.log');

// Display the raw output (for debugging purposes)
echo "<pre>Raw PowerShell output:\n" . htmlspecialchars($output) . "</pre>";

// Check if output is empty or has errors
if (empty($output)) {
    echo "<div class='alert alert-danger'>Error: No certificates found or unable to retrieve the certificates.</div>";
} else {
    // Attempt to decode the output if it's in JSON format
    $certificates = json_decode($output, true);

    if (isset($certificates['Error'])) {
        // If there is an error message from PowerShell, display it
        echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($certificates['Error']) . "<br>Details: " . htmlspecialchars($certificates['Details']) . "</div>";
    } else {
        // Display the certificates in a styled table using Bootstrap 4
        echo '<div class="container mt-5">';
        echo '<h3 class="text-center mb-4">Certificates in Keystore</h3>';
        echo '<table class="table table-striped table-hover table-responsive-sm">';
        echo '<thead class="thead-dark"><tr><th>Alias</th><th>Creation Date</th><th>Type</th><th>Certificate</th></tr></thead>';
        echo '<tbody>';
        
        // Check if 'Certificates' is available in the output
        if (isset($certificates['Certificates']) && is_array($certificates['Certificates'])) {
            foreach ($certificates['Certificates'] as $cert) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($cert) . '</td>';
                echo '<td>--</td>';
                echo '<td>--</td>';
                echo '<td>--</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="4" class="text-center">No certificate data found in the output.</td></tr>';
        }

        echo '</tbody>';\s
        echo '</table>';
        echo '</div>';
    }
}
?>

 
//==========================================================================================================================
// connecting to the box to get keystore
// Include necessary files
include('dbcon.php');
include('error_handler.php');
include('header.php');

// WinRM connection details
$serverIp = "172.25.30.118";
$username = "iswprod";
$password = "Password12";  // Secure this in real setup

// Execute the PowerShell command remotely using WinRM
try {
    // Set up WinRM connection
    $url = "http://$serverIp:5985/wsman";  // Port 5985 for HTTP (or 5986 for HTTPS)
    
    // Create the request
    $winrm = new \WinRM\Client($url, $username, $password);
    $winrm->setAuthMode(\WinRM\AuthMode::NTLM);  // Use NTLM authentication

    // PowerShell command to execute
    $command = "powershell.exe -File C:\\xampp\\htdocs\\certmanager\\getKeystoreCerts.ps1";

    // Execute the command on the remote server
    $response = $winrm->run($command);

    if ($response->isSuccessful()) {
        // Decode the PowerShell output from JSON
        $certificates = json_decode($response->getOutput(), true);

        // Display certificates if available
        if ($certificates) {
            echo '<div class="container mt-5">';
            echo '<h3>Certificates in ESB Keystore</h3>';
            echo '<table class="table table-bordered">';
            echo '<thead><tr><th>Name</th><th>Thumbprint</th><th>Expiration Date</th><th>Last Modified</th></tr></thead>';
            echo '<tbody>';
            foreach ($certificates as $cert) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($cert['Name']) . '</td>';
                echo '<td>' . htmlspecialchars($cert['Thumbprint']) . '</td>';
                echo '<td>' . htmlspecialchars($cert['ExpirationDate']) . '</td>';
                echo '<td>' . htmlspecialchars($cert['LastModified']) . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-danger">No certificates found or error in retrieving data.</div>';
        }
    } else {
        // Log and display an error message
        error_log("WinRM Error: " . $response->getError(), 3, 'C:\xampp\htdocs\certmanager\errors.log');
        echo "<div class='alert alert-danger'>An error occurred while retrieving the certificates. Please contact support.</div>";
    }

} catch (Exception $e) {
    // Log the error
    error_log("PHP Error: " . $e->getMessage(), 3, 'C:\xampp\htdocs\certmanager\errors.log');
    echo "<div class='alert alert-danger'>An error occurred. Please contact support.</div>";
}

include('footer.php');
?>
