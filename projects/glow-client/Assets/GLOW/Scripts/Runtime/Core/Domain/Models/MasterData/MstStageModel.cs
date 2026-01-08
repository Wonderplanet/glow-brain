using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.Models
{
    public record MstStageModel(
        MasterDataId Id,
        MasterDataId MstQuestId,
        MasterDataId MstInGameId,
        StageNumber StageNumber,
        StageRecommendedLevel RecommendedLevel,
        ObscuredDateTimeOffset StartAt,
        ObscuredDateTimeOffset EndAt,
        StageName Name,
        StageResultTips ResultTips,
        StageAssetKey StageAssetKey,
        AutoPlayerSequenceSetId MstAutoPlayerSequenceSetId,
        BGMAssetKey BGMAssetKey,
        BGMAssetKey BossBGMAssetKey,
        KomaBackgroundAssetKey LoopBackGroundAssetKey,
        OutpostAssetKey PlayerOutpostAssetKey,
        MasterDataId MstPageId,
        MasterDataId MstEnemyOutpostId,
        MasterDataId MstDefenseTargetId,
        MasterDataId BossMstEnemyStageParameterId,
        BossCount BossCount,
        EnemyParameterCoef MobEnemyHpCoef,
        EnemyParameterCoef MobEnemyAttackCoef,
        EnemyParameterCoef MobEnemySpeedCoef,
        EnemyParameterCoef BossEnemyHpCoef,
        EnemyParameterCoef BossEnemyAttackCoef,
        EnemyParameterCoef BossEnemySpeedCoef,
        MasterDataId MstArtworkFragmentDropGroupId,
        StageConsumeStamina StageConsumeStamina,
        SortOrder SortOrder,
        MasterDataId ReleaseRequiredMstStageId,
        Coin Coin,
        Exp Exp,
        InGameDescription InGameDescription,
        InGameConsumptionType InGameConsumptionType,
        AutoLapType? AutoLapType,
        StaminaBoostCount MaxStaminaBoostCount) : IMstInGameModel
    {
        public static MstStageModel Empty { get; } = new MstStageModel(
            MasterDataId.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            StageNumber.Empty,
            StageRecommendedLevel.Empty,
            DateTimeOffset.MinValue,
            DateTimeOffset.MaxValue,
            StageName.Empty,
            StageResultTips.Empty,
            StageAssetKey.Empty,
            AutoPlayerSequenceSetId.Empty,
            BGMAssetKey.Empty,
            BGMAssetKey.Empty,
            KomaBackgroundAssetKey.Empty,
            OutpostAssetKey.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            BossCount.Empty,
            EnemyParameterCoef.Empty,
            EnemyParameterCoef.Empty,
            EnemyParameterCoef.Empty,
            EnemyParameterCoef.Empty,
            EnemyParameterCoef.Empty,
            EnemyParameterCoef.Empty,
            MasterDataId.Empty,
            StageConsumeStamina.Empty,
            SortOrder.Zero,
            MasterDataId.Empty,
            Coin.Empty,
            Exp.Empty,
            InGameDescription.Empty,
            InGameConsumptionType.Stamina,
            null,
            StaminaBoostCount.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        InGameAssetKey IMstInGameModel.InGameAssetKey => StageAssetKey.ToInGameAssetKey();
        InGameName IMstInGameModel.InGameName => Name.ToInGameName();
        InGameNumber IMstInGameModel.InGameNumber => StageNumber.ToInGameStageNumber();
        EventBonusGroupId IMstInGameModel.EventBonusGroupId => EventBonusGroupId.Empty;
    }
}
