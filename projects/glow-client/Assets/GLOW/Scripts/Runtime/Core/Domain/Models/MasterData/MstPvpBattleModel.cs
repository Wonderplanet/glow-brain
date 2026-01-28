using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    /// <summary>
    /// Pvpの場面でMstDefenseTargetIdやMstAutoPlayerSequenceIdなど現状使用される想定の無いものがありますが、
    /// 今後何かしらギミックが追加された場合に備えて他のInGameModelと同じように定義しています。
    /// </summary>
    public record MstPvpBattleModel(
        MasterDataId Id,
        MasterDataId MstInGameId,
        AutoPlayerSequenceSetId MstAutoPlayerSequenceSetId,
        StageResultTips ResultTips,
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
        InGameDescription InGameDescription,
        PvpName PvpName,
        PvpDescription PvpDescription,
        InGameConsumptionType InGameConsumptionType) : IMstInGameModel
    {
        public static MstPvpBattleModel Empty { get; } = new MstPvpBattleModel(
            MasterDataId.Empty,
            MasterDataId.Empty,
            AutoPlayerSequenceSetId.Empty,
            StageResultTips.Empty,
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
            InGameDescription.Empty,
            PvpName.Empty,
            PvpDescription.Empty,
            InGameConsumptionType.Stamina);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        InGameAssetKey IMstInGameModel.InGameAssetKey => InGameAssetKey.Empty;
        InGameName IMstInGameModel.InGameName => new InGameName("ランクマッチ");
        InGameNumber IMstInGameModel.InGameNumber => InGameNumber.Empty;
        EventBonusGroupId IMstInGameModel.EventBonusGroupId => EventBonusGroupId.Empty;
        AutoPlayerSequenceSetId IMstInGameModel.MstAutoPlayerSequenceSetId => AutoPlayerSequenceSetId.Empty;
    }
}
