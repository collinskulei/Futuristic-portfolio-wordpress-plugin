# Futuristic-portfolio-wordpress-plugin
Futuristic portfolio CPT with media upload, features, whatsapp, frontend ratings, views, likes, and full display color settings.
Comprehensive User Guide: Futuristic Portfolio Display Plugin
This guide explains how to install, configure, and use the Futuristic Portfolio Display WordPress plugin, which allows you to showcase projects with detailed information, user ratings, and futuristic styling.

1. Installation and Setup
This plugin creates a custom area in your WordPress dashboard for managing your portfolio entries.

1.1 Installation
Save the Code: Save the provided PHP code into a single file named futuristic-portfolio-display.php.
Upload: Upload the futuristic-portfolio-display.php file to your WordPress plugins directory (wp-content/plugins/).

Activate: Navigate to Plugins in your WordPress dashboard and click Activate for the "Futuristic Portfolio Display (All Features)" plugin.
1.2 Accessing the Custom Post Type (CPT)
Once activated, a new menu item will appear in your admin sidebar: Add Project. This is where you will create and manage all your portfolio items.

2. Creating and Configuring Projects
Every portfolio item is a "Project" custom post.
2.1 The Project Details Meta Box
When creating a new project (under Add Project > Add New), you will see the Project Details meta box, which contains all the custom fields for the portfolio display.
Field Name
Purpose
Notes

Display Title
The main title of the project.
Defaults to the standard WordPress post title if left blank.
Problem it Solves
A brief explanation of the project's purpose.
Displayed prominently in the pop-up modal.
Project Link (Visit Live)
The URL to the live project/website.
Used by the "Visit Live" button on the front-end.
Image 1
The thumbnail image displayed on the main portfolio grid card.
Use the Upload / Select button to choose from your media library.
Image 2
The large feature image displayed inside the pop-up modal.
This often provides more detail than the thumbnail.
Features
A list of key features.
Enter each feature on a new line or separate them with commas. They will display as a bulleted list in the pop-up.
WhatsApp Number
A contact number (with country code).
Used for the "I Want Like This" button, which directs users to start a WhatsApp chat with a pre-filled message about the specific project.
2.2 Read-Only and Editable Statistics
The bottom of the Project Details box shows performance metrics.
Avg Rating & Review Count: These fields are read-only and are automatically calculated and updated by the plugin when users submit ratings on the front-end.
Likes & Views: These are automatically incremented by user activity, but they are also editable here. You can set initial values or manually adjust the counts as needed.

3. Customizing the Futuristic Look
The plugin includes a dedicated settings page to control the colors and achieve your desired aesthetic.
3.1 Accessing Display Settings
Go to Settings > Futuristic Portfolio.
3.2 Color Options
The front-end design uses the following color settings:
Settings
Function
Default Color
Card Background
The background color for the individual project cards in the grid.
#0a0f24 (Very Dark Blue)
Card Title Color
The color of the project title on the main card.
#00eaff (Neon Cyan)
Popup Background
The background color of the large project details modal.
#0a0f24 (Very Dark Blue)
Popup Border/Glow
The color of the border around the modal, which also creates a subtle "glow" effect.
#00eaff (Neon Cyan)
Button Color
The background color for the main action buttons ("Visit Live", "I Want Like This").
#00eaff (Neon Cyan)
Popup Text Color
The color of the general text inside the modal.
#eafcff (Off-White/Light Cyan)

Be sure to click the Save Settings button after making any color changes.


4. Displaying the Portfolio (Shortcode)
To show the portfolio grid on any WordPress page or post, simply insert the plugin’s shortcode:
Shortcode Usage
Insert one of the following shortcodes into the content editor of the page where you want the portfolio to appear:
[futuristic_portfolio]

If you need help with setup please Whatsapp  me at +254700574125
