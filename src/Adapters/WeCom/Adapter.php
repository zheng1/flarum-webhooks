<?php

/*
 * This file is part of fof/webhooks.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\Webhooks\Adapters\WeCom;

use FoF\Webhooks\Response;

class Adapter extends \FoF\Webhooks\Adapters\Adapter
{
    /**
     * {@inheritdoc}
     */
    const NAME = 'wecom';

    /**
     * {@inheritdoc}
     */
    protected $exception = WeComException::class;

    /**
     * Sends a message through the webhook.
     *
     * @param string   $url
     * @param Response $response
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws WeComException
     */
    public function send(string $url, Response $response)
    {
        $title = $this->settings->get('forum_title');

        $res = $this->request($url, [
            'msgtype' => 'markdown',
            'markdown' => $this->toArray($response),
        ]);

        if ($res->getStatusCode() == 302) {
            throw new WeComException($res, $url);
        }
    }

    /**
     * @param Response $response
     *
     * @return array
     */
    public function toArray(Response $response): array
    {
        $content = '';
        if ($response->author->exists) {
            $content .= '[' . $response->author->display_name . '](' . $response->getAuthorUrl() . ') ';
        }
        $content .= '[' . $response->title . '](' . $response->url . ")\n";

        $content .= $response->description;
    
        return [
            'content' => $content,
        ];
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    public static function isValidURL(string $url): bool
    {
        // allow any URL as multiple services support WeCom webhook payloads
        return true;
    }
}
