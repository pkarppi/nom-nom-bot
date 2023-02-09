# Quickstart

1. `composer install`
2. `cp .env.dist .env`
3. Put your coordinates to .env LAT and LNG
4. Show your nearby restaurants with `php run nearby`
5. Configure selected restaurants `RESTAURANTS` in .env like `RESTAURANTS='["Name", "Name2"]'` 
6. Preview message with `php run preview`
7. Put SLACK_WEBHOOK to .env and test it with `php run test-slack`
8. Cron it with `php run slack`

# .env variables 
```
LAT=
LNG=27.6802581
RESTAURANTS='["TOPCHEF"]'
SLACK_WEBHOOK=https://xxxx
```

# Commands 
`php run.php nearby` - Show nearby restaurants
`php run.php preview` - Preview message
`php run.php slack` - Send message to Slack
`php run.php test-slack` - Test slack

