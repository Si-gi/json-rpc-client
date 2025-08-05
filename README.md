# json-rpc-client
A PHP json RPC client for shitty json-rpc API

JSON-RPC specifiations: https://www.jsonrpc.org/specification

tested with https://api.random.org/json-rpc/1/ and limesurvey RPC API (and created because of limesurvey RPC API)

JSON-RPC and XML-RPC tend to be deprecated everywhere, sso feel free to consider other options if possible.
# installation

```
composer require si-gi/jsonrpc-php
```

Then create a config file somewhere
If you want to include defaut params, to send with each request such as the API KEY, you must add a defaultParams key in your config file and include all you default parameters

#  Demo with LimeSurveyAPI
```
$config = ConfigBuilder::build(__DIR__."/config.json", new Config());

// echo $config->getEndpoint();
$transport = new CurlTransport(new CurlClient());
$client = (new RPCClient($transport, $config, new MessageFactory()));

$sessionKey = $client->call('get_session_key', [$config->get("LS_USER"), $config->get("LS_PASSWORD")]);

```

This project is covered at 100% by test, and i hope will be useless one day, despite we have admit that in some case, RPC is usefull

Feel free to contribute and to make suggestions.