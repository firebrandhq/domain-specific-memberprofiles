<?php

/**
 * Extension to {@link MemberProfileField} to allow allowed domains, disallowed domains to be specified on the on the
 * Email Profile Field.
 *
 * @copyright Firebrand Holdings Limited 2016
 * @author Maxime Rainville <max@firebrand.nz>
 * @license https://raw.githubusercontent.com/firebrandhq/domain-specific-memberprofiles/master/LICENSE MIT License
 */
class DomainSpecificMemberProfileFieldExtension extends DataExtension
{

    /**
     * DB field to add to the Owner
     * @var [string]
     */
    private static $db = array(
        'AllowedDomains' => 'Text',
        'DisallowedDomains' => 'Text',
        'ShowDomainsOnError' => 'Boolean',
    );

    /**
     * Hook updateMemberProfileCMSFields to make sure the new fields only show on the Email proffile field.
     * @param  FieldList $fields
     */
    public function updateMemberProfileCMSFields(FieldList $fields)
    {
        // If this is the Email Profile Field
        if ($this->getOwner()->MemberField == 'Email') {
            // Add a header
            $fields->insertBefore(HeaderField::create(
                'DomainValidationHeader',
                _t('DomainSpecificMemberProfileFieldExtension.DomainValidationHeader', 'Domain Validation'),
                3
            ), 'AllowedDomains');
            // Add some helper text
            $fields->insertBefore(LiteralField::create(
                'DomainValidationHelper',
                _t(
                    'DomainSpecificMemberProfileFieldExtension.DomainValidationHelper',
                    'Allow or disallow profile registration based on the domain of the user\'s email address. One domain can be specified per line. You can use the wildcards (e.g.: <em>*.example.com</em>) to catch subdomains.'
                )
            ), 'AllowedDomains');
        } else {
            // If it's any other Member profile field remove the additional field.
            $fields->removeByName('AllowedDomains');
            $fields->removeByName('DisallowedDomains');
            $fields->removeByName('ShowDomainsOnError');
        }
    }

}
