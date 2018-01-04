# ozanhazer/testlistener

Tap listener is removed in PHPUnit v6 however some CI servers like PHPCI still
depends on it. This is the port of the original `PHPUnit_Util_Log_TAP`.

## Usage

    composer require --dev ozanhazer/testlistener
    phpunit --printer Erelyr\TapListener

You can also add as a `<listener>` in your phpunit.xml if you need to.
