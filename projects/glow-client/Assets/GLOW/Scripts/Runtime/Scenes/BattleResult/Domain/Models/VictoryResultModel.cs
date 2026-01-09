using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.AdventBattleResult.Domain.Model;
using GLOW.Scenes.ArtworkFragmentAcquisition.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.PvpBattleResult.Domain.Model;

namespace GLOW.Scenes.BattleResult.Domain.Models
{
    public record VictoryResultModel(
        UnitAssetKey PickupUnitAssetKey,
        IReadOnlyList<UserExpGainModel> UserExpGains,
        UserLevelUpEffectModel UserLevelUpEffect,
        IReadOnlyList<PlayerResourceModel> AcquiredPlayerResources,
        IReadOnlyList<IReadOnlyList<PlayerResourceModel>> AcquiredPlayerResourcesGroupedByStaminaRap,
        IReadOnlyList<UnreceivedRewardReasonType> UnreceivedRewardReasonTypes,
        IReadOnlyList<ArtworkFragmentAcquisitionModel> ArtworkFragmentAcquisitionModels,
        ResultScoreModel ResultScoreModel,
        ResultSpeedAttackModel ResultSpeedAttackModel,
        AdventBattleResultScoreModel AdventBattleResultScoreModel,
        PvpBattleResultPointModel PvpBattleResultPointModel,
        InGameType InGameType,
        RemainingTimeSpan RemainingEventCampaignTimeSpan,
        InGameRetryModel InGameRetryModel)
    {
        public static VictoryResultModel Empty { get; } = new VictoryResultModel(
            UnitAssetKey.Empty,
            new List<UserExpGainModel>(),
            UserLevelUpEffectModel.Empty,
            new List<PlayerResourceModel>(),
            new List<IReadOnlyList<PlayerResourceModel>>(),
            new List<UnreceivedRewardReasonType>(),
            new List<ArtworkFragmentAcquisitionModel>(),
            ResultScoreModel.Empty,
            ResultSpeedAttackModel.Empty,
            AdventBattleResultScoreModel.Empty,
            PvpBattleResultPointModel.Empty,
            InGameType.Normal,
            RemainingTimeSpan.Empty,
            InGameRetryModel.Empty);
    };
}
