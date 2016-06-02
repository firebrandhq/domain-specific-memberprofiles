<?php

class DomainSpecificEmailField extends EmailField
{

    protected $allowedDomains = [];
    protected $disallowed = [];

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

    public function setAllowedDomains($value)
    {
        if (!is_array($value)) {
            $value = $this->getLines($value);
        }
        $this->allowedDomains = $value;
        return $this;
    }

    public function getAllowedDomains()
    {
        return $this->allowedDomains;
    }

    public function setDisallowedDomains($value)
    {
        if (!is_array($value)) {
            $value = $this->getLines($value);
        }
        $this->disallowedDomains = $value;
        return $this;
    }

    public function getDisallowedDomains()
    {
        return $this->disallowedDomains;
    }

    public function setShowListOnError($value)
    {
        $this->showListOnError = (bool)$value;
        return $this;
    }

    public function getShowListOnError()
    {
        return $this->showListOnError;
    }

    public function validate($validator)
    {
        $valid = parent::validate($validator);

        // If the parent says the value is not valid, let's not bother with validating hte domain.
        if ($valid) {
            $valid = $this->validateDomain($validator);
        }

        return $valid;
    }

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

    protected function getLines($text)
    {
        $lines = explode("\n", $text);
        foreach ($lines as $key => &$line) {
            $line = trim($line);
            if (!$line) {
                unset($lines[$key]);
            }
        }
        return $lines;
    }

    protected function valueInList($domain, $lines)
    {
        foreach ($lines as $line) {
            if (fnmatch($line, $domain, FNM_NOESCAPE | FNM_PERIOD | FNM_CASEFOLD)) {
                return true;
            }
        }
        return false;
    }

}
