using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators.Pvp
{
    public class MstPvpBattleModelTranslator
    {
        public static MstPvpBattleModel ToMstPvpBattleModel(
            MstPvpData data,
            MstPvpI18nData i18NData,
            MstInGameData inGameData,
            MstInGameI18nData inGameI18NData)
        {
            var bossCount = BossCount.Empty;
            if (inGameData.BossCount.HasValue)
            {
                bossCount = inGameData.BossCount.Value < 0
                    ? BossCount.Infinity
                    : new BossCount(inGameData.BossCount.Value);
            }

            return new MstPvpBattleModel(
                new MasterDataId(data.Id),
                new MasterDataId(inGameData.Id),
                string.IsNullOrEmpty(inGameData.MstAutoPlayerSequenceSetId)
                    ? AutoPlayerSequenceSetId.Empty
                    : new AutoPlayerSequenceSetId(inGameData.MstAutoPlayerSequenceSetId),
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
                    PvpName.Empty :
                    new PvpName(i18NData.Name),
                string.IsNullOrEmpty(i18NData.Description) ?
                    PvpDescription.Empty :
                    new PvpDescription(i18NData.Description),
                InGameConsumptionType.ChallengeableCount);
        }
    }
}
