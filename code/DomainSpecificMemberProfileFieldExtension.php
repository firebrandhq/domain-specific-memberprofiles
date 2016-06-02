<?php

class DomainSpecificMemberProfileFieldExtension extends DataExtension
{

    private static $db = [
        'AllowedDomains' => 'Text',
        'DisallowedDomains' => 'Text',
        'ShowDomainsOnError' => 'Boolean',
    ];

    public function updateMemberProfileCMSFields(FieldList $fields)
    {
        if ($this->getOwner()->MemberField == 'Email') {
            // Add a header
            $fields->insertBefore('AllowedDomains', HeaderField::create(
                'DomainValidationHeader',
                _t('DomainSpecificMemberProfileFieldExtension.DomainValidationHeader', 'Domain Validation'),
                3
            ));
            // Add some helper text
            $fields->insertBefore('AllowedDomains', LiteralField::create(
                'DomainValidationHelper',
                _t(
                    'DomainSpecificMemberProfileFieldExtension.DomainValidationHelper',
                    'Allow or disallow profile registration based on the domain of the user\'s email address. One domain can be specified per line. You can use the wildcards (e.g.: <em>*.example.com</em>) to catch subdomains.'
                )
            ));

        } else {
            $fields->removeByName('AllowedDomains');
            $fields->removeByName('DisallowedDomains');
            $fields->removeByName('ShowDomainsOnError');
        }
    }

}
