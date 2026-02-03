<?php

namespace Karolina\Content;

/**
 * LinkMeta - Fetches and parses metadata from web pages
 * 
 * This class retrieves HTML from a given URL and extracts:
 * - Standard meta tags (description, keywords, author, etc.)
 * - Open Graph meta tags (og:*)
 * - Twitter Card meta tags (twitter:*)
 * - Page title
 * - Canonical URL
 * - Favicon
 */
class LinkMeta
{
    /** @var string */
    private $url;

    /** @var string */
    private $html;

    /** @var array */
    private $metadata = [];

    /** @var int */
    private $timeout = 10;

    /** @var string */
    private $userAgent = 'Mozilla/5.0 (compatible; LinkMetaBot/1.0)';

    /**
     * Constructor
     * 
     * @param string $url The URL to fetch metadata from
     * @param int $timeout Connection timeout in seconds (default: 10)
     */
    public function __construct(string $url, int $timeout = 10)
    {
        $this->url = $url;
        $this->timeout = $timeout;
    }

    /**
     * Set custom user agent
     * 
     * @param string $userAgent
     * @return self
     */
    public function setUserAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * Fetch the HTML content from the URL
     * 
     * @return self
     * @throws \Exception If fetching fails
     */
    public function fetch(): self
    {
        $ch = curl_init($this->url);
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $this->html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);

        if ($this->html === false || $httpCode !== 200) {
            throw new \Karolina\Exception("Failed to fetch URL: {$this->url}. HTTP Code: {$httpCode}. Error: {$error}");
        }

        return $this;
    }

    /**
     * Parse the HTML and extract all metadata
     * 
     * @return self
     */
    public function parse(): self
    {
        if (empty($this->html)) {
            throw new \Karolina\Exception("No HTML content to parse. Call fetch() first.");
        }

        $dom = new \DOMDocument();
        @$dom->loadHTML($this->html);

        // Extract title
        $this->extractTitle($dom);

        // Extract meta tags
        $this->extractMetaTags($dom);

        // Extract canonical URL
        $this->extractCanonical($dom);

        // Extract favicon
        $this->extractFavicon($dom);

        return $this;
    }

    /**
     * Extract page title
     * 
     * @param \DOMDocument $dom
     */
    private function extractTitle(\DOMDocument $dom): void
    {
        $titleNodes = $dom->getElementsByTagName('title');
        if ($titleNodes->length > 0) {
            $this->metadata['title'] = trim($titleNodes->item(0)->textContent);
        }
    }

    /**
     * Extract all meta tags
     * 
     * @param \DOMDocument $dom
     */
    private function extractMetaTags(\DOMDocument $dom): void
    {
        $metaTags = $dom->getElementsByTagName('meta');

        foreach ($metaTags as $meta) {
            $name = $meta->getAttribute('name');
            $property = $meta->getAttribute('property');
            $content = $meta->getAttribute('content');

            // Standard meta tags (name attribute)
            if (!empty($name) && !empty($content)) {
                $this->metadata['meta'][$name] = $content;

                // Also store common ones at top level for convenience
                if (in_array($name, ['description', 'keywords', 'author'])) {
                    $this->metadata[$name] = $content;
                }
            }

            // Open Graph and other property-based meta tags
            if (!empty($property) && !empty($content)) {
                $this->metadata['meta'][$property] = $content;

                // Store Open Graph tags at top level
                if (strpos($property, 'og:') === 0) {
                    $key = substr($property, 3); // Remove 'og:' prefix
                    $this->metadata['og'][$key] = $content;
                }

                // Store Twitter Card tags at top level
                if (strpos($property, 'twitter:') === 0) {
                    $key = substr($property, 8); // Remove 'twitter:' prefix
                    $this->metadata['twitter'][$key] = $content;
                }
            }

            // Handle charset
            $charset = $meta->getAttribute('charset');
            if (!empty($charset)) {
                $this->metadata['charset'] = $charset;
            }

            // Handle http-equiv
            $httpEquiv = $meta->getAttribute('http-equiv');
            if (!empty($httpEquiv) && !empty($content)) {
                $this->metadata['http-equiv'][$httpEquiv] = $content;
            }
        }
    }

    /**
     * Extract canonical URL
     * 
     * @param \DOMDocument $dom
     */
    private function extractCanonical(\DOMDocument $dom): void
    {
        $links = $dom->getElementsByTagName('link');
        
        foreach ($links as $link) {
            $rel = $link->getAttribute('rel');
            if ($rel === 'canonical') {
                $href = $link->getAttribute('href');
                if (!empty($href)) {
                    $this->metadata['canonical'] = $href;
                    break;
                }
            }
        }
    }

    /**
     * Extract favicon URL
     * 
     * @param \DOMDocument $dom
     */
    private function extractFavicon(\DOMDocument $dom): void
    {
        $links = $dom->getElementsByTagName('link');
        
        foreach ($links as $link) {
            $rel = $link->getAttribute('rel');
            
            // Check for various favicon rel types
            if (in_array($rel, ['icon', 'shortcut icon', 'apple-touch-icon'])) {
                $href = $link->getAttribute('href');
                if (!empty($href)) {
                    // Convert relative URLs to absolute
                    $this->metadata['favicon'] = $this->makeAbsoluteUrl($href);
                    break;
                }
            }
        }

        // Fallback to default favicon location
        if (!isset($this->metadata['favicon'])) {
            $parsed = parse_url($this->url);
            $this->metadata['favicon'] = $parsed['scheme'] . '://' . $parsed['host'] . '/favicon.ico';
        }
    }

    /**
     * Convert relative URL to absolute URL
     * 
     * @param string $url
     * @return string
     */
    private function makeAbsoluteUrl(string $url): string
    {
        // Already absolute
        if (parse_url($url, PHP_URL_SCHEME) !== null) {
            return $url;
        }

        $parsed = parse_url($this->url);
        $scheme = $parsed['scheme'] ?? 'http';
        $host = $parsed['host'] ?? '';

        // Protocol-relative URL
        if (strpos($url, '//') === 0) {
            return $scheme . ':' . $url;
        }

        // Absolute path
        if (strpos($url, '/') === 0) {
            return $scheme . '://' . $host . $url;
        }

        // Relative path
        $base = $scheme . '://' . $host;
        $path = $parsed['path'] ?? '/';
        $path = substr($path, 0, strrpos($path, '/') + 1);
        
        return $base . $path . $url;
    }

    /**
     * Get all extracted metadata
     * 
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Get specific metadata value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Get the fetched HTML
     * 
     * @return string
     */
    public function getHtml(): string
    {
        return $this->html;
    }

    /**
     * Get the URL
     * 
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Convenience method to fetch and parse in one call
     * 
     * @return array
     * @throws \Exception
     */
    public function fetchAndParse(): array
    {
        return $this->fetch()->parse()->getMetadata();
    }

    /**
     * Get a summary of the most important metadata
     * 
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'url' => $this->url,
            'title' => $this->get('title') ?? $this->get('og')['title'] ?? null,
            'description' => $this->get('description') ?? $this->get('og')['description'] ?? null,
            'image' => $this->get('og')['image'] ?? $this->get('twitter')['image'] ?? null,
            'favicon' => $this->get('favicon'),
            'canonical' => $this->get('canonical'),
            'type' => $this->get('og')['type'] ?? null,
            'site_name' => $this->get('og')['site_name'] ?? null,
        ];
    }

    /**
     * Export metadata as JSON
     * 
     * @param int $options JSON encode options
     * @return string
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->metadata, $options);
    }
}
