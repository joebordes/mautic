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

        // Try to find and click the standalone form tile
        try {
            // First try clicking by selector
            $I->click(FormPage::$FORM_TYPE);
        } catch (\Exception $e) {
            // If that fails, try JavaScript approach
            $I->executeJS("Mautic.selectFormType('standalone');");
        }

        $I->see('New Form');
        $I->executeJS(
            "(function(){\n".
            "  var m = document.querySelector('.form-type-modal');\n".
            "  if (m) { m.classList.remove('in'); m.style.display='none'; }\n".
            "  var b = document.querySelector('.form-type-modal-backdrop');\n".
            "  if (b && b.parentNode) { b.parentNode.removeChild(b); }\n".
            "})();"
        );
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
        try {
            $I->click($fieldType);
            $I->waitForElementVisible('#formComponentModal', 5);
        } catch (\Exception $e) {
            $escapedHeader = addslashes($modalHeader);
            $I->executeJS(
                "(function(){\n".
                "  var sel = document.querySelector('select.form-builder-new-component');\n".
                "  if (!sel) return;\n".
                "  var idx = -1;\n".
                "  for (var i=0; i<sel.options.length; i++){\n".
                "    if (sel.options[i].text.trim() === '".$escapedHeader."'){ idx = i; break; }\n".
                "  }\n".
                "  if (idx > 0){\n".
                "    sel.selectedIndex = idx;\n".
                "    var opt = sel.options[idx];\n".
                "    // Trigger the ajax modal explicitly as a robust fallback\n".
                "    if (window.Mautic && typeof Mautic.ajaxifyModal === 'function') { Mautic.ajaxifyModal(opt); }\n".
                "    if (window.mQuery) { mQuery(sel).trigger('change'); } else { var evt = new Event('change', { bubbles: true }); sel.dispatchEvent(evt); }\n".
                "  }\n".
                "})();"
            );
            $I->waitForElementVisible('#formComponentModal', 10);
        }
        $I->waitForElementVisible('input[name="formfield[label]"]', 10);
        $I->fillField('formfield[label]', $label);
        $I->click('#formComponentModal div.modal-footer button.btn-primary');
        $I->wait(2);
    }
}
