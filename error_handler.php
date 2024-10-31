<?php
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // Log error details to a file
    $logFile = 'errors.log';
    $errorMessage = "[" . date('Y-m-d H:i:s') . "] Error: [$errno] $errstr in $errfile on line $errline\n";
    error_log($errorMessage, 3, $logFile);

    // Display a user-friendly error message (customize this as needed)
    echo "<div class='alert alert-danger' role='alert'>Something went wrong. Please try again later.</div>";
}

// Set the custom error handler
set_error_handler("customErrorHandler");

// Catch fatal errors (like SQL errors) as exceptions
function handleShutdown() {
    $error = error_get_last();
    if ($error && ($error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR))) {
        // Log the fatal error
        customErrorHandler($error['type'], $error['message'], $error['file'], $error['line']);
    }
}

// Register the shutdown function
register_shutdown_function('handleShutdown');
?>
