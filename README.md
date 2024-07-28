# PHPGramBot

PHPGramBot is a simple bot framework for Telegram built with PHP. Follow the instructions below to set up and configure your bot.

## Setup Instructions

1. **Clone the Repository**

   Start by cloning the repository to your local machine:

   ```bash
   git clone https://github.com/e1l1ya/PHPGramBot.git
   ```

2. **Navigate to Project Directory**

   Change directory to the `PHPGramBot` folder:

   ```bash
   cd PHPGramBot
   ```

3. **Install Dependencies**

   Run `composer update` to install all the necessary dependencies:

   ```bash
   composer update
   ```

4. **Configure the Bot**

   Edit the `config/bots.php` file to replace `BOT_TOKEN` with your Telegram bot token. You can find your bot token by creating a new bot on Telegram. as you can see here is database information

   ```php
   return [
      "simple_token" => "BOT_TOKEN",
      "BOT_TOKEN" => [
         "name" => "Simple",
         "class" => "SimpleBot",
      ],
      "simple_db" => [
         "database" => "bot_db",
         "hostname" => "localhost",
         "user" => "user_db",
         "pass" => "user_password"
      ]
   ];
   ```

5. **Create a Telegram Bot**

    - Open Telegram and search for the **BotFather**.
    - Start a chat with the BotFather and use the `/newbot` command to create a new bot.
    - Follow the instructions to set up your bot and get the bot token.

6. **Set the Webhook**

   After setting up the bot, set the webhook using the following URL, replacing `BOT_TOKEN` and `WEBSITE` with your actual bot token and website URL:

   ```bash
   https://api.telegram.org/botYOUR_TELEGRAM_BOT_TOKEN/setWebhook?url=https://YOUR_WEBSITE/?token=YOUR_TELEGRAM_BOT_TOKEN
   ```

    - **YOUR_TELEGRAM_BOT_TOKEN**: Your Telegram bot token obtained from BotFather.
    - **YOUR_WEBSITE**: Your server's URL where the bot is hosted. For example, `https://mywebsite.com`.

## Example Webhook URL

```bash
https://api.telegram.org/bot123456789:ABCdefGhiJKlmnOPQrstuVWxYZ/setWebhook?url=https://mywebsite.com/?token=123456789:ABCdefGhiJKlmnOPQrstuVWxYZ
```

Replace `123456789:ABCdefGhiJKlmnOPQrstuVWxYZ` with your actual bot token.

## Additional Information

- For further details on using the Telegram Bot API, visit [Telegram Bot API Documentation](https://core.telegram.org/bots/api).
```

Feel free to adjust the URLs and tokens as needed. This README file should guide users through the setup process clearly.