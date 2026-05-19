<?php
include '../dbconnect.php';

if (isset($_GET['file'])) {
    // Καθαρίζουμε το όνομα του αρχείου για ασφάλεια (όχι ../ κλπ)
    $filename = basename($_GET['file']);
    $file_path = __DIR__ . "/../../uploads/datasets/" . $filename;

    if (file_exists($file_path)) {
        // Καθαρισμός buffer
        if (ob_get_length()) ob_end_clean();

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        
        readfile($file_path);
        exit;
    } else {
        die("Το αρχείο dataset δεν βρέθηκε: " . $filename);
    }
}
?>