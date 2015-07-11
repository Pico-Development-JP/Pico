高見知英個人サイト おんぷ村のサイトエンジンです。Picoをベースにしています。

# 高見知英追加部分

 * 定期的にPHPファイルを実行して、記事を追加更新するスクリプトを配置可能にした(/generate/ディレクトリ以下)
 * Webhookに対応し、Webhook呼び出し時にサイトを更新する機能を追加した(/pull/ディレクトリ以下)

## インストール
本家Picoと同様です。git cloneコマンドでこのリポジトリをクローン後、以下のコマンドを実行します。

    $ curl -sS https://getcomposer.org/installer | php
    $ php composer.phar install

### 追加機能の有効化

#### cronを用いたページの自動追加
また、このバージョンではcronで定期的に処理を行うことによって、自動的に記事を追加更新することが可能になっています。
crontabに以下の行を追加してください。

    0 * * * * [Picoまでの実パス]/generate/page_generator.sh

次に、page_generator.shを開き、必要に応じて以下のように記載を変更します。
    
    cd `dirname $0`
    [PHPまでの実パス]/php page_generator.php

これで、1時間に1回、ページ生成モジュールが実行されます。モジュールの書き方は、generate/nodules/generate_modules.phpや、[自分の作成した自動更新用モジュール](https://github.com/TakamiChie/pico-slideshare/blob/master/pico_slidesharelist.php)を参照してください。

#### リポジトリの更新に追従する
また、GithubやBitbucketのWebhookによりサイトを更新するためのphpファイルも用意しています。それぞれのサービスにあるWebhookにて、[サイトのURL]/pull/pull.phpをWebhookで呼び出すようにしてください。
サイトがgit cloneコマンドによってクローンされているようであれば、これでサイトが更新されます。

Pico
====

Pico is a stupidly simple, blazing fast, flat file CMS. See http://picocms.org/ for more info.
<!--flippa verify-->
[![I Love Open Source](http://www.iloveopensource.io/images/logo-lightbg.png)](http://www.iloveopensource.io/projects/524c55dcca7964c617000756)

Install
-------
Requires PHP 5.3+

Download [composer](<https://getcomposer.org/>) and run it with install option.

    $ curl -sS https://getcomposer.org/installer | php
    $ php composer.phar install

Run
---

The easiest way to Pico is using [the built-in web server on PHP](<http://php.net/manual/en/features.commandline.webserver.php>).

    $ php -S 0.0.0.0:8080 ./

Pico will be accessible from <http://localhost:8080>.

Wiki
---
Visit the [Pico Wiki](https://github.com/picocms/Pico/wiki) for plugins, themes, etc...
