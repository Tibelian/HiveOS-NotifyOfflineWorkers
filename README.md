# HiveOS-NotifyOfflineWorkers
Detect offline workers of a specific HiveOS farm and notify by mail which one is down.

## Installation
1. Download this repository
```bash
git clone https://github.com/Tibelian/HiveOS-NotifyOfflineWorkers.git
```
2. Use the dependency manager [composer](https://getcomposer.org/) to install PHPMailer
```bash
composer install
```

## Configuration
1. Edit the __/include/config.json__ file to insert the "Generated Personal API Token" from [HiveOS](https://the.hiveos.farm/account) and your Farm ID
2. Edit the __/include/mailer.json__ file to configure your outgoing mail

## Usage
1. Create CRON Job to run the __index.php__ file each X minutes/hours/days... 
For example: cron style to execute the script each 3 minutes 
```bash
*/3 * * * *
```
