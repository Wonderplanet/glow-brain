using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class StageDataTranslator
    {
        public static MstQuestModel ToQuestModel(MstQuestData data, MstQuestI18nData i18nData)
        {
            return new MstQuestModel(
                new MasterDataId(data.Id),
                new QuestName(i18nData.Name),
                new QuestName(i18nData.CategoryName),
                new QuestFlavorText(i18nData.FlavorText),
                new QuestAssetKey(data.AssetKey),
                data.QuestType,
                string.IsNullOrEmpty(data.MstEventId)?MasterDataId.Empty: new MasterDataId(data.MstEventId),
                new UnlimitedCalculableDateTimeOffset(data.StartDate),
                new UnlimitedCalculableDateTimeOffset(data.EndDate),
                new SortOrder(data.SortOrder),
                new MasterDataId(data.QuestGroup),
                data.Difficulty
                );
        }
        public static MstStageModel ToStageModel(
            MstStageData mstStageData,
            MstStageI18nData i18nData,
            MstInGameData mstInGameData,
            MstInGameI18nData mstInGameI18nData,
            MstQuestData mstQuestData)
        {
            var bossCount = BossCount.Empty;
            if (mstInGameData.BossCount.HasValue)
            {
                bossCount = mstInGameData.BossCount.Value < 0
                    ? BossCount.Infinity
                    : new BossCount(mstInGameData.BossCount.Value);
            }

            var inGameConsumptionType = InGameConsumptionType.Stamina;
            if(mstQuestData is { QuestType: QuestType.Enhance })
            {
                inGameConsumptionType = InGameConsumptionType.ChallengeableCount;
            }

            return new MstStageModel(
                new MasterDataId(mstStageData.Id),
                new MasterDataId(mstStageData.MstQuestId),
                new MasterDataId(mstInGameData.Id),
                StageNumber.Create(mstStageData.StageNumber),
                new StageRecommendedLevel(mstStageData.RecommendedLevel),
                mstStageData.StartAt,
                mstStageData.EndAt,
                new StageName(i18nData.Name),
                new StageResultTips(mstInGameI18nData.ResultTips),
                new StageAssetKey(mstStageData.AssetKey),
                new AutoPlayerSequenceSetId(mstInGameData.MstAutoPlayerSequenceSetId),
                new BGMAssetKey(mstInGameData.BgmAssetKey),
                string.IsNullOrEmpty(mstInGameData.BossBgmAssetKey)
                    ? BGMAssetKey.Empty
                    : new BGMAssetKey(mstInGameData.BossBgmAssetKey),
                string.IsNullOrEmpty(mstInGameData.LoopBackgroundAssetKey)
                    ? KomaBackgroundAssetKey.Empty
                    : new KomaBackgroundAssetKey(mstInGameData.LoopBackgroundAssetKey),
                string.IsNullOrEmpty(mstInGameData.PlayerOutpostAssetKey)
                    ? OutpostAssetKey.Empty
                    : new OutpostAssetKey(mstInGameData.PlayerOutpostAssetKey),
                new MasterDataId(mstInGameData.MstPageId),
                new MasterDataId(mstInGameData.MstEnemyOutpostId),
                string.IsNullOrEmpty(mstInGameData.MstDefenseTargetId)
                    ? MasterDataId.Empty
                    : new MasterDataId(mstInGameData.MstDefenseTargetId),
                string.IsNullOrEmpty(mstInGameData.BossMstEnemyStageParameterId)
                    ? MasterDataId.Empty
                    :new MasterDataId(mstInGameData.BossMstEnemyStageParameterId),
                bossCount,
                new EnemyParameterCoef(mstInGameData.NormalEnemyHpCoef),
                new EnemyParameterCoef(mstInGameData.NormalEnemyAttackCoef),
                new EnemyParameterCoef(mstInGameData.NormalEnemySpeedCoef),
                new EnemyParameterCoef(mstInGameData.BossEnemyHpCoef),
                new EnemyParameterCoef(mstInGameData.BossEnemyAttackCoef),
                new EnemyParameterCoef(mstInGameData.BossEnemySpeedCoef),
                new MasterDataId(mstStageData.MstArtworkFragmentDropGroupId),
                new StageConsumeStamina(mstStageData.CostStamina),
                new SortOrder(mstStageData.SortOrder),
                string.IsNullOrEmpty(mstStageData.PrevMstStageId)
                    ? MasterDataId.Empty
                    : new MasterDataId(mstStageData.PrevMstStageId),
                new Coin(mstStageData.Coin),
                new Exp(mstStageData.Exp),
                string.IsNullOrEmpty(mstInGameI18nData.Description) ?
                    InGameDescription.Empty :
                    new InGameDescription(mstInGameI18nData.Description),
                inGameConsumptionType,
                mstStageData.AutoLapType,
                new StaminaBoostCount(mstStageData.MaxAutoLapCount)
                );
        }

    }
}
