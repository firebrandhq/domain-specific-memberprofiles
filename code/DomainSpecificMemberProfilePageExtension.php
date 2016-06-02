<?php

class DomainSpecificMemberProfilePageExtension extends Extension
{

    public function updateProfileFields(FieldList $fields)
    {
        $owner = $this->getOwner();
        $fieldDO = $owner->Fields()->where(['MemberField' => 'Email'])->First();


        $oldField = $fields->fieldByName('Email');
        if (is_a($oldField, 'CheckableVisibilityField')) {

        } else {
            $newField = DomainSpecificEmailField::createFrom($oldField);
            $newField
                ->setAllowedDomains($fieldDO->AllowedDomains)
                ->setDisallowedDomains($fieldDO->DisallowedDomains)
                ->setShowListOnError($fieldDO->ShowDomainsOnError);

            $fields->replaceField('Email', $newField);
        }
    }

}
