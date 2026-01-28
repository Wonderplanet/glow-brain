using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators.AdventBattle
{
    public class MstAdventBattleModelTranslator
    {
        public static MstAdventBattleModel ToMstAdventBattleModel(
            MstAdventBattleData data,
            MstInGameData inGameData,
            MstInGameI18nData inGameI18NData,
            MstAdventBattleI18nData i18NData)
        {
            var bossCount = BossCount.Empty;
            if (inGameData.BossCount.HasValue)
            {
                bossCount = inGameData.BossCount.Value < 0
                    ? BossCount.Infinity
                    : new BossCount(inGameData.BossCount.Value);
            }

            return new MstAdventBattleModel(
                new MasterDataId(data.Id),
                string.IsNullOrEmpty(data.MstEventId) ? MasterDataId.Empty : new MasterDataId(data.MstEventId),
                new AdventBattleAssetKey(data.AssetKey),
                data.AdventBattleType,
                string.IsNullOrEmpty(inGameData.MstAutoPlayerSequenceSetId) ? AutoPlayerSequenceSetId.Empty : new AutoPlayerSequenceSetId(inGameData.MstAutoPlayerSequenceSetId),
                string.IsNullOrEmpty(data.EventBonusGroupId) ? EventBonusGroupId.Empty : new EventBonusGroupId(data.EventBonusGroupId),
                new AdventBattleChallengeCount(data.ChallengeableCount),
                new AdventBattleChallengeCount(data.AdChallengeableCount),
                string.IsNullOrEmpty(data.DisplayMstUnitId1) ? MasterDataId.Empty : new MasterDataId(data.DisplayMstUnitId1),
                string.IsNullOrEmpty(data.DisplayMstUnitId2) ? MasterDataId.Empty : new MasterDataId(data.DisplayMstUnitId2),
                string.IsNullOrEmpty(data.DisplayMstUnitId3) ? MasterDataId.Empty : new MasterDataId(data.DisplayMstUnitId3),
                new UserExp(data.Exp),
                new Coin(data.Coin),
                new AdventBattleStartDateTime(data.StartAt),
                new AdventBattleEndDateTime(data.EndAt),
                new BattlePoint(data.InitialBattlePoint),
                data.ScoreAdditionType,
                new DamageScoreAdditionalCoef(data.ScoreAdditionalCoef),
                new MasterDataId(inGameData.Id),
                new StageResultTips(inGameI18NData.ResultTips),
                new BGMAssetKey(inGameData.BgmAssetKey),
                string.IsNullOrEmpty(inGameData.BossBgmAssetKey)
                    ? BGMAssetKey.Empty
                    : new BGMAssetKey(inGameData.BossBgmAssetKey),
                new KomaBackgroundAssetKey(inGameData.LoopBackgroundAssetKey),
                string.IsNullOrEmpty(inGameData.PlayerOutpostAssetKey)
                    ? OutpostAssetKey.Empty
                    : new OutpostAssetKey(inGameData.PlayerOutpostAssetKey),
                new MasterDataId(inGameData.MstPageId),
                new MasterDataId(inGameData.MstEnemyOutpostId),
                string.IsNullOrEmpty(inGameData.MstDefenseTargetId)
                    ? MasterDataId.Empty
                    :new MasterDataId(inGameData.MstDefenseTargetId),
                string.IsNullOrEmpty(inGameData.BossMstEnemyStageParameterId)
                    ? MasterDataId.Empty
                    :new MasterDataId(inGameData.BossMstEnemyStageParameterId),
                bossCount,
                new EnemyParameterCoef(inGameData.NormalEnemyHpCoef),
                new EnemyParameterCoef(inGameData.NormalEnemyAttackCoef),
                new EnemyParameterCoef(inGameData.NormalEnemySpeedCoef),
                new EnemyParameterCoef(inGameData.BossEnemyHpCoef),
                new EnemyParameterCoef(inGameData.BossEnemyAttackCoef),
                new EnemyParameterCoef(inGameData.BossEnemySpeedCoef),
                string.IsNullOrEmpty(inGameI18NData.Description) ?
                    InGameDescription.Empty :
                    new InGameDescription(inGameI18NData.Description),
                string.IsNullOrEmpty(i18NData.Name) ?
                    AdventBattleName.Empty :
                    new AdventBattleName(i18NData.Name),
                string.IsNullOrEmpty(i18NData.BossDescription) ?
                    AdventBattleBossDescription.Empty :
                    new AdventBattleBossDescription(i18NData.BossDescription),
                InGameConsumptionType.ChallengeableCount
                );
        }

        public static MstAdventBattleRewardGroupModel ToMstAdventBattleRewardGroupModel(MstAdventBattleRewardGroupData groupData, IReadOnlyList<MstAdventBattleRewardData> rewardData)
        {
            return new MstAdventBattleRewardGroupModel(
                new MasterDataId(groupData.Id),
                new MasterDataId(groupData.MstAdventBattleId),
                rewardData.Select(ToMstAdventBattleRewardModel).ToList(),
                groupData.RewardCategory,
                new AdventBattleRewardCondition(groupData.ConditionValue));
        }

        public static MstAdventBattleScoreRankModel ToMstAdventBattleScoreRankModel(MstAdventBattleRankData data)
        {
            return new MstAdventBattleScoreRankModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstAdventBattleId),
                data.RankType,
                new AdventBattleScoreRankLevel(data.RankLevel),
                new AdventBattleScore(data.RequiredLowerScore));
        }

        static MstAdventBattleRewardModel ToMstAdventBattleRewardModel(MstAdventBattleRewardData data)
        {
            return new MstAdventBattleRewardModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstAdventBattleRewardGroupId),
                data.ResourceType,
                new MasterDataId(data.ResourceId),
                new ObscuredPlayerResourceAmount(data.ResourceAmount));
        }
    }
}
