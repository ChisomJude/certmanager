param (
    [string]$KeystorePath,
    [string]$KeystorePassword
)

$keytoolPath = "C:\Program Files\Microsoft\jdk-11.0.24.8-hotspot\bin\keytool.exe"  # Make sure the path to keytool is correct

try {
    # Command to list certificates in the keystore
    $command = "& '$keytoolPath' -list -keystore '$KeystorePath' -storepass '$KeystorePassword' -rfc"

    # Execute the command and capture the output
    $output = Invoke-Expression $command

    if ($output) {
        # If successful, return the output
        $result = @{
            "Certificates" = $output
        }
        $result | ConvertTo-Json
    } else {
        throw "No certificates found or error in retrieving data."
    }
} catch {
    # Catch any errors and return a JSON error message with more details
    $errorResponse = @{
        Error = 'Error retrieving certificates'
        Details = $_.Exception.Message
        StackTrace = $_.Exception.StackTrace
    }
    $errorResponse | ConvertTo-Json
}
