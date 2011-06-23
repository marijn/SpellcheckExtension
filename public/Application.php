<?php

  use Symfony\Component\HttpFoundation\Response;

  require_once __DIR__ . '/../vendor/Silex/autoload.php';
  require_once __DIR__ . '/../src/GoogleSpellcheckRequestBody.php';

  $app = new Silex\Application();

  $app['spelling.dictionary'] = './dictionary';
  $app['spelling.language'  ] = 'en';
  $app['spelling.google'    ] = 'https://www.google.com/tbproxy/spell?lang=%language%&hl=en';
  $app['buzz.class_path'    ] = __DIR__ . '/../vendor/Silex/vendor/Buzz/lib';

  $app->register(new Silex\Extension\BuzzExtension());

  function extract_suggestion ($arg_input)
  {
    $format  = '/<c o="([^"]*)" l="([^"]*)" s="([^"]*)">([^<]*)<\/c>/';
    $matches = array();

		preg_match_all($format, $arg_input, $matches, PREG_SET_ORDER);

    return $matches;
  }

  $app->get('/{arg_language}/check.json', function ($arg_language) use ($app)
  {
    $headers = array("Content-Type" => "application/json; charset=UTF-8"
                    );

    $uri                  = strtr($app['spelling.google'], '%language%', $arg_language);
    $text                 = $app['request']->get('text');
    $suggestions          = array();
    $googleRequest        = (string) new GoogleSpellCheckRequestBody($text);
    $googleRequestHeaders = array("Request-Number" => 1
                                 ,"MIME-Version"   => "1.0"
                                 ,"Document-Type"  => "Request"
                                 ,"Content-Type"   => "application/xml; charset=UTF-8"
                                 ,"Content-Length" => strlen($googleRequest)
                                 );

    $googleResponse = $app['buzz']->post($uri, $googleRequestHeaders, $googleRequest);

    $suggestions = array();

    foreach (extract_suggestion($googleResponse->getContent()) as $suggestion)
    {
			$word = substr($text, $suggestion[1], $suggestion[2]);

			if ( ! in_array($word, $suggestions))
			{
				$suggestions[] = $word;
			}
    }

    $response = new Response(json_encode($suggestions), $googleResponse->getStatusCode(), $headers);

    $response->setLastModified(new DateTime("now"));
    $response->setSharedMaxAge(60 * 60 * 24);
    $response->setLastModified(new DateTime("yesterday"));
    $response->setPublic();

    return $response;
  });

  $app->get('/{arg_language}/suggest.json', function ($arg_language) use ($app)
  {
    $headers = array("Content-Type" => "application/json; charset=UTF-8"
                    );

    $uri                  = strtr($app['spelling.google'], '%language%', $arg_language);
    $googleRequest        = (string) new GoogleSpellCheckRequestBody($app['request']->get('text'));
    $googleRequestHeaders = array("Request-Number" => 1
                                 ,"MIME-Version"   => "1.0"
                                 ,"Document-Type"  => "Request"
                                 ,"Content-Type"   => "application/xml; charset=UTF-8"
                                 ,"Content-Length" => strlen($googleRequest)
                                 );

    $googleResponse = $app['buzz']->post($uri, $googleRequestHeaders, $googleRequest);
    $suggestion     = extract_suggestion($googleResponse->getContent());

    $result = isset($suggestion[0]) && isset($suggestion[0][4]) ? $suggestion[0][4] : array();

    $response = new Response(json_encode(explode("\t", $result)), $googleResponse->getStatusCode(), $headers);

    $response->setLastModified(new DateTime("now"));
    $response->setSharedMaxAge(60 * 60 * 24);
    $response->setLastModified(new DateTime("yesterday"));
    $response->setPublic();

    return $response;
  });

  $app->run();
