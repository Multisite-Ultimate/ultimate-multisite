# Downgrading a plan (v2)

_**IMPORTANT NOTE: This article refers to Multisite Ultimate version 2.x.**_

Downgrading a plan or subscription is a common action your clients might do if they have a limited budget or they decided that they won't need many resources to run their subsite.

## How to downgrade a plan

Your clients can downgrade their plan anytime by logging in to their subsite admin dashboard and clicking **Change** under their account page.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-sprLyB2iMU.png)

Upon clicking the **Change** button, the user/client will be redirected to the checkout page where they can select the plan they want to change their subscription to.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-aTnhJPFVFh.png)

In this example, we are downgrading the plan from **Premium** to **Free**.

To proceed the user just need to click the **Complete Checkout** button. It will then bring them back to the account page showing a message about the pending change for the membership. The changes will take effect on the customer's **next billing cycle**.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-E2qcjxzDDG.png)

### What happens when a user downgrades their plan

It is important to note that downgrading the plan does not alter the existing configuration in the user's subsite.

It does not automatically change the site template since changing the site template will completely erase and reset the subsite. This is to avoid unnecessary data loss. So disk space, themes, plugins etc will be intact except for the posts.

We understand that your main concern would be the limits and quotas you set under each plan but we have to consider the damage it would do to the user's subsite should we delete or change any of its configurations.

For the posts exceeding the limit set on the plan, you have 3 different options: **Keep the posts as it** *,* **Move the posts to trash** *,* or **Move the posts to draft** *.* You can configure this under Multisite Ultimate settings.

![](https://wp-ultimo-space.fra1.cdn.digitaloceanspaces.com/hs-file-ztHV8cZDG0.png)

### What happens to the payment

In version 2.0, it no longer requires any adjustments on the payment in terms of proration.

This is because the system will wait for the existing membership to **complete its billing cycle before** the new plan/membership will take effect. The new billing amount for the new membership will automatically be applied and charged on the next billing cycle.
