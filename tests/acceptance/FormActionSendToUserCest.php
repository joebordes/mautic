<?php

use Page\Acceptance\FormPage;
use Step\Acceptance\FormStep;

class FormActionSendToUserCest
{
    public function _before(AcceptanceTester $I): void
    {
        // Login to Mautic
        $I->login('admin', 'Maut1cR0cks!');
    }

    public function createFormWithSendResultsAndToken(
        AcceptanceTester $I,
        FormStep $form,
    ): void {
        // Go to create new form
        $I->amOnPage(FormPage::$URL);

        // Select standalone form
        $form->selectAStandAloneType();

        // Fill basic form info
        $form->addFormMetaData();

        // Add First Name field
        $I->click('Fields');
        $I->waitForText('Add a new field', 3);

        // Add First Name field
        $form->createFormField(FormPage::$FORM_FIELD_TEXT_SHORT_ANSWER_SELECTOR, 'Text: Short answer', 'First Name');

        // Add Email Address field
        $form->createFormField(FormPage::$FORM_FIELD_EMAIL_SELECTOR, 'Email', 'Email Address');

        // Add "Send form results" action
        $I->click('Actions');

        $I->waitForText('Add a new submit action', 3);
        $I->click('Add a new submit action');
        try {
            $I->click('//li[contains(text(), "Send form results")]');
            $I->waitForElementVisible('#formComponentModal', 10);
        } catch (\Exception $e) {
            $I->executeJS(
                "(function(){\n".
                "  var selects = document.querySelectorAll('select.form-builder-new-component');\n".
                "  var sel = null;\n".
                "  for (var i=0;i<selects.length;i++){ if (selects[i].getAttribute('data-placeholder')==='Add a new submit action'){ sel = selects[i]; break; } }\n".
                "  if (!sel) return;\n".
                "  var idx = -1;\n".
                "  for (var i=0; i<sel.options.length; i++){ if (sel.options[i].text.trim() === 'Send form results'){ idx = i; break; } }\n".
                "  if (idx > 0){\n".
                "    sel.selectedIndex = idx;\n".
                "    var opt = sel.options[idx];\n".
                "    if (window.Mautic && typeof Mautic.ajaxifyModal === 'function') { Mautic.ajaxifyModal(opt); }\n".
                "    if (window.mQuery) { mQuery(sel).trigger('change'); } else { var evt = new Event('change', { bubbles: true }); sel.dispatchEvent(evt); }\n".
                "  }\n".
                "})();"
            );
            $I->waitForElementVisible('#formComponentModal', 10);
        }
        $I->waitForText('Send form results', 2);

        // Assert token insertion
        $message = $I->grabValueFrom('#formaction_properties_message');
        $I->assertEquals(1, substr_count($message, '<strong>First Name</strong>: {formfield=first_name}'));
        $I->assertEquals(1, substr_count($message, '<strong>Email Address</strong>: {formfield=email_address}'));

        // Insert token manually and verify again
        $I->click("//div[@id='formFieldTokens']//a[contains(text(), 'First Name')]");
        $I->wait(1);

        $message = $I->grabValueFrom('#formaction_properties_message');
        $I->assertEquals(2, substr_count($message, '<strong>First Name</strong>: {formfield=first_name}'));
        $I->assertEquals(1, substr_count($message, '<strong>Email Address</strong>: {formfield=email_address}'));

        // Save the action
        $I->executeJS("document.querySelector('button[name=\"formaction[buttons][save]\"]').click();");
    }
}
