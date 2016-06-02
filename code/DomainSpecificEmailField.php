<?php

/**
 * Extension to {@link EmailField} that adds the ability to limit which domains are allowed for the email address.
 *
 * Wildcards are supported.
 *
 * @copyright Firebrand Holdings Limited 2016
 * @author Maxime Rainville <max@firebrand.nz>
 * @license https://raw.githubusercontent.com/firebrandhq/domain-specific-memberprofiles/master/LICENSE MIT License
 */
class DomainSpecificEmailField extends EmailField
{

    /**
     * List of allowed domains. Wild cards are accepted.
     * @var [string]
     */
    protected $allowedDomains = [];

    /**
     * List of disallowed domains. Wild cards are accepted.
     * @var [string]
     */
    protected $disallowed = [];

    /**
     * Whatever to include the list of allowed or disallowed domains in the validation error message.
     * @var bool
     */
    protected $showListOnError = false;

    /**
     * Create a new DomainSpecificEmailField based on the provided TextField.
     * @param  TextField $field
     * @return DomainSpecificEmailField
     */
    public static function createFrom(TextField $field)
    {
        return new self(
            $field->getName(),
            $field->Title(),
            $field->Value(),
            $field->getMaxLength(),
            $field->getForm()
        );
    }

    /**
     * Set which domains are allowed. If not define, this condition will be ignored.
     * @param [string]|string $value A list of allowed domains. If a string is provided, it will be splitted by line.
     * @return DomainSpecificEmailField
     */
    public function setAllowedDomains($value)
    {
        if (!is_array($value)) {
            $value = $this->getLines($value);
        }
        $this->allowedDomains = $value;
        return $this;
    }

    /**
     * Return the list of Allowed Domains
     * @return [string]
     */
    public function getAllowedDomains()
    {
        return $this->allowedDomains;
    }

    /**
     * Set which domains are disallowed. If not define, this condition will be ignored.
     * @param [string]|string $value A list of allowed domains. If a string is provided, it will be splitted by line.
     * @return DomainSpecificEmailField
     */
    public function setDisallowedDomains($value)
    {
        if (!is_array($value)) {
            $value = $this->getLines($value);
        }
        $this->disallowedDomains = $value;
        return $this;
    }

    /**
     * Return the list of Disallowed Domains
     * @return [string]
     */
    public function getDisallowedDomains()
    {
        return $this->disallowedDomains;
    }

    /**
     * Set whatever to show the domains restriction to the user if the validation fails.
     * @param bool $value
     * @return DomainSpecificEmailField
     */
    public function setShowListOnError($value)
    {
        $this->showListOnError = (bool)$value;
        return $this;
    }

    /**
     * Whatever to show the domains restriction to the user if the validation fails.
     * @return bool $value
     */
    public function getShowListOnError()
    {
        return $this->showListOnError;
    }

    public function validate($validator)
    {
        // Run the parent validation
        $valid = parent::validate($validator);

        // If the parent says the value is not valid, let's do any domain validation.
        if ($valid) {
            $valid = $this->validateDomain($validator);
        }

        return $valid;
    }

    /**
     * Validate the value of this EmailField agianst our list of allowed and disallowed domains
     * @param  Validator $validator
     * @return bool
     */
    protected function validateDomain(Validator $validator)
    {
        // If we can't get a domain from the current value, just quite validating the domain.
        $domain = $this->getDomain();
        if (!$domain) {
            return true;
        }

        // Validate against the allowed domain list
        if ($this->allowedDomains && !$this->valueInList($domain, $this->allowedDomains)) {
            // Build the message
            $message = _t(
                'DomainSpecificEmailField.ALLOWEDDOMAIN',
                'The email address is not from an allowed domain.'
            );

            if ($this->showListOnError) {
                $message .=
                    ' ' .
                    _t('DomainSpecificEmailField.ALLOWEDDOMAINLLIST', 'The following domains are allowed: ') .
                    implode(', ', $this->allowedDomains);
            }

            // Record the error
            $validator->validationError(
                $this->getName(),
                $message
            );
            return false;
        }

        // Validate against the disallowed domain list
        if ($this->disallowedDomains && $this->valueInList($domain, $this->disallowedDomains)) {
            // Build messgae
            $message = _t(
                'DomainSpecificEmailField.DISALLOWEDDOMAIN',
                'The email address is from a disallowed domain.'
            );

            if ($this->showListOnError) {
                $message .=
                    ' ' .
                    _t('DomainSpecificEmailField.DISALLOWEDDOMAINLLIST', 'The following domains are disallowed: ') .
                    implode(', ', $this->disallowedDomains);
            }

            // Record error
            $validator->validationError(
                $this->getName(),
                $message
            );
            return false;
        }

        // Made it all the way without incident. Domain is valid.
        return true;
    }

    /**
     * Extract the domain part of the field's current value.
     * @return bool|string false if a domain can not be retrieve or the domain as a string.
     */
    public function getDomain()
    {
        $value = trim($this->Value());
        if ($value) {
            $domain = substr(strrchr($this->Value(), "@"), 1);
            return $domain;
        } else {
            return false;
        }
    }

    /**
     * Split a string by line and remove the empty lines.
     * @param  string $text
     * @return [string]
     */
    protected function getLines($text)
    {
        // boom
        $lines = explode("\n", $text);

        // Loop over all the lines, trim them and remove the empty ones.
        foreach ($lines as $key => &$line) {
            $line = trim($line);
            if (!$line) {
                unset($lines[$key]);
            }
        }
        return $lines;
    }

    /**
     * Check if the given domain mataches any of the provided lines. Entries in line may contain wildcards.
     * @param  string $domain
     * @param  [string] $lines
     * @return bool
     */
    protected function valueInList($domain, $lines)
    {
        foreach ($lines as $line) {
            // Ignore case, don't escap special characters
            if (fnmatch($line, $domain, FNM_NOESCAPE | FNM_PERIOD)) {
                return true;
            }
        }
        return false;
    }
}
