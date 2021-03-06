<?php

namespace frontwise\monitor404\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use frontwise\monitor404\elements\db\Web404Query;
use frontwise\monitor404\elements\Actions\DeleteWeb404Action;

class Web404 extends Element
{
    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $remoteIP;

    /**
     * @var string
     */
    public $userAgent;

    /**
     * @var sting
     */
    public $message;

    /**
     * @var string
     */
    public $filePath;

    /**
     * @var int
     */
    public $fileLine;

    /**
     * @inheritdoc
     */
    public static function refHandle()
    {
        return 'web404';
    }

    /**
     * @return bool
     */
    public static function isLocalized(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public static function hasContent(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public static function hasTitles(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getIsEditable(): bool
    {
        return false;
    }

    public function getSupportedSites(): array
    {
        return Craft::$app->sites->getAllSiteIds();
    }

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->url;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @inheritDoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key'      => '*',
                'label'    => Craft::t('monitor404', 'All 404 requests'),
                'criteria' => [],
            ],
        ];

        if (Craft::$app->getIsMultiSite()) {
            foreach (Craft::$app->sites->getAllGroups() as $group) {
                $sources[] = ['heading' => $group->name];

                foreach ($group->getSites() as $site) {
                    $sources[] = [
                        'key' => $site->id,
                        'label' => $site->name,
                    ];
                }
            }
        }

        return $sources;
    }

    public function afterSave(bool $isNew)
    {
        if ($isNew) {
            Craft::$app->db->createCommand()
                ->insert('{{%frontwise_web_404s}}', [
                    'id' => $this->id,
                    'url' => $this->url,
                    'remoteIP' => $this->remoteIP,
                    'message' => $this->message,
                    'userAgent' => $this->userAgent,
                    'filePath' => $this->filePath,
                    'fileLine' => $this->fileLine,
                ])
                ->execute();
        } else {
            // No updating/editing of requests
        }

        parent::afterSave($isNew);
    }

    public static function find(): ElementQueryInterface
    {
        return new Web404Query(static::class);
    }

    /**
     * @inheritdoc
     */
    protected static function defineSearchableAttributes(): array
    {
        return ['url', 'remoteIP'];
    }

    protected static function defineActions(string $source = null): array
    {
        return [
            Craft::$app->elements->createAction(
                [
                    'type'                => DeleteWeb404Action::class,
                    'confirmationMessage' => Craft::t('monitor404', 'Are you sure you want to delete the selected 404 request?'),
                    'successMessage'      => Craft::t('monitor404', 'Request deleted.'),
                ]
            ),
        ];
    }

    protected static function defineSortOptions(): array
    {
        return [
            'elements.dateCreated' => Craft::t('app', 'Date Created'),
            'url' => Craft::t('monitor404', 'URL'),
            'remoteIP' => Craft::t('monitor404', 'IP Address'),
            'userAgent' => Craft::t('monitor404', 'User Agent'),
            'message' => Craft::t('monitor404', 'Message'),
            'filePath' => Craft::t('monitor404', 'File'),
        ];
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'url' => Craft::t('monitor404', 'URL'),
            'dateCreated' => Craft::t('app', 'Date created'),
            'remoteIP' => Craft::t('monitor404', 'IP Address'),
            'userAgent' => Craft::t('monitor404', 'User Agent'),
            'message' => Craft::t('monitor404', 'Message'),
            'filePath' => Craft::t('monitor404', 'File'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'filePath':
                return $this->filePath . ':' . $this->fileLine;
        }

        return parent::tableAttributeHtml($attribute);
    }

}
