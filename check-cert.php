<?php include('header.php'); ?>

<div class="row"> <!-- Start col -->
    <div class="col-md-12"> <!--begin::Row-->
        
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
            </div> <!-- /.card-header -->
            <div class="card-body p-0">
                
                <form method="post" action="">
                    <label for="url">Enter URL:</label>
                    <input type="text" id="url" name="url" required>
                    <input type="submit" value="Check Certificate">
                </form>

                <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $url = $_POST['url'];

                    // Remove http:// or https:// if present
                    $url = preg_replace("~^(?:f|ht)tps?://~i", "", $url);

                    // Extract domain name
                    $domain = parse_url("https://" . $url, PHP_URL_HOST);

                    if ($domain) {
                        // Create a context to fetch SSL details
                        $context = stream_context_create([
                            "ssl" => [
                                "capture_peer_cert" => true,
                                "verify_peer" => false,
                                "verify_peer_name" => false,
                            ],
                        ]);

                        // Open a connection to the domain on port 443 (HTTPS)
                        $client = @stream_socket_client("ssl://{$domain}:443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);

                        if ($client) {
                            $params = stream_context_get_params($client);
                            $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);
                            
                            // Extract the expiration date
                            $expirationDate = date('Y-m-d H:i:s', $cert['validTo_time_t']);
                            
                            // Extract the Issuer/CA
                            $issuer = isset($cert['issuer']['O']) ? $cert['issuer']['O'] : 'Unknown CA';

                            echo "<p>SSL Certificate for <strong>{$domain}</strong> expires on: <strong>{$expirationDate}</strong></p>";
                            echo "<p>Certificate Authority (CA): <strong>{$issuer}</strong></p>";
                        } else {
                            echo "<p>Could not retrieve SSL certificate information. Please check the URL.</p>";
                        }
                    } else {
                        echo "<p>Invalid URL format. Please enter a valid URL.</p>";
                    }
                }
                ?>

            </div> <!-- /.card-body -->
                <!-- /.card-footer -->
        </div> <!-- /.card -->
    </div> <!-- /.col -->
    
</div> <!--end::Row-->

<?php include('footer.php'); ?>
