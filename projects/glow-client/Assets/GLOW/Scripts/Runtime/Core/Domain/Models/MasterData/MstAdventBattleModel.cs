using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstAdventBattleModel(
        MasterDataId Id,
        MasterDataId MstEventId,
        AdventBattleAssetKey AdventBattleAssetKey,
        AdventBattleType BattleType,
        AutoPlayerSequenceSetId MstAutoPlayerSequenceSetId,
        EventBonusGroupId EventBonusGroupId,
        AdventBattleChallengeCount ChallengeCount,
        AdventBattleChallengeCount AdChallengeCount,
        MasterDataId DisplayEnemyUnitIdFirst,
        MasterDataId DisplayEnemyUnitIdSecond,
        MasterDataId DisplayEnemyUnitIdThird,
        UserExp UserExp,
        Coin Coin,
        AdventBattleStartDateTime StartDateTime,
        AdventBattleEndDateTime EndDateTime,
        BattlePoint InitialBattlePoint,
        ScoreAdditionType ScoreAdditionType,
        DamageScoreAdditionalCoef DamageScoreAdditionCoef,
        MasterDataId MstInGameId,
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
        AdventBattleName AdventBattleName,
        AdventBattleBossDescription AdventBattleBossDescription,
        InGameConsumptionType InGameConsumptionType
    ): IMstInGameModel
    {
        public static MstAdventBattleModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            AdventBattleAssetKey.Empty,
            AdventBattleType.ScoreChallenge,
            AutoPlayerSequenceSetId.Empty,
            EventBonusGroupId.Empty,
            AdventBattleChallengeCount.Empty,
            AdventBattleChallengeCount.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            UserExp.Empty,
            Coin.Empty,
            AdventBattleStartDateTime.Empty,
            AdventBattleEndDateTime.Empty,
            BattlePoint.Empty,
            ScoreAdditionType.AllEnemiesAndOutPost,
            DamageScoreAdditionalCoef.Empty,
            MasterDataId.Empty,
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
            AdventBattleName.Empty,
            AdventBattleBossDescription.Empty,
            InGameConsumptionType.ChallengeableCount
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        InGameAssetKey IMstInGameModel.InGameAssetKey => AdventBattleAssetKey.ToInGameAssetKey();
        InGameName IMstInGameModel.InGameName => new InGameName("降臨バトル");
        InGameNumber IMstInGameModel.InGameNumber => InGameNumber.Empty;
        EventBonusGroupId IMstInGameModel.EventBonusGroupId => EventBonusGroupId;
    };
}
