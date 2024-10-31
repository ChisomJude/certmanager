<?php 
require('dbcon.php');
include('header.php');

function parseCertificate($certFilePath) {
    $certData = [
        'thumbprint' => 'Unknown',
        'valid_to' => 'N/A',
        'issuer' => 'Unknown CA'
    ];

    if (file_exists($certFilePath)) {
        $certContent = file_get_contents($certFilePath);
        $cert = openssl_x509_read($certContent);
        
        if ($cert) {
            $certData['thumbprint'] = openssl_x509_fingerprint($cert);
            $certDetails = openssl_x509_parse($cert);
            if ($certDetails) {
                $certData['valid_to'] = date('Y-m-d H:i:s', $certDetails['validTo_time_t']);
                $certData['issuer'] = $certDetails['issuer']['O'] ?? 'Unknown CA';
            }
        }
    }

    return $certData;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $certDomain = $_POST['certDomain'];
    $certType = $_POST['certType'];
    $teamIds = $_POST['team'];
    $externalContacts = $_POST['externalContacts'];
    $externalEmails = $_POST['externalEmails'];

    $targetDir = "certsuploads/";
    $zipFile = $targetDir . basename($_FILES["certZip"]["name"]);
    $domainCertFile = $targetDir . basename($_FILES["domainCert"]["name"]);
    // Get current date
    $currentDate = date('d-m-Y');

    // Save domain certificate file for parsing
    if (move_uploaded_file($_FILES["domainCert"]["tmp_name"], $domainCertFile)) {
        $certDetails = parseCertificate($domainCertFile);

        // Insert certificate data, file path, and date into the certdetails table
        $stmt = $conn->prepare("INSERT INTO certdetails (cert_domain, cert_type, cert_thumbprint, valid_to, issuer, certpath, date_uploaded) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $certDomain, $certType, $certDetails['thumbprint'], $certDetails['valid_to'], $certDetails['issuer'], $zipFile, $currentDate);

        
        if ($stmt->execute()) {
            $certId = $stmt->insert_id;

            // Insert into team_cert table for each selected team
            foreach ($teamIds as $teamId) {
                $stmtCertTeam = $conn->prepare("INSERT INTO team_cert (team_options, cert_id, external_contacts, external_emails) VALUES (?, ?, ?, ?)");
                $stmtCertTeam->bind_param("iiss", $teamId, $certId, $externalContacts, $externalEmails);
                $stmtCertTeam->execute();
            }

            echo "<div class='text-success'><i class='bi bi-check'></i>Certificate uploaded and processed successfully. </div>";
        } else {
            echo "<div class='text-danger'>Error uploading certificate.</div>";
        }

        $stmt->close();
    } else {
        echo "Failed to upload the domain certificate file.";
    }

    // Save ZIP file for reference
    if (move_uploaded_file($_FILES["certZip"]["tmp_name"], $zipFile)) {
        echo "<div class='text-success'> ZIP file uploaded successfully.</div>";
    } else {
        echo "<div class='text-danger'>Failed to upload the ZIP file.</div>";
    }
}


?>

<div class="container mt-5">
    <h3>Upload SSL Certificate</h3>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="domainCert">Domain Certificate File (.crt or .pem, required for parsing):</label>
            <input type="file" class="form-control-file" id="domainCert" name="domainCert" required>
        </div>

        <div class="form-group">
            <label for="certZip">Upload Certificate ZIP (optional):</label>
            <input type="file" class="form-control-file" id="certZip" name="certZip">
        </div>

        <div class="form-group">
            <label for="certDomain">Certificate Domain:</label>
            <input type="text" class="form-control" id="certDomain" name="certDomain" placeholder="Enter domain" required>
        </div>

        <div class="form-group">
            <label for="certType">Certificate Type:</label>
            <select class="form-control" id="certType" name="certType" required>
                <option value="Wildcard">Wildcard</option>
                <option value="Single-Domain-Cert">Single Domain Cert</option>
            </select>
        </div>

        <div class="form-group">
            <label for="team">Select Teams:</label>
            <select class="form-control" id="team" name="team[]" multiple required>
                <?php
                $result = $conn->query("SELECT team_id, team_name FROM teams");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['team_id']}'>{$row['team_name']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="externalContacts">External Contact Persons (separate multiple names with a comma):</label>
            <input type="text" class="form-control" id="externalContacts" name="externalContacts" placeholder="Enter contact persons">
        </div>

        <div class="form-group">
            <label for="externalEmails">External Contact Emails (separate multiple emails with a comma):</label>
            <input type="text" class="form-control" id="externalEmails" name="externalEmails" placeholder="Enter contact emails">
        </div>

        <button type="submit" class="btn btn-primary">Upload Certificate</button>
    </form>
</div>

<?php 
$conn->close();
include('footer.php'); ?>
