<?php

declare(strict_types=1);

namespace App\Domain\Message\Constants;

class MessageConstant
{
    // ショップ

    public const SHOP_PASS_TITLE = 'の購入報酬をお送りします';
    public const SHOP_PASS_BODY =
    'の購入報酬です。
受け取りボタンを押してお受け取りください。
※毎日報酬は、翌日以降のログイン時にメールBOXへ送られます。';

    public const SHOP_PASS_DAILY_REWARD_TITLE = 'の毎日報酬をお送りします';
    public const SHOP_PASS_DAILY_REWARD_BODY =
    'の毎日報酬です。
受け取りボタンを押してお受け取りください。';

    // FIXME: i18nマスタから文言取得するように対応する

    // 降臨バトル

    public const ADVENT_BATTLE_TITLE = '「降臨バトル」報酬';
    public const ADVENT_BATTLE_BODY =
    'ご参加ありがとうございました！
「降臨バトル」の報酬をお送りします。';

    public const ADVENT_BATTLE_MAX_SCORE_TITLE = '「降臨バトル」報酬';
    public const ADVENT_BATTLE_MAX_SCORE_BODY =
    'ご参加ありがとうございました！
「降臨バトル」の報酬をお送りします。';

    // 降臨バトル報酬メッセージの開封期限
    public const ADVENT_BATTLE_REWARD_MESSAGE_EXPIRATION_DAYS = 30;

    // ジャンプ+

    public const JUMP_PLUS_TITLE = 'ジャンプ+連携報酬配布';
    public const JUMP_PLUS_BODY = 'ジャンプ+連携報酬をお送りします';

    // PvP

    public const PVP_RANK_REWARD_TITLE = '「ランクマッチ」ランク報酬';
    public const PVP_RANK_REWARD_BODY =
    'ご参加ありがとうございました！
「ランクマッチ」のランク報酬をお送りします。';

    public const PVP_RANKING_REWARD_TITLE = '「ランクマッチ」ランキング報酬';
    public const PVP_RANKING_REWARD_BODY =
    'ご参加ありがとうございました！
「ランクマッチ」のランキング報酬をお送りします。';

    public const PVP_REWARD_MESSAGE_EXPIRATION_DAYS = 30;

    // 未受け取り報酬

    public const REWARD_UNRECEIVED_TITLE = '未受け取り報酬配布';
    public const REWARD_UNRECEIVED_BODY = '未受け取りの報酬をお送りします';
}
