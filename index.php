<?php 
include('dbcon.php');
include('header.php');
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">All SSL Certs</h3>
                <div class="card-tools">
                    <ul class="pagination pagination-sm float-end">
                        <li class="page-item"> <a class="page-link" href="#">&laquo;</a> </li>
                        <li class="page-item"> <a class="page-link" href="#">1</a> </li>
                        <li class="page-item"> <a class="page-link" href="#">2</a> </li>
                        <li class="page-item"> <a class="page-link" href="#">3</a> </li>
                        <li class="page-item"> <a class="page-link" href="#">&raquo;</a> </li>
                    </ul>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table m-0">
                        <thead>
                            <tr>
                                <th>#Order</th>
                                <th>Cert Domain</th>
                                <th>Cert Type</th>
                                <th>CA</th>
                                <th>Status/Expiration</th>
                                <th>Team / Usage</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("SELECT * FROM certdetails");

                            while ($row = $result->fetch_assoc()) {
                                $domain = $row['cert_domain'];
                                $certType = $row['cert_type'];
                                $certThumbprint = $row['cert_thumbprint'];
                                $orderId = $row['cert_id'];

                                $statusClass = 'text-bg-info';
                                $expirationDate = 'N/A';
                                $certCA = 'N/A';

                                if ($certThumbprint) {
                                    // Query the same certdetails table for certificate details using thumbprint
                                    $stmt = $conn->prepare("SELECT * FROM certdetails WHERE cert_thumbprint = ?");
                                    $stmt->bind_param("s", $certThumbprint);
                                    $stmt->execute();
                                    $certData = $stmt->get_result()->fetch_assoc();

                                    if ($certData) {
                                        $expirationDate = date('Y-m-d H:i:s', strtotime($certData['valid_to']));
                                        $certCA = $certData['issuer'] ?? 'Unknown CA';

                                        $now = new DateTime();
                                        $expiry = new DateTime($expirationDate);
                                        $interval = $now->diff($expiry);

                                        if ($interval->invert) {
                                            $statusClass = 'text-bg-danger'; // Expired
                                        } elseif ($interval->days <= 30) {
                                            $statusClass = 'text-bg-warning'; // Expiring soon
                                        } else {
                                            $statusClass = 'text-bg-success'; // Valid
                                        }
                                    }
                                    $stmt->close();
                                } else {
                                    // Use domain-based method if thumbprint is not available
                                    $domain = preg_replace("~^(?:f|ht)tps?://~i", "", $domain);
                                    $domain = parse_url("https://" . $domain, PHP_URL_HOST);

                                    if ($domain) {
                                        $context = stream_context_create([
                                            "ssl" => [
                                                "capture_peer_cert" => true,
                                                "verify_peer" => false,
                                                "verify_peer_name" => false,
                                            ],
                                        ]);

                                        $client = @stream_socket_client("ssl://{$domain}:443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);

                                        if ($client) {
                                            $params = stream_context_get_params($client);
                                            $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);
                                            $expirationDate = date('Y-m-d H:i:s', $cert['validTo_time_t']);
                                            $certCA = $cert['issuer']['O'] ?? 'Unknown CA';

                                            $now = new DateTime();
                                            $expiry = new DateTime($expirationDate);
                                            $interval = $now->diff($expiry);

                                            if ($interval->invert) {
                                                $statusClass = 'text-bg-danger'; // Expired
                                            } elseif ($interval->days <= 30) {
                                                $statusClass = 'text-bg-warning'; // Expiring soon
                                            } else {
                                                $statusClass = 'text-bg-success'; // Valid
                                            }
                                        }
                                    }
                                }

                                // Fetch teams and external contacts
                                $stmtTeam = $conn->prepare("SELECT t.team_name, t.team_group_email, t.team_contact_person_a, t.team_contact_person_a_email, t.team_contact_person_b, t.team_contact_person_b_email, tc.external_contacts, tc.external_emails 
                                                            FROM team_cert tc
                                                            JOIN teams t ON FIND_IN_SET(t.team_id, tc.team_options)
                                                            WHERE tc.cert_id = ?");
                                $stmtTeam->bind_param("i", $orderId);
                                $stmtTeam->execute();
                                $teams = $stmtTeam->get_result()->fetch_all(MYSQLI_ASSOC);

                                $teamDetails = '';
                                $externalContacts = '';

                                foreach ($teams as $team) {
                                    $teamName = $team['team_name'];
                                    $teamEmail = $team['team_group_email'];
                                    $contactPersonA = $team['team_contact_person_a'] ? "{$team['team_contact_person_a']} ({$team['team_contact_person_a_email']})" : '';
                                    $contactPersonB = $team['team_contact_person_b'] ? "{$team['team_contact_person_b']} ({$team['team_contact_person_b_email']})" : '';
                                    $externalContactsList = $team['external_contacts'] ? $team['external_contacts'] : 'No external contacts';

                                    $teamDetails .= "<p>Team: {$teamName} ({$teamEmail})</p>";
                                    $teamDetails .= $contactPersonA ? "<p>Contact: {$contactPersonA}" . ($contactPersonB ? ", {$contactPersonB}" : "") . "</p>" : '';
                                    $externalContacts .= $team['external_emails'] ? "<p>External Contacts: {$team['external_emails']}</p>" : 'No external contacts';
                                }

                                echo "<tr>
                                    <td><a href=\"#\" class=\"link-primary\">OR{$orderId}</a></td>
                                    <td>{$domain}</td>
                                    <td>{$certType}</td>
                                    <td>{$certCA}</td>
                                    <td><span class=\"badge {$statusClass}\">{$expirationDate}</span></td>
                                    <td>
                                        <button class=\"btn btn-primary btn-sm\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#teamUsage{$orderId}\" aria-expanded=\"false\" aria-controls=\"teamUsage{$orderId}\">View Teams Incharge</button>
                                        <div class=\"collapse\" id=\"teamUsage{$orderId}\">
                                            <div class=\"card card-body\">
                                                {$teamDetails}
                                                {$externalContacts}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                    <button type='button' class='btn btn-sm btn-info'>
                                        <i class='bi bi-download'></i> 
                                    </button>

                                    <button type='button' class='btn btn-sm btn-danger'>
                                        <i class='bi bi-pencil-square'></i> 
                                    </button>
                                    </td>
                                </tr>";
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
