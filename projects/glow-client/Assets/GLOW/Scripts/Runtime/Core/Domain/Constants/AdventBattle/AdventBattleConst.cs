namespace GLOW.Core.Domain.Constants.AdventBattle
{
    public static class AdventBattleConst
    {
        // 降臨バトルのランキング集計時間の設定がない場合のデフォルト時間
        public const int DefaultAdventBattleRankingAggregateHours = 48;

        // ランキング結果を表示する期間
        public const int RankingResultNotificationTermDays = 30;

        // 降臨バトルの最高ランク
        public const RankType AdventBattleMaxRankType = RankType.Master;
    }
}