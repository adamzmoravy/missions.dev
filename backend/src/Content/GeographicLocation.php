<?php

namespace Karolina\Content;

class GeographicLocation
{
    /** @var float */
    private $latitude;
    
    /** @var float */
    private $longitude;
    
    /** @var array */
    private $data;
    
    /** @var string */
    private $vicinity;
    
    /** @var string */
    private $source;
    
    /** @var string */
    private $formattedAddress;
    
    /** @var string */
    private $countryName;
    
    /** @var string */
    private $countryCode;

    /**
     * Creates a GeographicLocation value object from arrray or JSON data
     *
     * @param array|string $jsonData Decoded array or JSON string with geographic_location structure
     * @throws InvalidArgumentException If validation fails
     */
    public function __construct($jsonData)
    {

        // If string is provided, decode it to array
        if (is_string($jsonData)) {
            $data = json_decode($jsonData, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Karolina\Exception('Invalid JSON: ' . json_last_error_msg());
            }
            
            $jsonData = $data;
        }
        
        $this->validate($jsonData);

        $this->initializeFromArray($jsonData);
    }

    /**
     * Validates the geographic location data structure
     *
     * @param array $data
     * @throws InvalidArgumentException
     * @return void
     */
    private function validate(array $data)
    {
        $geoData = $data;

        // Validate location
        if (!isset($geoData['location'])) {
            throw new \Karolina\Exception('Missing required key: location');
        }

        if (!isset($geoData['location']['lat'])) {
            throw new \Karolina\Exception('Missing required key: location.lat');
        }

        if (!isset($geoData['location']['lng'])) {
            throw new \Karolina\Exception('Missing required key: location.lng');
        }

        if (!is_numeric($geoData['location']['lat'])) {
            throw new \Karolina\Exception('location.lat must be numeric');
        }

        if (!is_numeric($geoData['location']['lng'])) {
            throw new \Karolina\Exception('location.lng must be numeric');
        }

        // Validate latitude range (-90 to 90)
        $lat = (float)$geoData['location']['lat'];
        if ($lat < -90 || $lat > 90) {
            throw new \Karolina\Exception('location.lat must be between -90 and 90');
        }

        // Validate longitude range (-180 to 180)
        $lng = (float)$geoData['location']['lng'];
        if ($lng < -180 || $lng > 180) {
            throw new \Karolina\Exception('location.lng must be between -180 and 180');
        }

        // Validate vicinity
        if (!isset($geoData['vicinity'])) {
            throw new \Karolina\Exception('Missing required key: vicinity');
        }

        if (!is_string($geoData['vicinity']) || trim($geoData['vicinity']) === '') {
            throw new \Karolina\Exception('vicinity must be a non-empty string');
        }

        // Validate source
        if (!isset($geoData['source'])) {
            throw new \Karolina\Exception('Missing required key: source');
        }

        if (!is_string($geoData['source']) || trim($geoData['source']) === '') {
            throw new \Karolina\Exception('source must be a non-empty string');
        }

        // Validate formatted_address
        if (!isset($geoData['formatted_address'])) {
            throw new \Karolina\Exception('Missing required key: formatted_address');
        }

        if (!is_string($geoData['formatted_address']) || trim($geoData['formatted_address']) === '') {
            throw new \Karolina\Exception('formatted_address must be a non-empty string');
        }

        // Validate country
        if (!isset($geoData['country'])) {
            throw new \Karolina\Exception('Missing required key: country');
        }

        if (!isset($geoData['country']['name'])) {
            throw new \Karolina\Exception('Missing required key: country.name');
        }

        if (!is_string($geoData['country']['name']) || trim($geoData['country']['name']) === '') {
            throw new \Karolina\Exception('country.name must be a non-empty string');
        }

        if (!isset($geoData['country']['code'])) {
            throw new \Karolina\Exception('Missing required key: country.code');
        }

        if (!is_string($geoData['country']['code']) || trim($geoData['country']['code']) === '') {
            throw new \Karolina\Exception('country.code must be a non-empty string');
        }

        // Validate country code format (ISO 3166-1 alpha-2: 2 uppercase letters)
        if (!preg_match('/^[A-Z]{2}$/', $geoData['country']['code'])) {
            throw new \Karolina\Exception('country.code must be a 2-letter ISO country code (e.g., IS, US)');
        }
    }

    /**
     * Initializes object properties from validated array
     *
     * @param array $data
     * @return void
     */
    private function initializeFromArray(array $data)
    {
        $geoData = $data;

        $this->latitude = (float)$geoData['location']['lat'];
        $this->longitude = (float)$geoData['location']['lng'];
        $this->data = isset($geoData['data']) ? $geoData['data'] : [];
        $this->vicinity = trim($geoData['vicinity']);
        $this->source = trim($geoData['source']);
        $this->formattedAddress = trim($geoData['formatted_address']);
        $this->countryName = trim($geoData['country']['name']);
        $this->countryCode = trim($geoData['country']['code']);
    }

    /**
     * Creates instance from JSON string
     *
     * @param string $json
     * @return self
     * @throws InvalidArgumentException
     */
    public static function fromJson($json)
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Karolina\Exception('Invalid JSON: ' . json_last_error_msg());
        }

        return new self($data);
    }

    // Getters

    /**
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @return array
     */
    public function getLocation()
    {
        return [
            'lat' => $this->latitude,
            'lng' => $this->longitude
        ];
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getVicinity()
    {
        return $this->vicinity;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getFormattedAddress()
    {
        return $this->formattedAddress;
    }

    /**
     * @return string
     */
    public function getCountryName()
    {
        return $this->countryName;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @return array
     */
    public function getCountry()
    {
        return [
            'name' => $this->countryName,
            'code' => $this->countryCode
        ];
    }

    /**
     * Converts the value object back to array format
     *
     * @return array
     */
    public function toArray()
    {
        return [
                'location' => [
                    'lat' => $this->latitude,
                    'lng' => $this->longitude
                ],
                'data' => $this->data,
                'vicinity' => $this->vicinity,
                'source' => $this->source,
                'formatted_address' => $this->formattedAddress,
                'country' => [
                    'name' => $this->countryName,
                    'code' => $this->countryCode
                ]
            ];
    }

    /**
     * Converts the value object to JSON string
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * Calculates distance to another geographic location in kilometers
     * Uses the Haversine formula
     *
     * @param GeographicLocation $other
     * @return float Distance in kilometers
     */
    public function distanceTo(GeographicLocation $other)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($other->latitude);
        $lonTo = deg2rad($other->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    /**
     * Checks if this location is the same as another
     *
     * @param GeographicLocation $other
     * @return bool
     */
    public function equals(GeographicLocation $other)
    {
        return $this->latitude === $other->latitude
            && $this->longitude === $other->longitude
            && $this->countryCode === $other->countryCode
            && $this->vicinity === $other->vicinity;
    }
}
