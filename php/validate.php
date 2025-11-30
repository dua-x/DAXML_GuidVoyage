<?php
// validate.php — petite fonction de validation XSD réutilisable

function validateXmlWithXsd(string $xmlPath, string $xsdPath): bool {
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    if (!$doc->load($xmlPath)) {
        return false;
    }
    $isValid = $doc->schemaValidate($xsdPath);
    if (!$isValid) {
        foreach (libxml_get_errors() as $error) {
            error_log("XSD error: " . $error->message);
        }
        libxml_clear_errors();
    }
    return $isValid;
}
