/*global dotclear */
'use strict';

// Ready, set, go \o/
dotclear.ready(() => {
  const options = dotclear.getData('typo');

  for (const item of options.items) {
    // Look for each given forms
    const forms = document.querySelectorAll(`form[action^="${item.url}"]`);
    for (const form of forms) {
      if (item.id !== '' && form.id !== item.id) continue; // Looking for a specifif form ID

      // Form found
      for (const selector of item.selectors) {
        // Look for each given selectors (ID, …)
        const elements = form.querySelectorAll(selector);
        for (const input of elements)
          if (input && !input.readOnly && !input.disabled) {
            // Element is not readonly or disabled
            input.addEventListener('blur', () => {
              const buffer = input.value;
              if (buffer !== '')
                dotclear.jsonServicesGet(
                  'typoTransform',
                  (payload) => {
                    if (payload.ret && buffer !== payload.buffer) {
                      input.value = payload.buffer;
                    }
                  },
                  { buffer },
                );
            });
          }
      }
    }
  }
});
