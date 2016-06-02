<?php

/**
 * Extension to {@link MemberProfilePage} that adds extra validation on the domain of the email the user registers with.
 *
 * @copyright Firebrand Holdings Limited 2016
 * @author Maxime Rainville <max@firebrand.nz>
 * @license https://raw.githubusercontent.com/firebrandhq/domain-specific-memberprofiles/master/LICENSE MIT License
 */
class DomainSpecificMemberProfilePageExtension extends Extension
{

    /**
     * Hook into the updateProfileFields function to swap out the email field with our own custom Field.
     * @param  FieldList $fields
     */
    public function updateProfileFields(FieldList $fields)
    {
        // Get a reference to the MemberProfilePage
        $owner = $this->getOwner();

        // Get the Email Profile Field
        $profileField = $owner->Fields()->where(['MemberField' => 'Email'])->First();
        if (
            !$profileField || !$profileField->ID ||
            ($profileField->AllowedDomains && $profileField->DisallowedDomains)) {
            // If we can't find the Email Profile Field or if there's no domain condition on the field,
            // there's no point carrying on
            return;
        }

        // Get the actual Email Form Field
        $oldField = $fields->fieldByName('Email');
        if (!$oldField) {
            // IF we can't find the form field.
            return;
        }

        // The form field might come as a CheckableVisibilityField or a TextField.
        // We need to handle both case differently
        if (is_a($oldField, 'CheckableVisibilityField')) {
            // Create our new DomainSpecificEmailField
            $field = $this->initNewField($oldField->getChild(), $profileField);

            // We need to replicate a bit of logic from MembrProfilePage::getProfileFields()
            $field = new CheckableVisibilityField($field);
            if ($profileField->PublicVisibility == 'Display') {
                $field->makeAlwaysVisible();
            } else {
                $field->getCheckbox()->setValue($profileField->PublicVisibilityDefault);
            }
        } else {
            // Just convert the Email Form Field to our DomainSpecificEmailField
            $field = $this->initNewField($oldField, $profileField);
        }

        // Swap our the old field with the new one.
        $fields->replaceField('Email', $field);
    }

    /**
     * Received a TextField and return a matching DomainSpecificEmailField with domains restrictions.
     * @param  TextField          $oldField
     * @param  MemberProfileField $profileField
     * @return DomainSpecificEmailField
     */
    protected function initNewField(TextField $oldField, MemberProfileField $profileField)
    {
        $field = DomainSpecificEmailField::createFrom($oldField);
        $field
            ->setAllowedDomains($profileField->AllowedDomains)
            ->setDisallowedDomains($profileField->DisallowedDomains)
            ->setShowListOnError($profileField->ShowDomainsOnError);
        return $field;
    }
}
