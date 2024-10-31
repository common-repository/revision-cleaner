=== Revision Cleaner ===
Contributors: allarem
Tags: revision,cleaner,admin
Requires at least: 3.1
Tested up to: 3.9
Stable tag: 2.1.5

License: GPLv2 or later

This plugin will clean up your revisions AUTOMATICALLY (each authors can set their own interval). Once setup, ENJOY your writing.

== Description ==

Revision is a very useful stuff when you experienced an unexpected power failure or keyboard got peed by your cat while you're writing an article or just simply revise what you've wrote. After you've "finally" posted your article, the revisions, still consuming your SQL database as long as the database last. To save your database storage, wipe out the revisions is necessary and this is what Revision Cleaner do in AUTOMATICALLY and safely (won't delete any draft :) ). "Clean interval" setting support multiple-users, they can set their own clean interval (admin can control it and assimilated everyone of course).Email:mengzhuo1203(at)gmail.com

== Installation ==

Upload the Revision Cleaner plugin to your blog, Activate it.

== Screenshots ==

1. Option in General Setting(For Administrator)
2. Overview every user's setting(For Administrator)
3. Each user can set their own clean interval in Personal Profile.


== Languages ==
If you like to translate Revision Cleaner, the .POT file in the "po" directory under plugin directory. 
 after you've translated it please send it to mengzhuo1203(at)gmail.com, and attach with po file name by what your language called in English.

== Changelog ==

= 2.1.5 =
* FIX: prepare missing arg two bug

= 2.1.4 =
* ADD: Dutch Translation

= 2.1.3 =
* FIXED:Crashed while update user profile
* Change:Remove crashed type 4 & 5

= 2.1.2 =
* CHECKED: Compatibility with WP 3.3
* FIXED: Won't remove draft's revision in MYSQL 4.*
* Added: Auto Repair the Error 5

= 2.1.1 =
* Add:delete the old setting while not valid
* Fixed:the bug http://wordpress.org/support/topic/plugin-revision-cleaner-can-activate-the-plugin

= 2.1 =
* Fixed:deactivate function (wordpress's own deactivate function can't work properly, kind of weird).
* Fixed: Tons of BUGs...
* Add:contact message while failed.
* Add:Designate specific user's setting as admin's for those site don't has ID 1 user (Neo).
* Remove: Keep the Last Revision, This feature seems too stupid.

= 2.0.2 = 
* Add:Try to deactivate REVISION_CLEANER when it goes crazy
* Fix:New Administrator can't set his profile
* Upgrade: new option will not autoload to do things more efficient.

= 2.0.1 = 
Quick Fix Can't find Neo, I'm very sorry for any inconvenience ...

= 2.0 =
You can IGNORE this upgrade if your wordpress has only one author
This is version makes it brand new plugin!
* Multiple sites & authors supported
* Add option on delete draft's revision
* Optimized data structure
* require JavaScript this version

= 1.2 =
Fixed some bugs

= 1.1 = 
* improved the efficiency
* added "keep last revision"
* enhanced UI

= 0.1 =
Brand New start

