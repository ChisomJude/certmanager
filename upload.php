<?php 
include('dbcon.php');
include('header.php');

// Check if user is logged in and is an admin
// if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'admin') {
//     header('Location: login.php'); // Redirect to login if not logged in or not an admin
//     exit();
// }
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="container mt-5">
                    <h3>Upload SSL Certificate</h3>

                    <?php
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        $certDomain = $_POST['certDomain'];
                        $certType = $_POST['certType'];
                        $teamIds = $_POST['team'];
                        $externalContacts = $_POST['externalContacts'];
                        $externalEmails = $_POST['externalEmails'];

                        $targetDir = "certsuploads/";
                        $targetFile = $targetDir . basename($_FILES["certZip"]["name"]);

                        // Check if the file is a ZIP and move it to the uploads folder
                        if (move_uploaded_file($_FILES["certZip"]["tmp_name"], $targetFile)) {
                            // Extract ZIP file
                            $zip = new ZipArchive;
                            if ($zip->open($targetFile) === TRUE) {
                                $zip->extractTo($targetDir);
                                $zip->close();

                                // Initialize variables
                                $certFile = null;
                                $intermediateCertFile = null;
                                $keyFile = null;

                                // Identify files based on domain name
                                $files = scandir($targetDir);
                                foreach ($files as $file) {
                                    $filePath = $targetDir . $file;
                                    if (is_file($filePath)) {
                                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                        $fileBaseName = strtolower(pathinfo($file, PATHINFO_FILENAME));
                                        
                                        if ($ext == 'crt' || $ext == 'pem') {
                                            if (strpos($fileBaseName, strtolower($certDomain)) !== false) {
                                                // If file matches domain, set it as the domain cert
                                                $certFile = $filePath;
                                            } elseif (!$intermediateCertFile) {
                                                // If no domain cert found yet, set as intermediate cert
                                                $intermediateCertFile = $filePath;
                                            }
                                        } elseif ($ext == 'key') {
                                            $keyFile = $filePath;
                                        }
                                    }
                                }

                                if ($certFile) {
                                    // Parse the domain certificate
                                    $certDetails = parseCertificate($certFile);

                                    // Insert certificate data into the certdetails table
                                    $stmt = $conn->prepare("INSERT INTO certdetails (cert_domain, cert_type, cert_thumbprint, valid_to, issuer) VALUES (?, ?, ?, ?, ?)");
                                    $stmt->bind_param("sssss", $certDomain, $certType, $certDetails['thumbprint'], $certDetails['valid_to'], $certDetails['issuer']);

                                    if ($stmt->execute()) {
                                        $certId = $stmt->insert_id;

                                        // Insert into team_cert table for each selected team
                                        foreach ($teamIds as $teamId) {
                                            $stmtCertTeam = $conn->prepare("INSERT INTO team_cert (team_options, cert_id, external_contacts, external_emails) VALUES (?, ?, ?, ?)");
                                            $stmtCertTeam->bind_param("iiss", $teamId, $certId, $externalContacts, $externalEmails);
                                            $stmtCertTeam->execute();
                                        }

                                        echo "Certificate uploaded and processed successfully.";
                                    } else {
                                        echo "Error uploading certificate.";
                                    }

                                    $stmt->close();
                                } else {
                                    echo "No valid domain certificate file matching the domain found in ZIP.";
                                }

                                $conn->close();
                            } else {
                                echo "Failed to extract ZIP file.";
                            }
                        } else {
                            echo "Sorry, there was an error uploading your file.";
                        }
                    }

                    function parseCertificate($certFilePath) {
                        $certData = [
                            'thumbprint' => 'Unknown',
                            'valid_to' => 'N/A',
                            'issuer' => 'Unknown CA'
                        ];

                        if (file_exists($certFilePath)) {
                            $certContent = file_get_contents($certFilePath);

                            // Parse the certificate
                            $cert = openssl_x509_read($certContent);
                            
                            if ($cert) {
                                // Extract thumbprint
                                $certData['thumbprint'] = openssl_x509_fingerprint($cert);

                                // Extract validity period
                                $certDetails = openssl_x509_parse($cert);
                                if ($certDetails) {
                                    $certData['valid_to'] = date('Y-m-d H:i:s', $certDetails['validTo_time_t']);
                                    $certData['issuer'] = $certDetails['issuer']['O'] ?? 'Unknown CA';
                                }
                            }
                        }

                        return $certData;
                    }
                    ?>

                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="certZip">Upload Certificate (ZIP file):</label>
                            <input type="file" class="form-control-file" id="certZip" name="certZip" required>
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
                                // Fetch teams from the database
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
            </div>
        </div>   
    </div>
</div>

<?php include('footer.php'); ?>
