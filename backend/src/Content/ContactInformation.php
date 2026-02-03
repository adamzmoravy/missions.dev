<?php

namespace Karolina\Content;

use \Karolina\Exception as InvalidContactInformationException;

class ContactInformation
{
    private const VALID_SERVICES = ['WhatsApp', 'Telegram', 'Signal'];

    /**
     * @var string|null
     */
    private $phoneNumber;

    /**
     * @var array
     */
    private $services;

    /**
     * @var string|null
     */
    private $emailAddress;

    /**
     * @param array $data
     * @throws InvalidContactInformationException
     */
    public function __construct(array $data)
    {
        $this->phoneNumber = $this->normalizeString($data['phone_number'] ?? null);
        $this->emailAddress = $this->normalizeString($data['email_address'] ?? null);
        $this->services = $data['services'] ?? [];

        $this->validate();
    }

    /**
     * @param string|null $value
     * @return string|null
     */
    private function normalizeString($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        return $value;
    }

    /**
     * @throws InvalidContactInformationException
     */
    private function validate()
    {
        if ($this->phoneNumber !== null && !$this->isValidPhoneNumber($this->phoneNumber)) {
            throw new InvalidContactInformationException('Invalid phone number format');
        }

        if ($this->emailAddress !== null && !$this->isValidEmail($this->emailAddress)) {
            throw new InvalidContactInformationException('Invalid email address format');
        }

        if (!is_array($this->services)) {
            throw new InvalidContactInformationException('Services must be an array');
        }

        foreach ($this->services as $service) {
            if (!in_array($service, self::VALID_SERVICES, true)) {
                throw new InvalidContactInformationException(sprintf(
                    'Invalid service "%s". Allowed services: %s',
                    $service,
                    implode(', ', self::VALID_SERVICES)
                ));
            }
        }
    }

    /**
     * @param string $phoneNumber
     * @return bool
     */
    private function isValidPhoneNumber($phoneNumber)
    {
        // Basic validation: starts with + followed by digits and spaces
        return preg_match('/^\+[\d\s]+$/', $phoneNumber) === 1;
    }

    /**
     * @param string $email
     * @return bool
     */
    private function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @return string|null
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @return string|null
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'phone_number' => $this->phoneNumber,
            'services' => $this->services,
            'email_address' => $this->emailAddress,
        ];
    }
}

