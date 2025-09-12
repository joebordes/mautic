<?php

namespace Step\Acceptance;

use Page\Acceptance\FormPage;

class FormStep extends \AcceptanceTester
{
    public function selectAStandAloneType(): void
    {
        $I = $this;
        $I->waitForText('What type of form do you want to create?', 10);
        $I->wait(1); // Give the modal time to fully render

        $I->waitForJS("return typeof window.Mautic === 'object' && typeof window.Mautic.selectFormType === 'function';", 30);
        $I->waitForElementVisible(FormPage::$FORM_TYPE, 10);
        $I->waitForElementClickable(FormPage::$FORM_TYPE, 10);
        $I->click(FormPage::$FORM_TYPE);

        $I->waitForElementNotVisible('.form-type-modal', 10);
        $I->see('New Form');
    }

    public function addFormMetaData(): void
    {
        $I = $this;
        // Fill Basic form info
        $I->fillField('mauticform[name]', FormPage::$FORM_NAME);
        $I->fillField('mauticform[postActionProperty]', FormPage::$FORM_POST_ACTION_PROPERTY);
    }

    public function createFormField(string $fieldType, string $modalHeader, string $label): void
    {
        $I = $this;
        try {
            $I->click('css=select.form-builder-new-component + .chosen-container a.chosen-single');
        } catch (\Exception $e) {
            try {
                $I->click(FormPage::$ADD_NEW_FIELD_BUTTON_TEXT);
            } catch (\Exception $ignored) {
            }
        }
        $I->wait(1);
        $I->click($fieldType);
        $I->waitForElementVisible('#formComponentModal', 10);
        $I->waitForElementVisible('input[name="formfield[label]"]', 10);
        $I->fillField('formfield[label]', $label);
        $I->click('#formComponentModal div.modal-footer button.btn-primary');
        $I->wait(2);
    }
}
