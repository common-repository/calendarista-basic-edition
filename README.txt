=== Calendarista - Appointment booking system ===

Contributors:      typps
Donate link: 	   https://www.calendarista.com
Requires at least: 6.3
Tested up to:      6.4
Requires PHP:      7.0
Stable tag: 	   3.0.8
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Tags: 			   booking, appointment, reservation, booking calendar, events

CALENDARISTA - Appointment booking system: Calendarista is a hassle-free way to book services online. We want to make it super-fast and easy for your business to add their services and prices and begin taking bookings online.

== Screenshot ==
1. The single day mode. Users can book a single day. The same day can be booked multiple times or just once.
2. The details tab will allow your user to enter a full name and email.
3. The checkout step prepares the customer to complete the booking. If payment is enabled, they will be redirected to paypal when clicking book now.
4. The single day and time mode. This mode will allow the customer to select a date and time of booking. The same timeslot can be booked multiple times or just once.

== Description ==
#### Some of the features in this edition

1. Bookings with a single start date.
2. Bookings with both a start date and start time.
3. Bookings with both a start date and start time with padding.
4. Bookings with a start date and time range.
5. Bookings a date range.
6. Booking a date and time range.
7. Booking a date range with partial day charge (Half day booking).
8. Bookings a round trip.
9. Bookings a round trip (with time).
10. Booking multiple dates (non sequential).
12. Booking multiple dates with time (non sequential).
13. Booking one or more packages.
14. Payments through Paypal.
15. Collect payment offline.
16. Enable online payments.
17. Enable online payments and offline mode.
18. Enable or disable payments (perfect for free bookings).
19. List of custom date formats to choose from.
20. AM/PM or 24h time format.
21. Customize any piece of string displayed to customer directly from the plugins back-end.
22. Translation ready.
23. Customize the data capture fields in a way that suits your business via Custom form fields.
24. Unlimited Categorized extras to add elements that carry extra cost and can be in limited quantity.
25. Define departure and destination dropdown fields with predefined locations and cost.
26. Calculate price by travel distance.
27. Charge only an upfront deposit(percentage or fixed fee). Pay reminder upon arrival.
28. Restrict from booking too soon or too late in the future with min/max notice.
29. Turn over days to set prep time needed before and after the next booking.
30. List services in a dropdown list.
31. List availables in a dropdown list.
32. An availability can contain an area displayed on a map in the front-end.
33. Style by choosing from a predefined color list to allow a more natural integration with your website.
34. Customize all email notifications sent out by the plugin.
35. Booking cancel link sent via email.
36. Set up Email reminders.
37. View sales, request payment or confirm payment.
38. View appointments in your back-end calendar.
39. Manage all appointments in the back-end.
40. Departure and destination input fields with google maps autocomplete support.
41. Booking with both departure and destination input fields.
42. Booking with single departure input field only.
43. Booking with both departure and destination dropdown fields.
44. Booking with single departure dropdown field.
45. Set waypoints between departure and destination.
46. Enable direction on a live google map within your site (no redirect).
47. Enable options such as avoid highway, avoid tolls and show traffic.
48. Select departure and destination directly on google map using right click context menu.
49. Add Search form with search attributes via short-code – allow customers to find matching appointments.
50. Control availability and available space by setting seats quantity per booking.
51. GDPR ready – EU law compliant on data protection and privacy.

#### I need more features, do you have a premium version?
Honestly, we are glad you asked because we do have a premium version with many more features. Please checkout [Calendarista Premium Edition](https://www.calendarista.com)

### Full list of features found on [Calendarista Premium Edition](https://www.calendarista.com)

# BECOMING A CALENDARISTA
##### Getting started

Everything takes place in the “Services” page. This is your workshop for making your date and time available and ready for consumption through the web. But let’s do this in steps. We will be ignoring self-explanatory settings and focus on the basics that we need to get you up and running fast. 
>NOTE: A [premium verison](https://www.calendarista.com) of the plugin is also available.

##### What is an availability?
In Calendarista, an availability is the date and time you are available to provide a certain service. An availability can be a single date or time you are available or one that repeats daily, weekly, monthly, yearly and so forth.

However, in order to setup your availability, first you will need to define a service.

#### SERVICES
Services can be created from the “Services” page. The page is subdivided in tabs to allow the various settings that affect a service. 

##### Service tab
Provide a name that describes your service. Please try to keep this short and descriptive as it may come handy when you have defined several services. This will enable you to quickly distinguish one from the other at a glance. Additionally, it is possible to list services in a dropdown list in the front-end. In that case, this same name will be used so remember to keep it short and descriptive.

Select a mode for your service. This will dictate the way Calendarista behaves in the front-end. Remember you are simply selecting the behavior. You will be able to set the actual date(s) and how these repeat later when setting the availability. 

* Single day
In the front-end, the booking calendar will allow selecting only a day. In this edition, only a single day selection is eneabled. This mode does not support time slots. 
* Single day and time
In the front-end, the booking calendar will allow selecting a date and a single timeslots. 

##### Availability Tab
This is where you can setup the calendar, including the days and time slots you are available and the cost if applicable among other options. Multiple availabilities can be created. In this case, each availability will be listed in a list in the front-end. We will skip self-explanatory and optional settings so that we can focus on the main settings you will need.

>Note that most of the availability settings depend on the service mode selected.

1. Provide a name for the availability. Make sure the name is short and descriptive. This will help you identify an availability quickly. The same name will also be used in the front-end in case you have created multiple availabilities. 
2. Set the date and time your availability starts.
3. Seats can be set depending on the service mode. Not all modes support seats. Additionally, if the service supports timeslots, then you will be able to set seats per timeslot and the setting will be available separately when creating timeslots.
4. An image URL can be provided optionally. If you do provide one, it will display in the front-end when the availability is selected. This can be useful in cases where you want to display the picture of the person that owns the availability. To set the images width and height refer the “Styles” section.
5. Check “repeat”, this will allow you to add days to your availability. If you do not check repeat, you will end up with a single day in your availability, which is the date you setup in step 2.
Checking “Repeat” will produce a dialog box where you can setup the repetition pattern. 
>Note that we will not go through the options in the “Repeat” dialog as these are all quite >simple to guess what they do.

This covers the necessary settings required to create an availability. Now click the “Create New” button. Congratulations. You have just created your first service. 

In case the service mode is one that includes timeslots, then you are not quite done yet. Head on to the “Timeslots” tab. We will discuss this in the next section. 

##### Timeslots tab
If the service mode is one that supports time, then the time slots tab will display. This is where you generate time slots, set cost per time slot. But first ensure you have already created an availability otherwise you won’t be allowed to setup time slots.
>NOTE: In this edition, you can only autogenerate slots by weekday but this is quite sufficient for most usecases.

From the timeslots tab, select an availability. The timeslots you will be creating will be for the selected availability only. 

For instance, if you are running a dental clinic and have several dentists, then you will likely setup one availability per dentist. The timeslots generated will be the working hours of that particular dentist. A dental clinic is only an example, and you are not confined to any specific type of business as the use case for Calendarista aims to be for the general use case, thus covering several scenarios.

Next choose “Create timeslot”. If you select this option, you will be able to create time slots manually per specific day by date or week day. 

1. Weekday
The default selection of "All week days" will generate slots using the "Start interval", "Time length" and "End time" values to generate slots that will be included in all weekdays. If slots exist in any particular weekday, these will be wiped out and replaced by fresh values generated.
2.	Cost
If your service supports payments, you will be able to set the cost for each time slot. 
3.	Seats
The seats behavior determines if you want to allow a single individual booking per slot or the same slot can be booked multiple times. 
> NOTE: In this edition, you cannot set the number of seats available per slot nor take time timeoff.

Now click create. Your availability in the front-end calendar will show the time slots created. 

> NOTE: If for seats you selected "One seat per slot", in this case when a slot on any particular date is booked, it wont be available for a second booking.

##### Styles tab
The booking form displayed in the front-end can be styled. We offer basic styling and advanced styling. 

###### BASIC
Basic styling will allow you to change the color of the main menu and the navigation button.

1. Select a Theme
This setting allows you to choose from one of 3 predefined color schemes available. The color scheme will apply to the wizard containing the booking form.
2. Font-Family and Font-size
This applies to fonts used in the wizard
3. Thumbnail width and height
This applies to the images you set on the availability. The image displays in the wizard when the availability is active.

###### ADVANCED
You can also control the presentation of the summary data found within the wizard. This will allow you to change the entire HTML structure of the content but keep in mind that some basic knowledge of HTML is required.

Importantly, try to pay particular attention to the existing tokens and the braces around the tokens. Some tokens are enclosed within 2 curly braces and others within 3. 

We provide this level of customization to power users who know their way around templating engines. For a list of tokens, expand the tokens and control statement sections.

The templating engine in use will try to alert you of mistakes made, however this may not happen always. In case things don’t look right, reset the template and start over. A reset button will appear after saving your first customization.

##### Text tab
The plugin will display textual content in the front-end based on various conditions. 
> NOTE: In this edition, translations can be done manually using a separate third party plugin such as PoEdit.

If you just want to quickly edit text displayed in the front-end, this can be done by going in the Service Text tab. Here you can customize content quickly and effectively, so that the text reflects content specific to your business. 

This is service specific. So, if you have several services you will need to repeat this again for each service. A tool that will come in handy in this case is the “Duplicate” option found in the “Services” page.

##### Short codes tab
A service can be inserted into a page or post to allow booking by copying the generated short code and pasting it into your page or post.

To find the short-code of your service, go into the short codes tab found in the “Services” page and select a service in the left page. Then copy the short code generated in the right pane. 

If you select multiple services, this will add a service switcher dropdown list in your booking form allowing your customer to switch services on the fly. 

#### SETTINGS
The settings page is where you can control various configurations of the plugin.
##### The general tab
This is where you may change the general behavior of the plugin such as approval, notifications, date/time formats and so forth. 
#### The emails tab
The plugin sends out several notifications to customers regarding the status of their booking. General properties such a sender name and email, the admins email address, the logo of your company to include in the emails and color settings can be managed on this screen.

If you wish even more control, i.e. if you want to include custom content within the emails, select the template and add your modifications.

> Importantly, try to pay particular attention to the existing tokens and the braces around the tokens. Some tokens are enclosed within 2 curly braces and others within 3. 

Note that the templating engine in use will try to alert you of mistakes made, however this may not always happen. So, make sure you test the emails to catch any mistakes you may have made. In case things don’t look right, reset the template and start over. A reset button will appear after saving your first customization.
#### The payments tab
Here you can add or edit payment methods supported by the plugin. 
> NOTE: In this edition we support PayPal only and is not limited in anyway, for more options checkout our premium version.

##### PAYPAL
First ensure that you have selected a currency that is PayPal supported, next proceed.
1. Check the enable field.
2. Provide your email associated to your PayPal account.
3. Set the title field. This value is used for the Payment operator selection in the front-end. 
4. If you are still in the planning stages, check “Sandbox”. This will allow you to test before going live.
5. Next save.

#### Error log tab
Any errors encountered during the normal operations of the plugin will be logged here. This can be useful if something isn’t working, such as emails failing, payments rejected by a payment operator etc. The error log is cleared periodically or can also be cleared manually after viewing.

#### Assets tab
Third party CSS and JavaScript files registered by the plugin can be disabled here. This is useful in case there are third party plugins or themes causing a conflict because they too include the same libraries. Generally, we do not recommend you disable any assets because sometimes the versions may differ and this will cause Calendarista to function incorrectly.

Some of the options worth a mention:
1. A setting noteworthy mentioning is “Debug mode”. This option when will include the full uncompiled versions of the client-side libraries. This may help sometimes when debugging issues.
2. The calendar used in the front-end can also be themed here. Simply select one from the various available themes in the “Calendar themes” field. Currently we include a single theme to keep the plugin size compact.
3. Set the URL to a Font file using the Font-Family URL field. The font-family name itself needs to be set in the services page, styles tab.
#### SALES
The sales page is where you can view bookings that sold. These are bookings made from services that have payment enabled and a valid cost amount. Free bookings are excluded from this list.

In addition to sales being listed, you can also view details of the booking and request payment for “unpaid” ones. This will send an invoice to the user to solicit payment or you can confirm payment in case you are collecting payment offline.

In addition, you can also view the appointment.
#### APPOINTMENTS
From the appointments page, all appointments made will be displayed in a calendar view. Additionally, selecting an appointment on any particular date, you will be able to view and cancel or delete the appointment.
#### TRANSLATIONS
Editing text is possible from the plugins services page. After selecting a service, head on to the Text tab and edit any piece of string that is to be seen in the front-end. However, while it allows you a quick way to modify text, this may not always be convenient especially if you plan to support multiple languages. 

But do keep in mind, if you plan to create translation files, then in this case you will need to reset text strings in the “Text” tab mentioned above.

Calendarista stores its language files in the plugins "languages" folder, i.e. wp-content\plugins\calendarista\languages\.

After creating your .mo and .po files, usually done through a third-party application such as poEdit, ensure that the file is named based on the domain "calendarista" followed by a dash, and then the locale.

The locale is the language code and/or country. For example, the locale for German is 'de_DE', and the locale for Danish is 'da_DK'. 

So, for instance, if we were to translate to Danish, the .mo and .po files should be named "calendarista-da_DK.mo" and "calendarista-da_DK.po".

As we issue updates, you are responsible for maintaining your language files updated. This makes saving your language files within the plugins languages folder quite a chore, hence we recommend you to instead store your new language files at the following location: /wp-content/languages/calendarista/

This has the advantage of you not having to worry about when updates are available as the language resource is stored outside the plugin directory.

== Changelog ==

= 3.0.8 =
* Fixed: miscellaneous fixes and improvements, including security.

= 3.0.7 =
* Fixed: regression bug, saving [services > style > advanced] summary template feature broken.

= 3.0.6 =
* Fix: regression bug, services page bugged out and did not allow creating new services.
* Fix: miscellaneous minor fixes.

= 3.0.5 =
* Fix: regression bug, optional extras, custom form fields with checkbox/radio button lists choked app during the booking.
* Fix: regression bug, editing time slot from the backend did nothing.

= 3.0.4 =
* Fix: regression bug, optional extras step failed during the booking.

= 3.0.3 =
* Fixed: security update.
* Fixed: backend sales page order by columns.

= 3.0.2 =
* Fixed: security update.
* Fixed: miscellaneous minor fixes.

= 3.0.1 =
* Fixed: When returning from Paypal, a thank you message is now displayed correctly with invoice ID

= 2.0.9 =
* Fixed: security vulnerability.
* Fixed: round trip with time no longer enforces a continous time range on same day return.
* Fixed: round trip with time had inconsistent end time selection behavior on same day booking.
* Fixed: when cancel booking url was disabled from settings > general page, the cancel url was still present in the notification.
* Fixed: confirmation email was sent twice when using online payments or woocommerce.
* Fixed: if your translations included quotes, this could break the appointments page in the backend.
* Fixed: timeformat in settings > general page did not save your selection.
* Fixed: search list timeformat did not apply the settings > general page time format settings.
* Fixed: selecting a service or availability before page loaded did nothing.
* Fixed: editing appointments from the backend miscellaneous code improvements and fixes.
* Fixed: switching between register and login when membership was enabled, now field input is maintained.
* Added: improvements and additional features when creating/autogenerating timeslots.
* Added: manual dates added in the services > timeslots page will be removed automatically if expired.
* Added: autogenerating slots can now be done on all availabilities in one go.
* Added: new option in services > map page to hide the map but keep the location.
* Added: thankyou/review us email via CRON job, that can be timed at any period after the appointment.
* Added: services can now be listed by thumbnail and name (previously only in a dropdownlist).
* Added: availability's can now be listed by thumbnail, name and description(prev only as a dropdownlist).
* Added: instructions can now to be inserted in the add to calendar links and improved the appointment data as well.
* Added: new optional extra for incremental field with thumbnail and description.
* Added: new option to enable flat deposit by seats.

= 2.0.8 =
* Fixed: when using the multi date and time range mode, unavailable days were selectable on the calendar.
* Fixed: the text "repeat this appointment" can now be edited from the services > text page.

= 2.0.7 =
* Fixed: regression from last update. The date textbox always showed loading...even though loading was completed.

= 2.0.6 =
* Fixed: tabs hidden in normal view when new option to disable steps in mobile view was checked.
* Fixed: when certain conditions were met, the popup calendar inserted the last selected date even when no selection was made.

= 2.0.5 =
* Added: new option in general settings to enable/disable the wizard steps in mobile view.
* Fixed: editing appointment from the backend, service selector disappeared after making a selection.
* Fixed: editing/creating appointment from the backend was lacking validation checks.
* Fixed: public calendar was missing full date format hence the day view did not display the current date number.
* Fixed: remaining seats message when using the multi date range mode always displayed the value 0.
* Fixed: when using a service mode with timeslots and when all seats were out of stock, editing the appointment broken.

= 2.0.4 =
* Added: new option to add discount when deposits are active
* Added: when deposits are active, customer can now choose to pay full amount (optionally with discount)
* Added: creating/updating appointments from the backend will now show available space.
* Fixed: clear button on the public popup calendar is now translatable.
* Fixed: app choked when applying coupon codes.
* Fixed: updating an appointment from the back-end caused the appointment to duplicate.
* Fixed: regression bug, under specific condtions, editing appointments in the backend did nothing.
* Fixed: inconsistencies when using the round trip with time mode and group booking.
* Fixed: trying to resend a reminder did nothing.
* Fixed: miscellaneous fixes.

= 2.0.3 =
* Fixed: regression bug when synching seats with the single day mode.
* Fixed: miscellaneous fixes.

= 2.0.2 =
* Added: extended group booking support to multi date range, multi date and time range, round trip, round trip with time and changeover
* Fixed: synchronizing availabilities did not work correctly specifically when syncing half day/full day etc.
* Fixed: when the repeat pattern was set to weekly, the calendar in the booking form worked erratically.

= 2.0.1 =
* Fixed: changing the currency thousand and decimal separators did not apply in the email notifications.
* Fixed: changing the currency thousand and decimal separators broke total_amount_before_tax token.

= 2.0 =
* New: WordPress 6.1.1 compatibility.
* New: Expanded featureset, too many to list here compared to the previous 1.5 version.

= 1.5 =
* Fixed: WordPress 5.5 compatibility.

= 1.4 =
* Fixed: timeslots in availability were not getting saved after creation.

= 1.3 =
* Fixed: WordPress 5.1 compatibility.

= 1.2 =
* Fixed: emails had the default background color of white by default. 

= 1.1 =
* Added: {{customer_email}} token to emails.
* Added: search by customer name in back-end sales page and improved date selector with clear date functionality.
* Fixed: Deleting an availability in the back-end failed.
* Fixed: date formats weren't respected correctly and missing zero padding when necessary.

= 1.0 =
* Initial release