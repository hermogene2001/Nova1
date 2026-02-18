<?php
/**
 * Phone Number Utility Class
 * Provides phone number validation, formatting, and manipulation functions
 */
class PhoneNumberUtil {
    
    /**
     * Validate phone number format
     * @param string $phone Phone number to validate
     * @param int $minLength Minimum length (default: 10)
     * @param int $maxLength Maximum length (default: 15)
     * @return bool True if valid, false otherwise
     */
    public static function isValid($phone, $minLength = 10, $maxLength = 15) {
        // Remove spaces, dashes, parentheses, and other common formatting
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if it contains only digits and is within length limits
        return preg_match('/^[0-9]{' . $minLength . ',' . $maxLength . '}$/', $cleanPhone);
    }
    
    /**
     * Clean and normalize phone number
     * @param string $phone Phone number to clean
     * @return string Cleaned phone number or empty string if invalid
     */
    public static function clean($phone) {
        // Remove all non-digit characters
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Return only if valid length
        if (strlen($cleanPhone) >= 10 && strlen($cleanPhone) <= 15) {
            return $cleanPhone;
        }
        
        return '';
    }
    
    /**
     * Format phone number for display
     * @param string $phone Phone number
     * @param string $format Format pattern (default: '(XXX) XXX-XXXX')
     * @return string Formatted phone number
     */
    public static function format($phone, $format = '(XXX) XXX-XXXX') {
        $cleanPhone = self::clean($phone);
        
        if (empty($cleanPhone)) {
            return $phone; // Return original if invalid
        }
        
        // For international numbers, show country code
        if (strlen($cleanPhone) > 10) {
            $countryCode = substr($cleanPhone, 0, strlen($cleanPhone) - 10);
            $localNumber = substr($cleanPhone, -10);
            return '+' . $countryCode . ' (' . substr($localNumber, 0, 3) . ') ' . 
                   substr($localNumber, 3, 3) . '-' . substr($localNumber, 6);
        }
        
        // Standard US/Canada format
        return '(' . substr($cleanPhone, 0, 3) . ') ' . 
               substr($cleanPhone, 3, 3) . '-' . substr($cleanPhone, 6);
    }
    
    /**
     * Check if phone number is from a specific country
     * @param string $phone Phone number
     * @param string $countryCode Country code (e.g., '1' for US/Canada)
     * @return bool True if matches country code
     */
    public static function isCountry($phone, $countryCode) {
        $cleanPhone = self::clean($phone);
        return strpos($cleanPhone, $countryCode) === 0;
    }
    
    /**
     * Generate a random phone number for testing
     * @param int $length Length of phone number (default: 10)
     * @return string Random phone number
     */
    public static function generateRandom($length = 10) {
        $phone = '';
        for ($i = 0; $i < $length; $i++) {
            $phone .= rand(0, 9);
        }
        return $phone;
    }
    
    /**
     * Get phone number type/info
     * @param string $phone Phone number
     * @return array Phone information
     */
    public static function getInfo($phone) {
        $cleanPhone = self::clean($phone);
        $isValid = !empty($cleanPhone);
        
        return [
            'original' => $phone,
            'cleaned' => $cleanPhone,
            'formatted' => $isValid ? self::format($phone) : $phone,
            'length' => strlen($cleanPhone),
            'valid' => $isValid,
            'international' => $isValid && strlen($cleanPhone) > 10,
            'country_code' => $isValid && strlen($cleanPhone) > 10 ? substr($cleanPhone, 0, strlen($cleanPhone) - 10) : null
        ];
    }
    
    /**
     * Validate multiple phone numbers
     * @param array $phones Array of phone numbers
     * @return array Validation results
     */
    public static function validateBatch($phones) {
        $results = [];
        foreach ($phones as $index => $phone) {
            $results[$index] = self::getInfo($phone);
        }
        return $results;
    }
}
?>