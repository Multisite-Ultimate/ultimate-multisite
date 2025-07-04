# Auto Search and Replace in Site Templates (v2)

_**This tutorial requires WP UItimo version 2.x.**_

One of the most powerful features of Multisite Ultimate is the ability to add arbitrary text, color, and select fields to the registration form. Once we have that data captured, we can use it to pre-populate the content in certain parts of the site template selected. Then, when the new site is published, Multisite Ultimate will replace the placeholders with the actual information entered during registration.

For example, you can do your template sites with placeholders. Placeholders should be added surrounded by double curly braces - {{placeholder_name}}.

Then, you can simply add a matching registration field to capture that data

Your customer will then be able to fill that field during the registration.

Multisite Ultimate will then replace the placeholders with the data provided by the customer automatically.

## **Solving the "template full of placeholders" problem**

All of that is great, but we do run into an ugly problem: now our site templates - that can be visited by our customers - are full of ugly placeholders that don't tell much.

To solve that, we offer the option of setting fake values for the placeholders, and we use those values to search and replace their contents on the template sites while your customers are visiting.

You can have access to the template placeholders editor by heading to **Multisite Ultimate > Settings > Sites**, and then, on the sidebar, clicking the Edit Placeholders link.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-aFtnOrska9.png)

That will take you to the placeholders' content editor, where you can add placeholders and their respective content.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-OeMzuyauOW.png)
