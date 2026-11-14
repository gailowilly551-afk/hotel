<?php
// ============================================================
// MARVELOUS HOTEL — XML Utility Helper
// includes/xml.php
// ============================================================

function loadXMLFile(string $filename): ?SimpleXMLElement {
    $fullPath = SITE_ROOT . '/assets/xml/' . ltrim($filename, '/');
    if (!file_exists($fullPath)) {
        return null;
    }
    libxml_use_internal_errors(true);
    $xml = simplexml_load_file($fullPath);
    return $xml ?: null;
}

function getHotelConfigXML(): ?SimpleXMLElement {
    static $xml = null;
    if ($xml === null) {
        $xml = loadXMLFile('hotel_config.xml');
    }
    return $xml;
}

function getNavigationXML(string $role): ?SimpleXMLElement {
    return loadXMLFile("navigation_{$role}.xml");
}
