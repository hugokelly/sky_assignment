# Monitoring Solutions Technical Test

Sky assignment technical test submission - Hugo Kelly 04/02/2020

## Installation (Docker)

I have created a web application running on localhost using docker containers for Apache, PHP, MySQL and Python. The instructions will be for a Windows machine but as long as you have docker installed you may run it on anything. See below for the versions I used:

- APACHE 2.4.41
- PHP 7.4.2
- MYSQL 8.0.18
- PYTHON 3.8.1

To install: 
1. Download the files from this git repository
2. Unzip to a directory on your machine
3. Open a command line and `cd` to the new directory
4. Build the docker containers using `docker-compose build`
5. Start the docker containers using `docker-compose up -d`

## MySQL Database

Once you have built and started docker, the database and table used in the project should be present and ready to go with some data from 04/02/2020 when I was developing and testing. For persistent  data I have stored the mysql volume in the `/mysql` directory. The table was created using the following SQL:

```sql
USE local_db;
CREATE TABLE `cpu_log` (
	`timestamp` INT(11) NOT NULL,
	`cpuLoad` FLOAT NULL DEFAULT NULL,
	`concurrency` INT(6) NULL DEFAULT NULL,
	PRIMARY KEY (`timestamp`)
) COLLATE='utf8_general_ci';
```

## Ingestion Layer

I wrote the python script `/python/ingest.py` with the intention of using it to populate the database but could not get the db connection working in time. Feel free to inspect the code and try it on your machine. I believe it is an issue with the port binding between docker containers but to get around this I created a model in Codeigniter for mimicking the functionality. This can be run from the following URL:

[http://localhost/welcome/ingest](http://localhost/welcome/ingest)

Upon navigating here, the ingest function will run and you should see something similar to the following in the browser:

```html
Inserted Row @ 1580860081 (2020/02/04 23:48)
Inserted Row @ 1580860021 (2020/02/04 23:47)
Inserted Row @ 1580859961 (2020/02/04 23:46)
Inserted Row @ 1580859901 (2020/02/04 23:45)
Inserted Row @ 1580859841 (2020/02/04 23:44)
```

## PHP / Web App

I have used the Codeigniter 3.1.11 framework to build the web app and Slim Framework 4.4.0 for the rest API. The files are all located in `/public_html`. The codeigniter MVC files are located in `\public_html\application` and the API runs from the `\public_html\api\index.php` file. The API works by querying a unix timestamp range set from the web app front end.

To run the web app after installing the project simply navigate to the following URL:

[http://localhost/](http://localhost/)

On navigating to the web app you should see the following:

![Web App Screenshot](https://i.imgur.com/VLOVJKp.jpg)

You have a basic filter for selecting your date/time range, for this I used Jquery UI's datepicker plugin with a timepicker addon. Below the filter is the graph output for displaying any data within your selected range. The stats are listed under the graph and both will be updated as soon as the date/time range is changed via AJAX. There is a reset button in the filter for quickly changing the date/time range to view the last hour and when data is present you also have the option of toggling between the CPU load and concurrency metrics.

## Improvements & Notes

I attempted to start writing some unit tests for the project but ran out of time and only got round to making an example test located here:

[http://localhost/tests/welcome](http://localhost/tests/welcome)

On navigating here you should see the following:

![Testing Screenshot](https://i.imgur.com/MELuadS.jpg)

Given more time I would have liked to make the following changes / additions:

1. Get the python script connecting to the database and use that for the ingestion layer so I don't need a db connection from the web app.
2. Use PHP Symfony instead of Codeigniter or even the latest version of Codeigniter but due to the time constraints I stuck to what I know best.
3. Write some proper unit tests and add function / end to end testing.
4. Use a Slim container for the Slim framework db connection.
5. Update the stats section whenever the graph is zoomed in on so that it applies correctly to the data currently visible.