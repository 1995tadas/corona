# Covid-19 In Lithuanian

**Covid-19 In lithuanian language** is a website which displays global and national covid-19 cases data history in
simple manner. It targets lithuanian speaking audience.

# DEMO

https://tadassapitavicius.com/corona/

## Why this website exists ?

There are many lithuanians who do not understand english or other languages, so this project is for them.
I scouted all over the web while searching for global Covid-19 statistics in lithuanian language.
All I found was national cases, news articles, or unprocessed data, so I decided to create a dedicated website just for that.

## Data sources

Covid-19 Api:
https://covid19api.com/
[Data provided by Johns Hopkins University.]

Countries Api:
https://restcountries.eu/

Country flags Api:
https://www.countryflags.io/

Customizable charts:
https://www.chartjs.org/

# Technologies used

<ul>
<li>Php 7.4 </li>
<li>Laravel 8.19.0</li>
<li>VueJs 2.6.12</li>
<li>MySql 8.0.22</li>
<li>Sass 1.30.0</li>
<li>Composer 2.0.8 for php packages</li>
<li>NodeJs 6.14.11 for npm packages</li>
</ul>

# How to deploy

1. Copy all files from this repo</li>
1. Make sure you have right technologies installed</li>
1. Copy .env.example and rename it to .env (if you using unix operating system just run this command in app root `mv .env.example .env`)
1. Run `composer install` and `npm install`(needed dependencies will be installed)  
1. Then run `php artisan key:generate` and replace database credentials inside .env file
1. Migrate tables by running `php artisan migrate`
1. `php artisan cases:fetch` command will fetch all countries, summary, covid-19 cases and save it to database (it will also update data when ran again - if new data exists)
1. You can set up cron for daily updates, run `crontab e` and put this code at the end  `* * * * * cd path-to-your-project/corona && php artisan schedule:run >> /dev/null 2>&1`
1. You can also check if data needs to be updated by running this command`php artisan cases:needsUpdate`, (`php artisan cases:needsUpdate --update` check and update - if needed) 


