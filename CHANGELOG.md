# v1.2.2
## 14-01-2019

1. [](#bug)
    * [solved] Problem with assets organization solved, button sometimes didn't show properly

# v1.2.1
## 28-10-2018

1. [](#bug)
    * [solved] Button was not removed, even when admin cookie did not exist (anymore)

# v1.2
## 25-10-2018

1. [](#modification)
    * Restructuring of the assets folder and combined sass/css.
    * Corrected bug where button was only shown after doing a page refresh

# v1.0.12.1
## 19-10-2018

1. [](#bugfix)
    * Corrected the check if admin is logged on. In the former version the button was not on shown when not logged in.
    * Version 1.12 (former version) is not working with release 1.5 and later of Grav CMS. 

# v1.0.12
## 22-07-2018

1. [](#bugfix)
    * Corrected that the button still was showing sometimes, even after the admin was logged out. (Paul Hibbits, thanks for noticing)

# v1.0.11
## 16-07-2018

1. [](#bugfix)
    * Corrected that page kept on refreshing, even after logged out. Now it refreshes 1 time after logging out to remove the button and the JavaScript to reload the page.

# v1.0.10
## 13-07-2018

1. [](#bugfix)
    * Button is not displayed anymore when admin has logged out, button disappears after refresh (when clicking the tab with the page)
 

# v1.0.9
## 02-06-2018

1. [](#feature)
    * Automatic update of the contents in other tabs is now optional

# v1.0.8
## 23-03-2018

1. [](#feature)
    * Button icon is now an independent font for this plugin
2. [](#new)
    * New language Croatian


# v1.0.7
## 16-03-2018

1. [](#feature)
    * Optional, if user has to be logged in before showing the button

# v1.0.6
## 15-03-2018

1. [](#bugfix)
    * Fix for incorrect edit button href
2. [](#new)
    * New language Spanish and Catalunian

# v1.0.5
##  27-01-2018

1. [](#bugfix)
    * Solved problem of multilanguage URL redirecting to wrong corresponding URL in backend

2. [](#bugfix)
    * Updated README.md content

# v1.0.4
##  16-12-2017

1. [](#bugfix)
    * Solved the hardcoded '/admin' in relocate URL in case the admin route has been modified to something other than '/admin'

2. [](#bugfix)
    * Updated README.md content

3. [](#bugfix)
    * Renamed some files

# v1.0.3
##  14-10-2017

1. [](#bugfix)
    * Solved problem when Grav is installed in sub folder

# v1.0.2
##  01-11-2017

1. [](#bugfix)
    * Solved issue when Grav is installed in sub folder 


# v1.0.1
##  04-08-2017

1. [](#bugfix)
    * Problem solved with `NULL` on the `header()` function
     in the `onOutputGenerated` event


# v1.0.0
##  04-03-2017

1. [](#new)
    * Initial version committed to Github
