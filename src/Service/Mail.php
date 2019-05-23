<?php

namespace App\Service;

use App\Entity\Mail\Template;
use App\Entity\Site;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityNotFoundException;

/**
 * Class Mail
 *
 * @package App\Service
 */
class Mail
{
    /**
     * @var Template
     */
    private $template;
    /**
     * @var ObjectManager
     */
    private $manager;
    /**
     * @var \Swift_Message
     */
    private $message;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var array
     */
    private $attributes = [
        'from' => 'sender',
        'to' => 'recipient',
        'replyTo' => 'replyTo',
        'bcc' => 'copyTo'
    ];
    /**
     * @var array
     */
    private $textAttributes = [
        'subject' => 'subject',
        'htmlBody' => 'htmlBody',
        'textBody' => 'textBody'
    ];
    /**
     * @var array
     */
    private $params = [];

    /**
     * Mail constructor.
     *
     * @param ObjectManager $manager
     * @param \Swift_Mailer $mailer
     */
    public function __construct(ObjectManager $manager, \Swift_Mailer $mailer)
    {
        $this->manager = $manager;

        // Initialize message
        $this->message = new \Swift_Message();

        // Set mailer
        $this->mailer = $mailer;
    }

    /**
     * @param array|null $params
     *
     * @return Mail
     */
    public function params(?array $params): self
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Load template from database and parse variables.
     *
     * @param string $code
     *
     * @return Mail
     * @throws EntityNotFoundException
     */
    public function template(string $code): self
    {
        $this->template = $this->manager->getRepository('App:Mail\Template')->findOneBy(['code' => $code]);

        if (!$this->template) {
            throw new EntityNotFoundException('Invalid `Template` code.');
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function send(): bool
    {
        $attributes = [];
        foreach ($this->attributes as $name => $attribute) {
            $attributes[$name] = $this->template->{'get' . ucfirst($attribute)}();
        }

        foreach ($this->textAttributes as $name => $attribute) {
            $attributes[$name] = $this->template->{'get' . ucfirst($attribute)}();
        }

        // Add site params
        foreach (['site', 'admin'] as $type) {
            $this->addSiteParams($type);
        }

        $renderer = new \Twig_Environment(new \Twig_Loader_Array($attributes), ['autoescape' => false]);

        foreach ($attributes as $name => &$value) {
            if ($value) {
                $value = $renderer->render($name, $this->params);
            }
        }

        // Parse E-Mail addresses
        foreach (['from', 'to', 'replyTo', 'bcc'] as $attribute) {
            $attributes[$attribute] = $this->parseAddresses($attributes[$attribute]);
        }

        // Set message fields and headers
        $this->compose($attributes);

        // Send message
        $result = $this->mailer->send($this->message);

        return $result > 0;
    }

    /**
     * @param array $attributes
     */
    protected function compose(array $attributes)
    {
        $htmlBody = $attributes['htmlBody'];
        $textBody = $attributes['textBody'];

        $this->message->setSubject($attributes['subject']);

        if ($htmlBody) {
            $this->message->setBody($htmlBody, 'text/html');

            if ($textBody) {
                $this->message->addPart($textBody, 'text/plain');
            }
        }
        else {
            $this->message->setBody($textBody, 'text/plain');
        }

        foreach ($this->attributes as $attribute => $value) {
            $methodName = 'set' . ucfirst($attribute);

            if ($attributes[$attribute] && method_exists($this->message, $methodName)) {
                $this->message->$methodName($attributes[$attribute]);
            }
        }
    }

    /**
     * @param string|null $addresses
     *
     * @return array
     */
    protected function parseAddresses(?string $addresses): array
    {
        $addresses = preg_split('/[,;]+/', $addresses, -1, PREG_SPLIT_NO_EMPTY);
        $result = [];

        foreach ($addresses as $address) {
            if (preg_match('/^(.+)\<(.+)\>$/', $address, $matches)) {
                $result[$matches[2]] = trim($matches[1]);
            }
            else {
                $result[] = $address;
            }
        }

        return $result;
    }

    /**
     * @param array $attributes
     * @param string $prefix
     */
    protected function setParams(array $attributes, string $prefix = ''): void
    {
        foreach ($attributes as $key => $value) {
            $this->params[strtoupper(($prefix ? ($prefix . '_') : '') . $key)] = $value;
        }
    }

    /**
     * @param string $type
     *
     * @return Site|null
     */
    protected function getSite(string $type): ?Site
    {
        $siteId = ($type === 'site' && $this->params['CLIENT_ID'])
            ? $this->params['CLIENT_ID']
            : 'ADMIN';

        $site = $this->manager
            ->getRepository('App:Site')
            ->findOneBy(['code' => strtoupper($siteId)]);

        if ($site && in_array($site, $this->template->getSites()->toArray())) {
            return $site;
        }

        return null;
    }

    /**
     * @param string $type
     */
    protected function addSiteParams(string $type): void
    {
        $site = $this->getSite($type);
        if ($site) {
            $siteAttributes = [
                'url' => $site->getUrl(),
                'email' => $site->getEmail(),
                'code' => $site->getCode()
            ];

            $this->setParams($siteAttributes, $type);
        }
    }
}