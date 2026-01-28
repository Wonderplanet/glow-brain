using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.AdventBattle;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.AdventBattle.Presentation.ViewModel
{
    public record AdventBattleTopViewModel(
        MasterDataId MstAdventBattleId,
        AdventBattleType BattleType,
        EventBonusGroupId EventBonusGroupId,
        AdventBattleChallengeCount ChallengeableCount,
        AdventBattleChallengeCount AdChallengeableCount,
        AdventBattleChallengeType AdventBattleChallengeType,
        AdventBattleScore TotalScore,
        AdventBattleScore RequiredLowerScore,
        AdventBattleScore MaxScore,
        AdventBattleRaidTotalScore RaidTotalScore,
        AdventBattleRaidTotalScore RequiredLowerRaidTotalScore,
        RankType CurrentRankType,
        AdventBattleScoreRankLevel CurrentScoreRankLevel,
        UnitImageAssetPath DisplayEnemyUnitFirst,
        UnitImageAssetPath DisplayEnemyUnitSecond,
        UnitImageAssetPath DisplayEnemyUnitThird,
        KomaBackgroundAssetPath KomaBackgroundAssetPath,
        IReadOnlyList<AdventBattleHighScoreRewardViewModel> HighScoreRewards,
        RemainingTimeSpan AdventBattleRemainingTimeSpan,
        PartyName PartyName,
        AdventBattleHighScoreGaugeViewModel HighScoreGaugeViewModel,
        ExistsSpecialRuleFlag ExistsSpecialRule,
        NotificationBadge MissionBadge,
        AdventBattleRankingCalculatingFlag CalculatingRankings,
        AdventBattleName AdventBattleName,
        AdventBattleBossDescription AdventBattleBossDescription,
        HeldAdSkipPassInfoViewModel HeldAdSkipPassInfoViewModel,
        List<CampaignViewModel> CampaignViewModels);
}
