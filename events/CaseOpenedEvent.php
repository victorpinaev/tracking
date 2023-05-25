<?php

namespace common\modules\tracking\events;

use common\enums\SocketMessageEnum;
use common\helpers\MoneyHelper;
use common\modules\tracking\dtos\CentrifugoMessageDTO;
use common\modules\tracking\enums\DomainEventEnum;
use common\modules\tracking\enums\EventEnum;
use common\modules\tracking\events\interfaces\AmplitudeEventInterface;
use common\modules\tracking\events\interfaces\CentrifugoEventInterface;
use common\modules\tracking\events\interfaces\DomainEventInterface;
use domain\case\enums\CaseTypeEnum;
use domain\case\models\Cases;
use domain\user\models\User;
use frontend\models\UsersDrop;
use yii\base\BaseObject;

class CaseOpenedEvent extends BaseObject implements
    AmplitudeEventInterface,
    CentrifugoEventInterface,
    DomainEventInterface
{
    public Cases $case;
    public UsersDrop $drop;
    public int $userPrice;
    public int $bulk;

    public function getUser(): User
    {
        return $this->drop->user;
    }

    public function getInternalName(): string
    {
        return EventEnum::CASE_OPENED;
    }

    public function getExternalName(): string
    {
        return DomainEventEnum::CASE_OPENED;
    }

    public function toAmplitude(): array
    {
        $eventProperties = [
            'case_id'         => $this->case->id . '_' . $this->case->name,
            'case_main_price' => MoneyHelper::convertFromCents($this->drop->caseOpen->generation->case_price),
            'case_user_price' => MoneyHelper::convertFromCents($this->userPrice),
            'drop_id'         => $this->drop->id,
            'drop_name'       => $this->drop->item->market_hash_name,
            'drop_price'      => MoneyHelper::convertFromCents($this->drop->price),
            'case_bulk'       => $this->bulk,
            'point_amount'    => $this->case->feastCase->points ?? 0,
            'case_type'       => $this->case->mainSection->name ?? null,
            'opening_type'    => $this->case->type,
        ];

        return [
            'event_type'       => 'case_opened',
            'event_properties' => $eventProperties,
        ];
    }

    public function toDomain(): array
    {
        $sections = [];

        if ($this->case->mainSection) {
            $sections[] = [
                'id' => $this->case->mainSection->id,
            ];
        }

        return [
            'player_id' => $this->getUser()->id,
            'case_id'   => $this->case->id,
            'price'     => $this->drop->caseOpen->generation->case_price,

            'player' => [
                'id' => $this->getUser()->id,
            ],
            'case' => [
                'id'    => $this->case->id,
                'price' => $this->drop->caseOpen->generation->case_price,
                'type'  => $this->case->type,
            ],
            'drop' => [
                'id'    => $this->drop->id,
                'price' => $this->drop->price,
            ],
            'sections' => $sections,
        ];
    }

    public function toCentrifugo(): array
    {
        $messages = [];

        // For limited cases only.
        if ((CaseTypeEnum::QUANTITY_LIMITED === $this->case->type) && $this->case->limitedQuantity) {
            $newQuantity = $this->case->limitedQuantity->current_quantity - $this->bulk;

            $messages[] = new CentrifugoMessageDTO([
                'name' => SocketMessageEnum::CASE_UPDATE,
                'data' => [
                    'caseId'          => $this->case->id,
                    'currentQuantity' => $newQuantity,
                ],
            ]);

            // The actual decrease in 'current_quantity' occurs after this event, at the controller level.
            // @see \domain\case\actions\CaseOpenAction::run()
        }

        return $messages;
    }
}
