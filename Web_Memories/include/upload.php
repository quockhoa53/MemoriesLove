<?php
function uploadImages($fileInputName, $uploadDir = 'upload/') {
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] == 0) {
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadFile = $uploadDir . basename($_FILES[$fileInputName]['name']);

        if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $uploadFile)) {
            return $uploadFile;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
?>
