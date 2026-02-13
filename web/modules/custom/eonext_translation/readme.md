# Module Features

## Translation Mode

This feature allows you to select a translation mode
for your site. There are two options:

1. **Google Translate** (default):
   When this option is selected, the site operates
   with its default configuration, enabling the
   Google Translate widget and functionality.
   No additional changes are applied.

2. **Drupal Translation**:
   When this mode is selected, the site switches to
   Drupal's translation mode. This allows you to
   select the available languages, and the application
   will use the `preprocess_dpl_react_app` function to
   indicate that the site is running in translation
   mode. Two attributes are generated to reflect this
   configuration:

   - **`data-eonext-translation-type`**: Indicates
     the translation type, either `google_translate`
     or `drupal_translate`.
   - **`data-eonext-translation-languages`**: A JSON
     representation of available languages. Example:

     ```json
     {
       "en": {
         "name": "English",
         "path": "/en/node/15/edit"
       },
       "da": {
         "name": "Danish",
         "path": "/da/node/15/edit"
       }
     }
     ```

   When `data-eonext-translation-type` is set to
   `drupal_translate`, the default Google Translate
   widget is hidden. Instead, a new language
   selection widget is displayed, allowing users to
   translate the current page dynamically.

---

## Footer Translation

The footer translation functionality enhances the
existing `dpl_footer` module. Previously, footer data
was stored in the Drupal State, making it
non-translatable. The new implementation introduces
the following improvements:

- **Config Schema**: A new configuration schema
  replaces the state-based storage.
- **Config Form**: A configuration form allows data
  migration from state to config.
- **Submit Handler**: Updates the `dpl_footer`
  configuration form to store footer data as a
  translatable configuration.

This integration with Drupal's core
**Configuration Translation** module enables footer
content to be translated. If the footer is
translated, a new attribute is added to the page:

- **`data-eonext-footer`**: Contains the translated
  footer content. Example:

  ```json
  {
    "translate_footer": 1,
    "footer_items": [
      {
        "name": "Social Media",
        "content": {
          "value": "<ul>...</ul>",
          "format": "basic"
        }
      },
      {
        "name": "Contact",
        "content": {
          "value": "<p>...</p>",
          "format": "basic"
        }
      }
    ],
    "secondary_links": [
      {
        "name": "Facebook",
        "content": "https://www.facebook.com/..."
      }
    ],
    "facebook": "http://facebook.com",
    "instagram": "",
    "youtube": "",
    "spotify": ""
  }
  ```

## Help and Guides

After installing the module, a new menu appears
under:
**`Configuration -> Library Agency ->
Translation Settings`**.

1. **Main Translation Settings**:
   On this page, you can select the
   `Translation Type` (either `Google Translate`
   or `Drupal Translate`).

2. **Footer Translation Settings**:
   Located at:
   **`Configuration -> Library Agency ->
   Translation Settings -> Translate Footer`**.
   Here, you can enable or disable footer
   translation and access a link to the
   **Configuration Translation** page for
   translating footer configuration.
