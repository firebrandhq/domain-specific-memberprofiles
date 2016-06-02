<?php
use MemberProfileField;

class DomainSpecificMemberProfilePageExtension extends Extension
{

    public function updateProfileFields(FieldList $fields)
    {
        $owner = $this->getOwner();

        $profileField = $owner->Fields()->where(['MemberField' => 'Email'])->First();
        $oldField = $fields->fieldByName('Email');

        // If we are missing the field or $profileField DataObject for Email, do nothing.
        if (!$profileField || !$profileField->ID || !$oldField) {
            return;
        }

        if (is_a($oldField, 'CheckableVisibilityField')) {
            $field = $this->initNewField($oldField->getChild(), $profileField);
            $field = new CheckableVisibilityField($field);

            if ($profileField->PublicVisibility == 'Display') {
                $field->makeAlwaysVisible();
            } else {
                $field->getCheckbox()->setValue($profileField->PublicVisibilityDefault);
            }
        } else {
            $field = $this->initNewField($oldField, $profileField);
        }
        $fields->replaceField('Email', $field);
    }

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
