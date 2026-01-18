using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Constants;
using WPFramework.Constants.MasterData;

namespace GLOW.Core.Data.DataStores.Cache
{
    internal sealed class MstI18nDataToCacheDataConverter : IMstDataToCacheDataConverter
    {
        readonly MstI18nData _data;

        public MstI18nDataToCacheDataConverter(MstI18nData data)
        {
            _data = data;
        }

        IEnumerable<MstCacheData> IMstDataToCacheDataConverter.Convert(Language language)
        {
            // NOTE: Language絞りが必要なものは外部から渡されてくる情報をもとにキャッシュする値を変更する
            var mstAbilityI18nData = _data.MstAbilityI18n.Where(mst => mst.Language == language).ToArray();
            var mstAttackI18nData = _data.MstAttackI18n.Where(mst => mst.Language == language).ToArray();
            var mstEnemyCharacterI18nData = _data.MstEnemyCharacterI18n.Where(mst => mst.Language == language).ToArray();
            var mstEmblemI18nData = _data.MstEmblemI18n.Where(mst => mst.Language == language).ToArray();
            var mstUnitI18nData = _data.MstUnitI18n.Where(mst => mst.Language == language).ToArray();
            var mstItemI18nData = _data.MstItemI18n.Where(mst => mst.Language == language).ToArray();
            var mstMissionAchievementI18nData = _data.MstMissionAchievementI18n.Where(mst => mst.Language == language).ToArray();
            var mstMissionDailyI18nData = _data.MstMissionDailyI18n.Where(mst => mst.Language == language).ToArray();
            var mstMissionWeeklyI18nData = _data.MstMissionWeeklyI18n.Where(mst => mst.Language == language).ToArray();
            var mstMissionBeginnerI18nData = _data.MstMissionBeginnerI18n.Where(mst => mst.Language == language).ToArray();
            var mstMissionBeginnerPromptPhraseI18nData = _data.MstMissionBeginnerPromptPhraseI18n.Where(mst => mst.Language == language).ToArray();
            var mstMissionEventI18nData = _data.MstMissionEventI18n.Where(mst => mst.Language == language).ToArray();
            var mstMissionLimitedTermI18nData = _data.MstMissionLimitedTermI18n.Where(mst => mst.Language == language).ToArray();
            var mstOutpostEnhancementI18nData = _data.MstOutpostEnhancementI18n.Where(mst => mst.Language == language).ToArray();
            var mstOutpostEnhancementLevelI18nData = _data.MstOutpostEnhancementLevelI18n.Where(mst => mst.Language == language).ToArray();
            var mstPackI18nData = _data.MstPackI18n.Where(mst => mst.Language == language).ToArray();
            var mstShopPassI18nData = _data.MstShopPassI18n.Where(mst => mst.Language == language).ToArray();
            var mstQuestI18nData = _data.MstQuestI18n.Where(mst => mst.Language == language).ToArray();
            var mstSeriesI18nData = _data.MstSeriesI18n.Where(mst => mst.Language == language).ToArray();
            var mstSpecialAttackI18nData = _data.MstSpecialAttackI18n.Where(mst => mst.Language == language).ToArray();
            var mstSpeechBalloonI18nData = _data.MstSpeechBalloonI18n.Where(mst => mst.Language == language).ToArray();
            var mstStageI18nData = _data.MstStageI18n.Where(mst => mst.Language == language).ToArray();
            var mstInGameI18nData = _data.MstInGameI18n.Where(mst => mst.Language == language).ToArray();
            var mstResultTipsI18nData = _data.MstResultTipI18n.Where(mst => mst.Language == language).ToArray();
            var mstStoreProductI18nData = _data.MstStoreProductI18n.Where(mst => mst.Language == language).ToArray();
            var mstArtworkI18nData = _data.MstArtworkI18n.Where(mst => mst.Language == language).ToArray();
            var mstArtworkFragmentI18nData = _data.MstArtworkFragmentI18n.Where(mst => mst.Language == language).ToArray();
            var mstEventI18nData = _data.MstEventI18n.Where(mst => mst.Language == language).ToArray();
            var mstEventDisplayUnitI18nData = _data.MstEventDisplayUnitI18n.Where(mst => mst.Language == language).ToArray();
            var mstTutorialTipI18nData = _data.MstTutorialTipI18n.Where(mst => mst.Language == language).ToArray();
            var mstAdventI18nData = _data.MstAdventBattleI18n.Where(mst => mst.Language == language).ToArray();
            var mstPvpI18nData = _data.MstPvpI18n.Where(mst => mst.Language == language).ToArray();
            var mstDummyUserI18nData = _data.MstDummyUserI18n.Where(mst => mst.Language == language).ToArray();
            var mstExchangeI18nData = _data.MstExchangeI18n.Where(mst => mst.Language == language).ToArray();
            var mstBoxGachaI18nData = _data.MstBoxGachaI18n.Where(mst => mst.Language == language).ToArray();

            return new[]
            {
                new MstCacheData(MasterType.MstI18n, typeof(MstAbilityI18nData), mstAbilityI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstAttackI18nData), mstAttackI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstEnemyCharacterI18nData), mstEnemyCharacterI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstEmblemI18nData), mstEmblemI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstUnitI18nData), mstUnitI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstItemI18nData), mstItemI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstMissionAchievementI18nData), mstMissionAchievementI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstMissionDailyI18nData), mstMissionDailyI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstMissionWeeklyI18nData), mstMissionWeeklyI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstMissionBeginnerI18nData), mstMissionBeginnerI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstMissionBeginnerPromptPhraseI18nData), mstMissionBeginnerPromptPhraseI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstMissionEventI18nData), mstMissionEventI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstMissionLimitedTermI18nData), mstMissionLimitedTermI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstOutpostEnhancementI18nData), mstOutpostEnhancementI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstOutpostEnhancementLevelI18nData), mstOutpostEnhancementLevelI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstPackI18nData), mstPackI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstShopPassI18nData), mstShopPassI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstQuestI18nData), mstQuestI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstSeriesI18nData), mstSeriesI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstSpecialAttackI18nData), mstSpecialAttackI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstSpeechBalloonI18nData), mstSpeechBalloonI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstStageI18nData), mstStageI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstInGameI18nData), mstInGameI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstResultTipI18nData), mstResultTipsI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstStoreProductI18nData), mstStoreProductI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstArtworkI18nData), mstArtworkI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstArtworkFragmentI18nData), mstArtworkFragmentI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstEventI18nData), mstEventI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstEventDisplayUnitI18nData), mstEventDisplayUnitI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstTutorialTipI18nData), mstTutorialTipI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstAdventBattleI18nData), mstAdventI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstPvpI18nData), mstPvpI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstDummyUserI18nData), mstDummyUserI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstExchangeI18nData), mstExchangeI18nData),
                new MstCacheData(MasterType.MstI18n, typeof(MstBoxGachaI18nData), mstBoxGachaI18nData),
            };
        }
    }
}
