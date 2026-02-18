<?php
/**
 * Support Phone Utility Functions
 * Provides functions to manage and display support phone numbers
 */

/**
 * Get the current support phone number
 * @param mysqli $conn Database connection
 * @return string|null Support phone number or null if not set
 */
function getSupportPhone($conn) {
    $query = "SELECT support_phone FROM social_links WHERE id = 1 LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['support_phone'] ?: null;
    }
    
    return null;
}

/**
 * Format support phone number for display
 * @param string $phone Raw phone number
 * @return string Formatted phone number
 */
function formatSupportPhone($phone) {
    if (empty($phone)) {
        return '';
    }
    
    // Remove all non-digit characters except +
    $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
    
    // Format based on pattern
    if (strpos($cleanPhone, '+250') === 0) {
        // Rwandan international format
        return '+250 ' . substr($cleanPhone, 4, 3) . ' ' . substr($cleanPhone, 7, 3) . ' ' . substr($cleanPhone, 10, 3);
    } elseif (strpos($cleanPhone, '0') === 0 && strlen($cleanPhone) >= 10) {
        // Rwandan local format
        return '0' . substr($cleanPhone, 1, 3) . ' ' . substr($cleanPhone, 4, 3) . ' ' . substr($cleanPhone, 7, 3);
    } else {
        // Generic format
        return $phone;
    }
}

/**
 * Generate support phone HTML display
 * @param mysqli $conn Database connection
 * @param array $options Display options
 * @return string HTML markup for support phone display
 */
function displaySupportPhone($conn, $options = []) {
    $defaults = [
        'show_icon' => true,
        'show_label' => true,
        'show_button' => true,
        'css_class' => 'support-phone-display',
        'icon_size' => 'fa-lg',
        'button_text' => 'Call Support',
        'button_class' => 'btn btn-primary'
    ];
    
    $options = array_merge($defaults, $options);
    $supportPhone = getSupportPhone($conn);
    
    if (empty($supportPhone)) {
        return ''; // Don't display if no support phone is set
    }
    
    $formattedPhone = formatSupportPhone($supportPhone);
    $telLink = 'tel:' . preg_replace('/[^0-9+]/', '', $supportPhone);
    
    $html = '<div class="' . htmlspecialchars($options['css_class']) . '">';
    
    if ($options['show_icon']) {
        $html .= '<i class="fas fa-headset ' . htmlspecialchars($options['icon_size']) . ' me-2 text-primary"></i>';
    }
    
    if ($options['show_label']) {
        $html .= '<strong>Need Help?</strong> ';
    }
    
    $html .= 'Call our support team: ';
    $html .= '<a href="' . htmlspecialchars($telLink) . '" class="text-decoration-none">';
    $html .= '<strong>' . htmlspecialchars($formattedPhone) . '</strong>';
    $html .= '</a>';
    
    if ($options['show_button']) {
        $html .= ' <a href="' . htmlspecialchars($telLink) . '" class="' . htmlspecialchars($options['button_class']) . ' btn-sm ms-2">';
        $html .= '<i class="fas fa-phone-alt me-1"></i>';
        $html .= htmlspecialchars($options['button_text']);
        $html .= '</a>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Check if support phone is configured
 * @param mysqli $conn Database connection
 * @return bool True if support phone is set, false otherwise
 */
function hasSupportPhone($conn) {
    return !empty(getSupportPhone($conn));
}

/**
 * Get support phone for JSON API response
 * @param mysqli $conn Database connection
 * @return array Support phone information
 */
function getSupportPhoneJson($conn) {
    $supportPhone = getSupportPhone($conn);
    
    return [
        'configured' => !empty($supportPhone),
        'phone' => $supportPhone,
        'formatted' => formatSupportPhone($supportPhone),
        'tel_link' => $supportPhone ? 'tel:' . preg_replace('/[^0-9+]/', '', $supportPhone) : null
    ];
}
?>