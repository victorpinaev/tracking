<?php

namespace common\modules\tracking\enums;

use common\traits\ClassConstantsTrait;

class EventEnum
{
    use ClassConstantsTrait;

    public const CASE_OPENED = 'case_opened'; // am, d
    public const CASE_FAVORITED = 'case_favorited'; // am
    public const CASE_UNFAVORITED = 'case_unfavorited'; // am

    public const BATTLE_FINISHED = 'battle_finished'; // am
    public const BATTLE_CREATED = 'battle_created'; // am
    public const BATTLE_JOINED = 'battle_joined'; // am
    public const BATTLE_LEFT = 'battle_left'; // am
    public const BATTLE_FINISHED_PER_USER = 'battle_finished_per_user'; // am, d

    public const SKIN_UPGRADED = 'skin_upgraded'; // fb, am, d
    public const SKIN_SOLD = 'skin_sold'; // am, d

    public const ACCOUNT_CREATED = 'account_created'; // am
    public const ACCOUNT_FUNDED = 'account_funded'; // fb, ga, owx, am, d
    public const PAYMENT_CHECKOUT_OPENED = 'payment_checkout_opened'; // am
    public const PAYMENT_ERROR = 'payment_error'; // am

    public const ITEM_WITHDRAWAL = 'user_completed_withdraw'; // ga, owx
    public const PROMOCODE_SUCCEEDED = 'promocode_succeeded'; // am
    public const TRADE_URL_ADDED = 'trade_url_added'; // am

    public const WITHDRAWAL_STARTED = 'withdrawal_started'; // am
    public const WITHDRAWAL_DECLINED = 'withdrawal_declined'; // am
    public const WITHDRAWAL_ERROR = 'withdrawal_error'; // am

    public const SKIN_RECEIVED = 'skin_received'; // am
    public const SKIN_EXCHANGE_COMPLETED = 'skin_exchange_completed'; // am

    public const PENDING_DROP_SUM_UPD = 'pending_drop_sum_upd'; // am
    public const USER_ROLE_CHANGED = 'user_role_changed'; // am
    public const USER_STATUS_CHANGED = 'user_status_changed'; // am
    public const USER_DELETED_BY_ADMIN = 'user_deleted_by_admin'; // am

    public const PRIZE_TAKEN = 'feast_progress_prize_taken'; // am
    public const LEVEL_ACHIEVED = 'feast_progress_level_achieved'; // am, d
    public const TICKET_SPENT = 'feast_progress_ticket_spent'; // d

    public const GIVEAWAY_CASE_RECEIVED = 'giveaway_case_received'; // am
    public const GIVEAWAY_CASE_PARTICIPATION = 'giveaway_case_participation'; // am

    public const LOGIN_SUCCEEDED = 'login_succeeded'; // am
    public const LOGOUT_SUCCEEDED = 'logout_succeeded'; // am

    public const ANTIFRAUD_POINTS_GAINED = 'antifraud_points_gained'; // am

    public const BALANCES_TOTAL = 'balances_total'; // am

    public const EMAIL_ADDED = 'email_added'; // am
    public const EMAIL_CONFIRMED = 'email_confirmed'; // am

    public const LEADERBOARD_TIER_CHANGED = 'leaderboard_tier_changed'; // am
    public const LEADERBOARD_PRIZE_TAKEN = 'leaderboard_prize_taken'; // am

    public const MISSION_PRIZE_TAKEN = 'mission_completed_and_prize_taken'; // am

    public const COMPONENT_STATUS_UPDATE = 'component_status_update'; // c

    public const HYPE_DROP_CREATED = 'hype_drop_created'; // c

    public const LEVEL_UP = 'level_up'; // c, am
    public const LEVEL_CALCULATION_REQUESTED = 'level_calculation_requested'; // am

    // NGR Events
    public const GGR_BATTLES = 'ggr_battles'; // am
    public const GGR_CASES = 'ggr_cases'; // am
    public const GGR_UPGRADES = 'ggr_upgrades'; // am
    public const PROFIT_WITHDRAWALS = 'profit_withdrawals'; // am
    public const BONUS_COMMISSION_COMPENSATION = 'bonus_commission_compensation'; // am
    public const BONUS_PROMOCODE_PAYMENT_BONUS = 'bonus_promocode_payment_bonus'; // am
    public const BONUS_PROMOCODE_MONEY = 'bonus_promocode_money'; // am
    public const BONUS_GIVEAWAY_CASE = 'bonus_giveaway_case'; // am
    public const BONUS_FEAST_CASE = 'bonus_feast_case'; // am
    public const BONUS_FEAST_MONEY = 'bonus_feast_money'; // am
    public const BONUS_CASES_DISCOUNT = 'bonus_cases_discount'; // am
    public const BONUS_ADMIN = 'bonus_admin'; // am

    public static function initialName(string $finalName): string
    {
        return self::fetchValueFromArray($finalName, self::processMap());
    }

    public static function initialNames(): array
    {
        return \array_unique(\array_values(self::processMap()));
    }

    /**
     * Events which cannot obtain an amplitude session ID are listed here.
     * For example, it's fired from console commands or queue jobs without interaction with UI.
     *
     * @return string[]
     */
    public static function withoutAmpSession(): array
    {
        return [
            self::BATTLE_FINISHED,
            self::ANTIFRAUD_POINTS_GAINED,
        ];
    }

    /**
     * Format: finishing event => starting (initial) event.
     *
     * @return string[]
     */
    private static function processMap(): array
    {
        return [
            self::ACCOUNT_FUNDED           => self::PAYMENT_CHECKOUT_OPENED,
            self::PAYMENT_ERROR            => self::PAYMENT_CHECKOUT_OPENED,
            self::BATTLE_FINISHED_PER_USER => self::BATTLE_JOINED,
            self::BATTLE_LEFT              => self::BATTLE_JOINED,
            self::WITHDRAWAL_ERROR         => self::WITHDRAWAL_STARTED,
            self::ITEM_WITHDRAWAL          => self::WITHDRAWAL_STARTED,
        ];
    }
}
