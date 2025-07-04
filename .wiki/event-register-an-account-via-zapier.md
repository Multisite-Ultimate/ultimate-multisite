# Event: Register an account via Zapier

In the article [Integrating Multisite Ultimate with Zapier](1677127282-integrating-wp-ultimo-with-zapier.html), we discussed how to use Zapier to perform different actions within Multisite Ultimate based on triggers and events. In this article, we will show how you can integrate 3rd party applications. We will use Google Sheets as the source of data and send the information to Multisite Ultimate to register an account.

First, you need to create a **Google Sheet** under your Google Drive. Make sure you properly define each column so that you can easily map the data later.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-7wnYotvYtO.png)After creating a Google sheet, you can log in to your Zapier account and start creating a zap.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-C0khOzCSCF.png)Under the search field for **"App event"** select **"Google Sheets"**

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-Cj2rk0zpOO.png)

Then for the "**Event** " field select "**New spreadsheet row** " and hit "**Continue** "

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-Y6z9NX6HAn.png)The next step will ask you to select a **Google Account** where the **Google Sheet** is saved. So just make sure that the right google account is specified.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-536o0FgLI1.png)

Under **"Set up trigger** ", you will need to select and specify the google spreadsheet and worksheet you will use where the data will be coming from. Just go ahead and fill those out and hit "**Continue** "

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-4juCX9m6M2.png)Next is to "**test your trigger** " to make sure that your google sheet is properly connected.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-E1RjprMVNM.png)If your test is successful, you should see the result showing some values from your spreadsheets. Click "**Continue** " to proceed.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-FNVMJRdoBs.png)The next step is to set up the second action that will create or register an account in Multisite Ultimate. On the search field select "**Multisite Ultimate(2.0.2)** "

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-bbSevglDSJ.png)

Under the "**Event** " field, select "**Register an Account in Multisite Ultimate** " then click the "**Continue** " button.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-DZTN6Hno0w.png)Under "**Set up an action** ", you will see different fields available for customer data, memberships, products, etc. You can map the values under your google sheet and assign them to the proper field where they should be populated as shown in the screenshot below.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-c1ozo05Uam.png)

After mapping the values, you can test the action.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-UKI9kdBjIc.png)
