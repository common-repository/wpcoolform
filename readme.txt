=== WpCoolForm ===
Contributors: (this should be a list of wordpress.org userid's)
Donate link: http://wpcoolform.com/
Tags: Form Builder captcha word file generator reCaptcha web formular file upload
Requires at least: 4.0.0
Tested up to: 4.4.2
Stable tag: 4.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create HTML-Formulars in minutes, Use reCaptcha or our inbuild captchas with no 
additional dependencies. Define confirmation emails and export your form data in Excel format.

== Description ==

WpCoolForm is a fast and easy to use form builder for Wordpress with no pro version limitations.
Our pro version has some innovative additional features, but the normal version has no restrictions.
You have many options to define input fields, your form can have one, two or three columns of
input fields. Every form has a different data table, and all data can be exported as an Excel File.

Check our videos: http://wpcoolform.com

The key features for short:

*   reCaptcha prepared.
*   an out of the box inbuild captcha solution with no dependencies. 
*   a fast and easy drag and drop form builder.
*   Excel Export.
*   Confirmation Email solution with replaceable form field.
*   Wordpress Page generator with your defined form.
*   or use a code which will be replaced by your form.
*   Additional CSS input possible.
*   definable redirect url
*   Form inputs can be appended to the redirect url for multilevel forms.
*   FAQs
*   a lot of youtube videos.


== Installation ==

This section describes how to install the plugin and get it working.


1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Open WpCoolForm->New Form to create a new HTML-Form.
4. Give your new form a unique name.
5. Under WpCoolForm->Settings you then find a new entry with your form name.
6. Define your form unter WpCoolForm->Settings->Your Form Name
7. If you are done, create a new Wordpress Form Page or insert the code given unter "Generate Form" on any Wordpress page.
8. All user input data you find unter WpCoolForm->Form Data->Your form name. 


== Frequently Asked Questions ==


= I have changed my form a little bit, but the resulting form hasn't change. Why? =

If you use the code of your form, your form is always up to date. But if you've 
created a wordpress page one time and change something, you have to create a new
wordpress page. Also keep in mind to update all links to that page.

= How can I change the design or content of a form? =

* First of all use the inbuild form builder, where you can change the input types, labels 
headlines and column count.
* Under Design Options you can insert your own CSS to change the look of your form.
* Of course you can also add a global CSS file to your Website.
* Under Settings you can generate your defined form. This leeds to a "normal" Wordpress page, which you
can find under pages -> your form name. This page you can change as you please, but please don't 
break the form and input fields.


= What about Captchas? =

You have two options:
* Use our inbuild captcha by selecting the checkbox under Settings.
* use reCaptcha. Therefore you need to register at Google. The secret keys you can cut/paste under Settings.

Our Captcha has two modi. If you have installed a graphical library, every captcha-image is generated
on the fly. If you don't, we use an existing image which displays a number between 1 and 100.


= What about confirmation emails? =

Under Email-Settings you can design your confirmation email and insert a fixed reveiver (e.g. webmaster).
You can use your form field names as placeholders in your email content like: Dear [wcf.Lastname] - for a
field "Lastname".
If you like to send a confirmation to your customer, you need an input field of type "Email", also you need 
to check the checkbox confirmation email. Then your customer also gets a confirmation email.

= Is it possible to change the redirection URL? =

You find the input field for your redirection URL under Settings->Form Data.
There are also two checkboxes to append all input fields to the redirection url. So you can create
multilevel input forms.


= The input field mapping of the data table isn't correct, what can I do? =

WpCoolForm is very flexible, you can allways change the Form Name, also you can change field names, types,
you can add and delete fields everytime. Normally this works fine. We suggest not to delete fields if user inputs
already exists. You may loose these inputs as well. On the other hand adding new fields is allways possible.



== Screenshots ==

1. wpcf_data1.png shows an empty form data table.
2. wpcf_data2.png shows more columns than screenshot 1.
3. wpcf_data3.png shows an entry with edit download, and delete button.
4. wpcf_generated_form.png shows a generated form. You can change the design with your own CSS.
5. wpcf_settings1.png shows the top of the settings page.
6. wpcf_settings2.png shows some setting options.
6. wpcf_settings3.png shows some more setting options.
7. wpcf_settings4.png shows the form builder on the settings page.


== Changelog ==
= 1.0.0 =
- some cleanups
- icons
- update readme.txt

= 0.9 =
published on wordpress.org

