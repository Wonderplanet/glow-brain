using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.Data;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.ManualGenerated;
using GLOW.Core.Data.Translators;
using GLOW.Core.Data.Translators.AdventBattle;
using GLOW.Core.Data.Translators.Pvp;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Event;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Exceptions;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;
using WonderPlanet.UnityStandard.Extension;
using WPFramework.Constants.MasterData;
using WPFramework.Domain.Modules;
using WPFramework.Domain.Repositories;
using WPFramework.Modules.Log;
using Zenject;
using MstPvpModel = GLOW.Core.Domain.Models.Pvp.MstPvpModel;

namespace GLOW.Core.Data.Repositories
{
    public class MasterDataRepository : BaseMstRepository,
        IMasterDataManagement,
        IMstDataRepository,
        IMstStageDataRepository,
        IMstQuestDataRepository,
        IMstSeriesDataRepository,
        IMstStageEnhanceRewardParamDataRepository,
        IMstStageRewardDataRepository,
        IMstStageEventRewardDataRepository,
        IMstStageEndConditionDataRepository,
        IMstShopProductDataRepository,
        IMstPageDataRepository,
        IMstCharacterDataRepository,
        IMstEnemyCharacterDataRepository,
        IMstUnitRoleBonusDataRepository,
        IMstUnitLevelUpRepository,
        IMstUnitRankUpRepository,
        IMstUnitSpecificRankUpRepository,
        IMstUnitRankCoefficientRepository,
        IMstUnitGradeUpRepository,
        IMstUnitGradeCoefficientRepository,
        IMstEnemyOutpostDataRepository,
        IMstOutpostEnhanceDataRepository,
        IMstItemDataRepository,
        IMstFragmentBoxGroupDataRepository,
        IMstFragmentBoxDataRepository,
        IMstItemTransitionDataRepository,
        IMstUserLevelDataRepository,
        IMstIdleIncentiveRepository,
        IMstMissionDataRepository,
        IMstConfigRepository,
        IMstArtworkDataRepository,
        IMstArtworkFragmentDataRepository,
        IMstEmblemRepository,
        IMstMangaAnimationDataRepository,
        IMstUnitEncyclopediaRewardDataRepository,
        IMstUnitEncyclopediaEffectDataRepository,
        IOprGachaRepository,
        IOprGachaUpperRepository,
        IOprGachaUseResourceRepository,
        IMstEventDataRepository,
        IMstResultTipsDataRepository,
        IMstStageEventSettingDataRepository,
        IMstInGameSpecialRuleDataRepository,
        IMstInGameSpecialRuleUnitStatusDataRepository,
        IMstStageClearTimeRewardRepository,
        IMstAbilityDescriptionDataRepository,
        IMstPartyUnitCountDataRepository,
        IMstEventBonusUnitDataRepository,
        IMstQuestEventBonusScheduleDataRepository,
        IMstQuestBonusUnitRepository,
        IMstAdventBattleDataRepository,
        IMstHomeBannerRepository,
        IMstItemRarityTradeRepository,
        IMstAutoPlayerSequenceRepository,
        IMstDefenseTargetDataRepository,
        IMstInGameGimmickObjectDataRepository,
        IMstTutorialRepository,
        IOprCampaignRepository,
        IMstPvpDataRepository,
        IMstExchangeShopDataRepository
    {
        [Inject] IMstDataLocalDataStore LocalDataStore { get; }
        [Inject] IMstDataDataStore DataStore { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }

        Dictionary<MasterDataId, MstEnemyStageParameterModel> _mstEnemyStageParameterModelDictionary = new ();

        async UniTask IMasterDataManagement.Load(
            CancellationToken cancellationToken,
            MasterType masterType,
            string masterPath,
            string hash)
        {
            // ローカルMstデータの読み込み
            if (masterType == MasterType.Mst)
            {
                await LocalDataStore.Load(cancellationToken);
            }

            var name = Path.GetFileName(masterPath);
            // TODO: 多言語対応するときはLanguage.jaをCurrentLanguageを変更する必要あり
            await DataStore.Load(cancellationToken, masterType, name, hash, Language.ja);

            ClearCache();
        }

        void IMasterDataManagement.Save(MasterType masterType, string name, byte[] data)
        {
            DataStore.Save(name, masterType, data);
        }

        UniTask<bool> IMasterDataManagement.Validate(
            CancellationToken cancellationToken,
            MasterType masterType,
            string name,
            string hash)
        {
            var result = DataStore.Validate(masterType, name);

            ApplicationLog.Log(
                nameof(IMasterDataManagement),
                ZString.Format("Validate: name: {0}, hash: {1}, isValid: {2}", name, hash, result));

            return UniTask.FromResult(result);
        }

        void IMasterDataManagement.DeleteAll(MasterType masterType)
        {
            DataStore.DeleteAll(masterType);
            ClearCache();
        }

        #region IMstStageDataRepository
        MstStageModel IMstStageDataRepository.GetMstStageFirstOrDefault(MasterDataId id)
        {
            var target = GetOrCreateModelCache(CreateMstStageModels).FirstOrDefault(m => m.Id == id);
            return target ?? MstStageModel.Empty;
        }

        MstStageModel IMstStageDataRepository.GetMstStage(MasterDataId id)
        {
            try
            {
                return GetOrCreateModelCache(CreateMstStageModels).First(m => m.Id == id);
            }
            catch(MasterDataCreateModelFailedException e)
            {
                throw;
            }
            catch (Exception e)
            {
                var errorMessage = $"MstStageに存在しないIdを指定しました。\n該当のデータが無いか生成に失敗した可能性があります:{id}。\n";
                errorMessage += CheckCreateMstStageModelsError();
                throw new MasterDataNotFoundException(errorMessage, e);
            }
        }

        IReadOnlyList<MstStageModel> IMstStageDataRepository.GetMstStages()
        {
            return GetOrCreateModelCache(CreateMstStageModels).ToList();
        }

        IReadOnlyList<MstStageModel> IMstStageDataRepository.GetMstStagesFromMstQuestId(MasterDataId mstQuestId)
        {
            return GetOrCreateModelCache(CreateMstStageModels).Where(m => m.MstQuestId == mstQuestId).ToList();
        }

        IEnumerable<MstStageModel> CreateMstStageModels()
        {
            try
            {
                var datas = DataStore.Get<MstStageData>();
                var i18ns = DataStore.Get<MstStageI18nData>();
                var inGameDatas = DataStore.Get<MstInGameData>();
                var inGameI18nDatas = DataStore.Get<MstInGameI18nData>();
                var mstQuestDatas = DataStore.Get<MstQuestData>();

                return datas
                    .Join(
                        i18ns,
                        stage => stage.Id,
                        i18n => i18n.MstStageId,
                        (data, i18n) => (data, i18n))
                    .Join(
                        inGameDatas,
                        stages => stages.data.MstInGameId,
                        inGame => inGame.Id,
                        (stage, inGameData) => (stage.data, stage.i18n, inGameData))
                    .Join(
                        inGameI18nDatas,
                        d => d.inGameData.Id,
                        i => i.MstInGameId,
                        (d, inGameI18n) => (d.data, d.i18n, d.inGameData, inGameI18n))
                    .GroupJoin(
                        mstQuestDatas,
                        stageAndInGame => stageAndInGame.data.MstQuestId,
                        quest => quest.Id,
                        (stageAndInGame, quest) => (
                            stageAndInGame.data,
                            stageAndInGame.i18n,
                            stageAndInGame.inGameData,
                            stageAndInGame.inGameI18n,
                            quest: quest.FirstOrDefault()))
                    .Select(stageAndInGame =>
                        StageDataTranslator.ToStageModel(
                            stageAndInGame.data,
                            stageAndInGame.i18n,
                            stageAndInGame.inGameData,
                            stageAndInGame.inGameI18n,
                            stageAndInGame.quest));
            }
            catch (Exception e)
            {
                var errorMessage = "CreateMstStageModelsにてエラーが発生しました。\n" + CheckCreateMstStageModelsError();
                throw new MasterDataCreateModelFailedException(errorMessage, e);
            }
        }

        string CheckCreateMstStageModelsError()
        {
            var errorMessage = string.Empty;
            var mstStageDatas = DataStore.Get<MstStageData>().ToList();
            var mstStageI18nDatas = DataStore.Get<MstStageI18nData>();
            var mstInGameDatas = DataStore.Get<MstInGameData>().ToList();
            var mstInGameI18nDatas = DataStore.Get<MstInGameI18nData>().ToList();

            var missingStageI18nIds = mstStageDatas
                .Where(stage => mstStageI18nDatas.All(i18n => i18n.MstStageId != stage.Id))
                .Select(stage => stage.Id)
                .ToList();
            if (missingStageI18nIds.Any())
            {
                errorMessage += $"MstStage：MstStageI18nに該当のStageIDが存在していません: {string.Join(", ", missingStageI18nIds)}\n";
            }

            var missingInGameIds = new List<string>();
            var missingInGameI18nIds = new List<string>();
            foreach (var stage in mstStageDatas)
            {
                var inGameData = mstInGameDatas.FirstOrDefault(inGame => inGame.Id == stage.MstInGameId);
                if (inGameData == null)
                {
                    missingInGameIds.Add(ZString.Format("{0}:{1}", stage.Id, stage.MstInGameId));
                    continue;
                }

                var inGameI18nData = mstInGameI18nDatas.FirstOrDefault(i18n => i18n.MstInGameId == inGameData.Id);
                if (inGameI18nData == null)
                {
                    missingInGameI18nIds.Add(ZString.Format("{0}:{1}", stage.Id, inGameData.Id));
                }
            }

            if (missingInGameIds.Any())
            {
                errorMessage += $"MstStage：MstInGameに該当のIdが存在していません: {string.Join(", ", missingInGameIds)}\n";
            }

            if (missingInGameI18nIds.Any())
            {
                errorMessage += $"MstInGame：MstInGameI18nに該当のMstInGameIdが存在していません: {string.Join(", ", missingInGameI18nIds)}\n";
            }

            Debug.LogError(errorMessage);
            return errorMessage;
        }

        # endregion

        # region IMstQuestDataRepository
        IReadOnlyList<MstQuestModel> IMstQuestDataRepository.GetMstQuestModels()
        {
            return GetOrCreateModelCache(CreateMstQuestModels).ToList();
        }

        IReadOnlyList<MstQuestModel> IMstQuestDataRepository.GetMstQuestModelsFromEvent(MasterDataId mstEventId)
        {
            return GetOrCreateModelCache(CreateMstQuestModels)
                .Where(s => s.MstEventId == mstEventId)
                .ToList();
        }

        IReadOnlyList<MstQuestModel> IMstQuestDataRepository.GetMstQuestModelsByQuestGroup(MasterDataId mstQuestGroupId)
        {
            return GetOrCreateModelCache(CreateMstQuestModels)
                .Where(s => s.GroupId == mstQuestGroupId)
                .ToList();
        }

        MstQuestModel IMstQuestDataRepository.GetMstQuestModel(MasterDataId id)
        {
            try
            {
                return GetOrCreateModelCache(CreateMstQuestModels).First(m => m.Id == id);
            }
            catch(MasterDataCreateModelFailedException e)
            {
                throw;
            }
            catch (Exception e)
            {
                var errorMessage = $"MstQuestに存在しないIdを指定しました\n該当のデータが無いか生成に失敗した可能性があります:{id}。\n";
                errorMessage += CheckCreateMstQuestModelsError();
                throw new MasterDataNotFoundException(errorMessage, e);
            }
        }

        MstQuestModel IMstQuestDataRepository.GetMstQuestModelFirstOrDefault(MasterDataId id)
        {
            var target = GetOrCreateModelCache(CreateMstQuestModels).FirstOrDefault(m => m.Id == id);
            return target ?? MstQuestModel.Empty;
        }

        IReadOnlyList<MstQuestModel> CreateMstQuestModels()
        {
            try
            {
                var data = DataStore.Get<MstQuestData>();
                var i18n = DataStore.Get<MstQuestI18nData>();

                return data
                    .Join(i18n, d => d.Id, i => i.MstQuestId, (d, i) => (d, i))
                    .Select(dataAndI18n => StageDataTranslator.ToQuestModel(dataAndI18n.d, dataAndI18n.i))
                    .ToList();
            }
            catch (Exception e)
            {
                var errorMessage = $"CreateMstQuestModelsにてエラーが発生しました。\n" + CheckCreateMstQuestModelsError();
                throw new MasterDataCreateModelFailedException(errorMessage, e);
            }
        }

        string CheckCreateMstQuestModelsError()
        {
            var errorMessage = string.Empty;
            var mstQuestDatas = DataStore.Get<MstQuestData>();
            var mstQuestI18nDatas = DataStore.Get<MstQuestI18nData>();

            var missingQuestI18nIds = mstQuestDatas
                .Where(quest => mstQuestI18nDatas.All(i18n => i18n.MstQuestId != quest.Id))
                .Select(quest => quest.Id)
                .ToList();
            if (missingQuestI18nIds.Any())
            {
                errorMessage += $"MstQuest：MstQuestI18nに該当のMstQuestIDが存在していません: {string.Join(", ", missingQuestI18nIds)}\n";
            }

            Debug.LogError(errorMessage);
            return errorMessage;
        }

        IReadOnlyList<MstEventDisplayUnitModel> IMstQuestDataRepository.GetEventDisplayUnits()
        {
            return GetOrCreateModelCache(CreateEventDisplayUnits).ToList();
        }

        IReadOnlyList<MstEventDisplayUnitModel> CreateEventDisplayUnits()
        {
            try
            {
                var i18ns = DataStore.Get<MstEventDisplayUnitI18nData>();
                return DataStore.Get<MstEventDisplayUnitData>()
                    .Join(i18ns, d => d.Id, i => i.MstEventDisplayUnitId, (d, i) => (d, i))
                    .Select(dAndI => MstEventDisplayUnitTranslator.Translate(dAndI.d, dAndI.i))
                    .ToList();
            }
            catch (Exception e)
            {
                var errorMessage = $"CreateEventDisplayUnitsにてエラーが発生しました。\n" + CheckCreateEventDisplayUnitsError();
                throw new MasterDataCreateModelFailedException(errorMessage, e);
            }
        }

        string CheckCreateEventDisplayUnitsError()
        {
            var errorMessage = string.Empty;
            var mstEventDisplayUnitDatas = DataStore.Get<MstEventDisplayUnitData>();
            var mstEventDisplayUnitI18nDatas = DataStore.Get<MstEventDisplayUnitI18nData>();

            var missingEventDisplayUnitI18nIds = mstEventDisplayUnitDatas
                .Where(eventDisplayUnit =>
                    mstEventDisplayUnitI18nDatas.All(i18n => i18n.MstEventDisplayUnitId != eventDisplayUnit.Id))
                .Select(eventDisplayUnit => eventDisplayUnit.Id)
                .ToList();
            if (missingEventDisplayUnitI18nIds.Any())
            {
                errorMessage += $"MstEventDisplayUnit：MstEventDisplayUnitI18nに該当のMstEventDisplayUnitIDが存在していません: {string.Join(", ", missingEventDisplayUnitI18nIds)}\n";
            }

            Debug.LogError(errorMessage);
            return errorMessage;
        }

        # endregion

        # region IMstSeriesDataRepository
        IReadOnlyList<MstSeriesModel> IMstSeriesDataRepository.GetMstSeriesModels()
        {
            return GetOrCreateModelCache(CreateMstSeriesModels).ToList();
        }

        MstSeriesModel IMstSeriesDataRepository.GetMstSeriesModel(MasterDataId id)
        {
            try
            {
                return GetOrCreateModelCache(CreateMstSeriesModels)
                    .First(mst => mst.Id == id);
            }
            catch(MasterDataCreateModelFailedException e)
            {
                throw;
            }
            catch (Exception e)
            {
                var errorMessage = $"MstSeriesに存在しないIdを指定しました\n該当のデータが無いか生成に失敗した可能性があります:{id}。\n";
                errorMessage += CheckCreateMstSeriesModelsError();
                throw new MasterDataNotFoundException(errorMessage, e);
            }
        }

        IReadOnlyList<MstSeriesModel> CreateMstSeriesModels()
        {
            try
            {
                var i18ns = DataStore.Get<MstSeriesI18nData>();
                return DataStore.Get<MstSeriesData>()
                    .Join(i18ns, d => d.Id, i => i.MstSeriesId, (d, i) => (d, i))
                    .Select(dataAndI18n => MstSeriesDataTranslator.ToModel(dataAndI18n.d, dataAndI18n.i))
                    .ToList();
            }
            catch (Exception e)
            {
                var errorMessage = $"CreateMstSeriesModelsにてエラーが発生しました。\n" + CheckCreateMstSeriesModelsError();
                throw new MasterDataCreateModelFailedException(errorMessage, e);
            }
        }

        string CheckCreateMstSeriesModelsError()
        {
            var errorMessage = string.Empty;
            var mstSeriesDatas = DataStore.Get<MstSeriesData>();
            var mstSeriesI18nDatas = DataStore.Get<MstSeriesI18nData>();

            var missingSeriesI18nIds = mstSeriesDatas
                .Where(series => mstSeriesI18nDatas.All(i18n => i18n.MstSeriesId != series.Id))
                .Select(series => series.Id)
                .ToList();
            if (missingSeriesI18nIds.Any())
            {
                errorMessage += $"MstSeries：MstSeriesI18nに該当のMstSeriesIDが存在していません: {string.Join(", ", missingSeriesI18nIds)}\n";
            }

            Debug.LogError(errorMessage);
            return errorMessage;
        }

        #endregion

        #region IMstPageDataRepository
        MstPageModel IMstPageDataRepository.GetPage(MasterDataId id)
        {
            try
            {
                return GetOrCreateModelCache(CreateMstPageModels).First(m => m.MstPageId == id);
            }
            catch(MasterDataCreateModelFailedException e)
            {
                throw;
            }
            catch (Exception e)
            {
                var errorMessage = $"MstPageに存在しないIdを指定しました\n該当のデータが無いか生成に失敗した可能性があります:{id}。\n";
                errorMessage += CheckCreateMstPageModelsError();
                throw new MasterDataNotFoundException(errorMessage, e);
            }
        }

        IReadOnlyList<MstPageModel> CreateMstPageModels()
        {
            try
            {
                var komaData = DataStore.Get<MstKomaLineData>();
                return DataStore.Get<MstPageData>()
                    .GroupJoin(komaData, page => page.Id, koma => koma.MstPageId, (page, koma) => (page, koma))
                    .Select(pageAndKoma => PageDataTranslator.ToPageModel(pageAndKoma.page, pageAndKoma.koma.ToList())
                    ).ToList();
            }
            catch (Exception e)
            {
                var errorMessage = $"CreateMstPageModelsにてエラーが発生しました。\n" + CheckCreateMstPageModelsError();
                throw new MasterDataCreateModelFailedException(errorMessage, e);
            }
        }

        string CheckCreateMstPageModelsError()
        {
            var errorMessage = string.Empty;
            var mstPageDatas = DataStore.Get<MstPageData>();
            var mstKomaLineDatas = DataStore.Get<MstKomaLineData>();

            var missingKomaLineIds = mstPageDatas
                .Where(page => mstKomaLineDatas.All(koma => koma.MstPageId != page.Id))
                .Select(page => page.Id)
                .ToList();
            if (missingKomaLineIds.Any())
            {
                errorMessage += $"MstPage：MstKomaLineに該当のMstPageIDが存在していません: {string.Join(", ", missingKomaLineIds)}\n";
            }

            Debug.LogError(errorMessage);
            return errorMessage;
        }

        #endregion

        #region IMstStageRewardDataRepository
        IReadOnlyList<MstStageRewardModel> IMstStageRewardDataRepository.GetMstStageRewardList(MasterDataId id)
        {
            return GetOrCreateModelCache(CreateGetMstStageRewardList)
                .Where(m => m.MstStageId == id)
                .ToList();
        }

        IReadOnlyList<MstStageRewardModel> CreateGetMstStageRewardList()
        {
            return DataStore.Get<MstStageRewardData>()
                .Select(MstStageRewardDataTranslator.ToMstStageRewardModel)
                .ToList();
        }
        #endregion

        #region IMstStageEventRewardDataRepository
        IReadOnlyList<MstStageEventRewardModel> IMstStageEventRewardDataRepository.GetMstStageEventRewardList(
            MasterDataId id)
        {
            return GetOrCreateModelCache(CreateMstStageEventRewardModels)
                .Where(m => m.MstStageId == id)
                .ToList();
        }

        IReadOnlyList<MstStageEventRewardModel> CreateMstStageEventRewardModels()
        {
            return DataStore.Get<MstStageEventRewardData>()
                .Select(StageEventRewardDataTranslator.CreateMstStageEventRewardModel)
                .ToList();
        }

        #endregion

        #region IMstStageEnhanceRewardParamDataRepository
        IReadOnlyList<MstStageEnhanceRewardParamModel> IMstStageEnhanceRewardParamDataRepository.GetStageEnhanceRewardParams()
        {
            return GetOrCreateModelCache(CreateMstStageEnhanceRewardParamModels)
                .ToList();
        }

        IReadOnlyList<MstStageEnhanceRewardParamModel> CreateMstStageEnhanceRewardParamModels()
        {
            return DataStore.Get<MstStageEnhanceRewardParamData>()
                .Select(StageEnhanceRewardParamDataTranslator.ToStageEnhanceRewardParamModel)
                .ToList();
        }
        #endregion

        #region IMstStageEndConditionDataRepository
        IReadOnlyList<MstStageEndConditionModel> IMstStageEndConditionDataRepository.GetMstStageEndConditions(MasterDataId id)
        {
            return GetOrCreateModelCache(CreateMstStageEndConditionModels)
                .Where(data => data.MstStageId == id)
                .ToList();
        }

        IReadOnlyList<MstStageEndConditionModel> CreateMstStageEndConditionModels()
        {
            return DataStore.Get<MstStageEndConditionData>()
                .Select(MstStateEndConditionDataTranslator.ToStageEndConditionModel)
                .ToList();
        }
        #endregion

        #region IMstCharacterDataRepository
        IReadOnlyList<MstCharacterModel> IMstCharacterDataRepository.GetCharacters()
        {
            return GetOrCreateModelCache(CreateMstCharacterModels).ToList();
        }

        IReadOnlyList<MstCharacterModel> IMstCharacterDataRepository.GetSeriesCharacters(MasterDataId mstSeriesId)
        {
            return GetOrCreateModelCache(CreateMstCharacterModels)
                .Where(mst => mst.MstSeriesId == mstSeriesId)
                .ToList();
        }

        MstCharacterModel IMstCharacterDataRepository.GetCharacter(MasterDataId id)
        {
            try
            {
                return GetOrCreateModelCache(CreateMstCharacterModels).First(m => m.Id == id);
            }
            catch(MasterDataCreateModelFailedException e)
            {
                throw;
            }
            catch (Exception e)
            {
                var errorMessage = $"MstCharacterに存在しないIdを指定しました\n該当のデータが無いか生成に失敗した可能性があります:{id}。\n";
                errorMessage += CheckCreateMstCharacterModelsError();
                throw new MasterDataNotFoundException(errorMessage, e);
            }
        }

        MstCharacterModel IMstCharacterDataRepository.GetCharacterByFragmentMstItemId(MasterDataId id)
        {
            try
            {
                return GetOrCreateModelCache(CreateMstCharacterModels).First(mst => mst.FragmentMstItemId == id);
            }
            catch(MasterDataCreateModelFailedException e)
            {
                throw;
            }
            catch (Exception e)
            {
                var errorMessage = $"MstCharacterに存在しないFragmentMstItemIdを指定しました\n該当のデータが無いか生成に失敗した可能性があります:{id}。\n";
                errorMessage += CheckCreateMstCharacterModelsError();
                throw new MasterDataNotFoundException(errorMessage, e);
            }
        }

        IReadOnlyList<MstCharacterModel> CreateMstCharacterModels()
        {
            try
            {
                var mstUnitI18NDatas = DataStore.Get<MstUnitI18nData>();
                var mstSeriesDatas = DataStore.Get<MstSeriesData>();
                var mstUnitRoleBonusDatas = DataStore.Get<MstUnitRoleBonusData>();

                var mstSpeechBalloonI18nDataLists =
                    DataStore.Get<MstSpeechBalloonI18nData>();

                // 複数回使用するためToListしておく
                var mstUnitAbilityDatas = DataStore.Get<MstUnitAbilityData>().ToList();
                var mstAbilityDatas = DataStore.Get<MstAbilityData>().ToList();
                var mstAbilityI18nDatas = DataStore.Get<MstAbilityI18nData>().ToList();

                var mstCharacterModels = DataStore.Get<MstUnitData>()
                    .Join(mstUnitI18NDatas, unit => unit.Id, i18n => i18n.MstUnitId, (unit, i18n) => (unit, i18n))
                    .Join(mstSeriesDatas, unit => unit.unit.MstSeriesId, series => series.Id,
                        (unit, series) => (unit.unit, unit.i18n, series))
                    .Join(mstUnitRoleBonusDatas, unit => unit.unit.RoleType, roleBonus => roleBonus.RoleType,
                        (unit, roleBonus) =>
                        (
                            unit.unit,
                            unit.i18n,
                            unit.series,
                            roleBonus
                        )
                    )
                    .GroupJoin(mstSpeechBalloonI18nDataLists, unit => unit.unit.Id, speech => speech.MstUnitId,
                        (unit, speech) =>
                        (
                            unit.unit,
                            unit.i18n,
                            unit.series,
                            unit.roleBonus,
                            speech.ToList()
                        )
                    )
                    .GroupJoin(mstUnitAbilityDatas, unit => unit.unit.MstUnitAbilityId1, ability => ability.Id,
                        (unit, ability) =>
                        (
                            unit.unit,
                            unit.i18n,
                            unit.series,
                            unit.roleBonus,
                            unit.Item5,
                            ability.FirstOrDefault()
                        )
                    )
                    .GroupJoin(mstUnitAbilityDatas, unit => unit.unit.MstUnitAbilityId2, ability => ability.Id,
                        (unit, ability) =>
                        (
                            unit.unit,
                            unit.i18n,
                            unit.series,
                            unit.roleBonus,
                            unit.Item5,
                            unit.Item6,
                            ability.FirstOrDefault()
                        )
                    )
                    .GroupJoin(mstUnitAbilityDatas, unit => unit.unit.MstUnitAbilityId3, ability => ability.Id,
                        (unit, ability) =>
                        (
                            unit.unit,
                            unit.i18n,
                            unit.series,
                            unit.roleBonus,
                            unit.Item5,
                            unit.Item6,
                            unit.Item7,
                            ability.FirstOrDefault()
                        )
                    )
                    .GroupJoin(mstAbilityDatas, unit => unit.Item6?.MstAbilityId, ability => ability.Id,
                        (unit, ability) =>
                        (
                            unit.unit,
                            unit.i18n,
                            unit.series,
                            unit.roleBonus,
                            unit.Item5,
                            unit.Item6,
                            unit.Item7,
                            unit.Item8,
                            ability.FirstOrDefault()
                        )
                    )
                    .GroupJoin(mstAbilityDatas, unit => unit.Item7?.MstAbilityId, ability => ability.Id,
                        (unit, ability) =>
                        (
                            unit.unit,
                            unit.i18n,
                            unit.series,
                            unit.roleBonus,
                            unit.Item5,
                            unit.Item6,
                            unit.Item7,
                            unit.Item8,
                            unit.Item9,
                            ability.FirstOrDefault()
                        )
                    )
                    .GroupJoin(mstAbilityDatas, unit => unit.Item8?.MstAbilityId, ability => ability.Id,
                        (unit, ability) =>
                        (
                            unit.unit,
                            unit.i18n,
                            unit.series,
                            unit.roleBonus,
                            unit.Item5,
                            unit.Item6,
                            unit.Item7,
                            unit.Item8,
                            unit.Item9,
                            unit.Item10,
                            ability.FirstOrDefault()
                        )
                    )
                    .GroupJoin(mstAbilityI18nDatas, unit => unit.Item9?.Id, abilityI18n => abilityI18n.MstAbilityId,
                        (unit, abilityI18n) =>
                        (
                            unit.unit,
                            unit.i18n,
                            unit.series,
                            unit.roleBonus,
                            unit.Item5,
                            unit.Item6,
                            unit.Item7,
                            unit.Item8,
                            unit.Item9,
                            unit.Item10,
                            unit.Item11,
                            abilityI18n.FirstOrDefault()
                        )
                    )
                    .GroupJoin(mstAbilityI18nDatas, unit => unit.Item10?.Id, abilityI18n => abilityI18n.MstAbilityId,
                        (unit, abilityI18n) =>
                        (
                            unit.unit,
                            unit.i18n,
                            unit.series,
                            unit.roleBonus,
                            unit.Item5,
                            unit.Item6,
                            unit.Item7,
                            unit.Item8,
                            unit.Item9,
                            unit.Item10,
                            unit.Item11,
                            unit.Item12,
                            abilityI18n.FirstOrDefault()
                        )
                    )
                    .GroupJoin(mstAbilityI18nDatas, unit => unit.Item11?.Id, abilityI18n => abilityI18n.MstAbilityId,
                        (unit, abilityI18n) =>
                            new
                            {
                                unit.unit,
                                unit.i18n,
                                unit.series,
                                unit.roleBonus,
                                speeches = unit.Item5,
                                unitAbility1 = unit.Item6,
                                unitAbility2 = unit.Item7,
                                unitAbility3 = unit.Item8,
                                ability1 = unit.Item9,
                                ability2 = unit.Item10,
                                ability3 = unit.Item11,
                                abilityI18n1 = unit.Item12,
                                abilityI18n2 = unit.Item13,
                                abilityI18n3 = abilityI18n.FirstOrDefault(),
                            }
                    )
                    .Select(data =>
                    {
                        var id = new MasterDataId(data.unit.Id);
                        var normalAttack = GetMstAttackModel(id, AttackKind.Normal);
                        var specialAttacks = GetSpecialAttackModels(id);
                        var abilityDataList = new List<MstAbilityDataModel>()
                        {
                            new(data.unitAbility1, data.ability1, data.abilityI18n1,
                                new UnitRank(data.unit.AbilityUnlockRank1)),
                            new(data.unitAbility2, data.ability2, data.abilityI18n2,
                                new UnitRank(data.unit.AbilityUnlockRank2)),
                            new(data.unitAbility3, data.ability3, data.abilityI18n3,
                                new UnitRank(data.unit.AbilityUnlockRank3)),
                        };

                        // 最大ランクより上に解放ランクが設定されているアビリティは設定エラーもしくは現状未解放想定のものとして空のものに置き換える
                        if (data.unit.HasSpecificRankUp)
                        {
                            var maxRank = GetUnitSpecificRankUpList(id).Max(x => x.Rank);
                            abilityDataList = abilityDataList
                                .Select(ability => ability.UnlockUnitRank <= maxRank
                                    ? ability
                                    : MstAbilityDataModel.Empty)
                                .ToList();
                        }
                        else
                        {
                            var maxRank = GetUnitRankUpList(data.unit.UnitLabel).Max(x => x.Rank);
                            abilityDataList = abilityDataList
                                .Select(ability => ability.UnlockUnitRank <= maxRank
                                    ? ability
                                    : MstAbilityDataModel.Empty)
                                .ToList();
                        }

                        return CharacterDataTranslator.ToCharacterModel(
                            data.unit,
                            data.i18n,
                            data.series,
                            abilityDataList,
                            data.roleBonus,
                            normalAttack,
                            specialAttacks,
                            data.speeches);
                    })
                    .ToList();

                return mstCharacterModels;
            }
            catch (MasterDataCreateModelFailedException e)
            {
                // GetSpecialAttackModelsでエラーが発生した場合などはエラー原因検証済みのためそのまま返す
                var errorMessage = $"CreateMstCharacterModels内の" + e.Message;
                throw new MasterDataCreateModelFailedException(errorMessage, e);
            }
            catch (Exception e)
            {
                var errorMessage = $"CreateMstCharacterModelsにてエラーが発生しました。\n" + CheckCreateMstCharacterModelsError();
                throw new MasterDataCreateModelFailedException(errorMessage, e);
            }
        }

        IReadOnlyList<MstSpecialAttackModel> GetSpecialAttackModels(MasterDataId mstUnitId)
        {
            try
            {
                return GetOrCreateModelCache(CreateMstSpecialAttackModels)
                    .Where(m => m.MstUnitId == mstUnitId)
                    .ToList();
            }
            catch (MasterDataCreateModelFailedException e)
            {
                var errorMessage = $"CreateMstSpecialAttackModelsにてエラーが発生しました。\n";
                throw new MasterDataCreateModelFailedException(errorMessage, e);
            }
        }

        IReadOnlyList<MstSpecialAttackModel> CreateMstSpecialAttackModels()
        {
            try
            {
                var i18ns = DataStore.Get<MstAttackI18nData>();
                var specialAttackI18ns = DataStore.Get<MstSpecialAttackI18nData>();
                return DataStore.Get<MstAttackData>()
                    .Where(d => d.AttackKind == AttackKind.Special)
                    .Join(i18ns, d => d.Id, i => i.MstAttackId, (d, i) => (d, i))
                    .Join(specialAttackI18ns, d => d.d.MstUnitId, i => i.MstUnitId, (d, s) => (d, s))
                    .Select(data => CreateMstSpecialAttackModel(data.d.d, data.d.i, data.s))
                    .ToList();
            }
            catch (Exception e)
            {
                var errorMessage = $"CreateMstSpecialAttackModelsにてエラーが発生しました。\n" + CheckCreateSpecialAttackModelsError();
                throw new MasterDataCreateModelFailedException(errorMessage, e);
            }
        }

        MstSpecialAttackModel CreateMstSpecialAttackModel(
            MstAttackData mstAttackData,
            MstAttackI18nData mstAttackI18nData,
            MstSpecialAttackI18nData mstSpecialAttackI18nData)
        {
            var attackData = CreateAttackData(mstAttackData);
            var specialRoleLevelUpAttackElements = CreateSpecialRoleLevelUpAttackElements(attackData);

            return SpecialAttackDataTranslator.ToSpecialAttackModel(
                mstAttackData,
                mstAttackI18nData,
                mstSpecialAttackI18nData,
                attackData,
                specialRoleLevelUpAttackElements);
        }

        string CheckCreateMstCharacterModelsError()
        {
            var errorMessage = string.Empty;
            var mstUnitI18NDatas = DataStore.Get<MstUnitI18nData>();
            var mstSeriesDatas = DataStore.Get<MstSeriesData>();
            var mstUnitRoleBonusDatas = DataStore.Get<MstUnitRoleBonusData>();

            var mstSpeechBalloonI18nDataLists = DataStore.Get<MstSpeechBalloonI18nData>();

            var mstUnitAbilityDatas = DataStore.Get<MstUnitAbilityData>().ToList();
            var mstAbilityDatas = DataStore.Get<MstAbilityData>().ToList();
            var mstAbilityI18nDatas = DataStore.Get<MstAbilityI18nData>().ToList();

            var mstUnitDatas = DataStore.Get<MstUnitData>().ToList();

            var mstUnitSpecificRankUpDatas = DataStore.Get<MstUnitSpecificRankUpData>();

            var missingUnitI18nIds = mstUnitDatas
                .Where(unit => mstUnitI18NDatas.All(i18n => i18n.MstUnitId != unit.Id))
                .Select(unit => unit.Id)
                .ToList();
            if (missingUnitI18nIds.Any())
            {
                errorMessage += $"MstUnit：MstUnitI18nに該当のUnitIDが存在していません: {string.Join(", ", missingUnitI18nIds)}\n";
            }

            var missingSeriesIds = mstUnitDatas
                .Where(unit => mstSeriesDatas.All(series => series.Id != unit.MstSeriesId))
                .Select(unit => ZString.Format("{0}:{1}", unit.Id, unit.MstSeriesId))
                .ToList();
            if (missingSeriesIds.Any())
            {
                errorMessage += $"MstUnit：MstSeriesに該当のSeriesIdが存在していません: {string.Join(", ", missingSeriesIds)}\n";
            }

            var missingRoleBonusIds = mstUnitDatas
                .Where(unit => mstUnitRoleBonusDatas.All(roleBonus => roleBonus.RoleType != unit.RoleType))
                .Select(unit => ZString.Format("{0}:{1}", unit.Id, unit.RoleType))
                .ToList();
            if (missingRoleBonusIds.Any())
            {
                errorMessage += $"MstUnit：MstUnitRoleBonusに該当のRoleTypeが存在していません: {string.Join(", ", missingRoleBonusIds)}\n";
            }

            var missingSpeechBalloonIds = mstUnitDatas
                .Where(unit => mstSpeechBalloonI18nDataLists.All(speech => speech.MstUnitId != unit.Id))
                .Select(unit => unit.Id)
                .ToList();
            if (missingSpeechBalloonIds.Any())
            {
                errorMessage += $"MstUnit：MstSpeechBalloonI18nに該当のUnitIdが存在していません: {string.Join(", ", missingSpeechBalloonIds)}\n";
            }

            // HasSpecificRankUpがオンのユニットはMstSpecificRankUpに該当Idがあるかチェエク
            var missingSpecificRankUpIds = mstUnitDatas
                .Where(unit => unit.HasSpecificRankUp && mstUnitSpecificRankUpDatas.All(rankUp => rankUp.MstUnitId != unit.Id))
                .Select(unit => unit.Id)
                .ToList();
            if (missingSpecificRankUpIds.Any())
            {
                errorMessage += $"MstUnitSpecificRankUpに該当のUnitIdが存在していません: {string.Join(", ", missingSpecificRankUpIds)}\n";
            }

            // 特性
            var missingUnitAbilityIds = new List<string>();
            var missingAbilityIds = new List<string>();
            var missingAbilityI18nIds = new List<string>();
            foreach (var unit in mstUnitDatas)
            {
                var abilityIds = new List<string>
                {
                    unit.MstUnitAbilityId1,
                    unit.MstUnitAbilityId2,
                    unit.MstUnitAbilityId3
                };

                foreach (var abilityId in abilityIds)
                {
                    if (string.IsNullOrEmpty(abilityId))
                    {
                        continue;
                    }

                    var unitAbility = mstUnitAbilityDatas.FirstOrDefault(ability => ability.Id == abilityId);
                    if (unitAbility == null)
                    {
                        missingUnitAbilityIds.Add(ZString.Format("{0}:{1}", unit.Id, unit.MstUnitAbilityId1));
                        continue;
                    }

                    var ability = mstAbilityDatas.FirstOrDefault(ability => ability.Id == unitAbility.MstAbilityId);
                    if (ability == null)
                    {
                        missingAbilityIds.Add(ZString.Format("{0}:{1}", unit.Id, unitAbility.MstAbilityId));
                        continue;
                    }

                    var abilityI18n = mstAbilityI18nDatas.FirstOrDefault(abilityI18n => abilityI18n.MstAbilityId == ability.Id);
                    if (abilityI18n == null)
                    {
                        missingAbilityI18nIds.Add(ZString.Format("{0}:{1}", unit.Id, ability.Id));
                    }
                }
            }

            if (missingUnitAbilityIds.Any())
            {
                errorMessage += $"MstUnit：MstUnitAbilityに該当のUnitAbilityIdが存在していません: {string.Join(", ", missingUnitAbilityIds)}\n";
            }

            if (missingAbilityIds.Any())
            {
                errorMessage += $"MstUnit：MstAbilityに存在しないAbilityIDがMstUnitAbilityに設定されています: {string.Join(", ", missingAbilityIds)}\n";
            }

            if (missingAbilityI18nIds.Any())
            {
                errorMessage += $"MstUnit：MstAbilityI18nDataに存在しないAbilityIDがMstUnitAbilityに設定されています: {string.Join(", ", missingAbilityI18nIds)}\n";
            }

            Debug.LogError(errorMessage);
            return errorMessage;
        }

        string CheckCreateSpecialAttackModelsError()
        {
            var errorMessage = string.Empty;
            var mstAttackDatas = DataStore.Get<MstAttackData>();
            var mstAttackI18nDatas = DataStore.Get<MstAttackI18nData>();
            var mstSpecialAttackI18nDatas = DataStore.Get<MstSpecialAttackI18nData>();

            var mstSpecialAttackDatas = mstAttackDatas
                .Where(attack => attack.AttackKind == AttackKind.Special)
                .ToList();

            var missingMstAttackI18nIds = mstSpecialAttackDatas
                .Where(attack => mstAttackI18nDatas.All(i18n => i18n.MstAttackId != attack.Id))
                .Select(attack => attack.Id)
                .ToList();
            if (missingMstAttackI18nIds.Any())
            {
                errorMessage += $"MstAttack：MstAttackI18nに該当のMstAttackIDが存在していません: {string.Join(", ", missingMstAttackI18nIds)}\n";
            }

            var missingMstSpecialAttackI18nIds = mstSpecialAttackDatas
                .Where(attack => mstSpecialAttackI18nDatas.All(i18n => i18n.MstUnitId != attack.MstUnitId))
                .Select(attack => attack.MstUnitId)
                .ToList();
            if (missingMstSpecialAttackI18nIds.Any())
            {
                errorMessage += $"MstSpecialAttack：MstSpecialAttackI18nに該当のMstUnitIDが存在していません: {string.Join(", ", missingMstSpecialAttackI18nIds)}\n";
            }

            Debug.LogError(errorMessage);
            return errorMessage;
        }

        #endregion

        #region IMstEnemyCharacterDataRepository
        IReadOnlyList<MstEnemyCharacterModel> IMstEnemyCharacterDataRepository.GetEnemyCharacters()
        {
            return GetOrCreateModelCache(CreateMstEnemyCharacterModels)
                .ToList();
        }

        IReadOnlyList<MstEnemyCharacterModel> IMstEnemyCharacterDataRepository.GetSeriesEnemyCharacters(MasterDataId mstSeriesId)
        {
            return GetOrCreateModelCache(CreateMstEnemyCharacterModels)
                .Where(d => d.MstSeriesId == mstSeriesId)
                .ToList();
        }

        MstEnemyCharacterModel IMstEnemyCharacterDataRepository.GetEnemyCharacter(MasterDataId mstEnemyCharacterId)
        {
            try
            {
                return GetOrCreateModelCache(CreateMstEnemyCharacterModels)
                    .First(m => m.Id == mstEnemyCharacterId);
            }
            catch (MasterDataCreateModelFailedException e)
            {
                throw;
            }
            catch(Exception e)
            {
                var errorMessage = $"MstEnemyCharacterに存在しないIdを指定しました\n該当のデータが無いか生成に失敗した可能性があります:{mstEnemyCharacterId}。\n";
                errorMessage += CheckCreateMstEnemyCharacterModelsError();
                throw new MasterDataNotFoundException(errorMessage, e);
            }
        }

        IReadOnlyList<MstEnemyCharacterModel> CreateMstEnemyCharacterModels()
        {
            try
            {
                var i18nDatas = DataStore.Get<MstEnemyCharacterI18nData>();
                return DataStore.Get<MstEnemyCharacterData>()
                    .Join(i18nDatas, data => data.Id, i18n => i18n.MstEnemyCharacterId, (data, i18n) => (data, i18n))
                    .Select(datas => MstEnemyCharacterDataTranslator.ToEnemyCharacterModel(datas.data, datas.i18n))
                    .ToList();
            }
            catch (Exception e)
            {
                var errorMessage = CheckCreateMstEnemyCharacterModelsError();
                throw new MasterDataCreateModelFailedException(errorMessage, e);
            }
        }

        string CheckCreateMstEnemyCharacterModelsError()
        {
            var errorMessage = string.Empty;
            var mstEnemyCharacterDatas = DataStore.Get<MstEnemyCharacterData>();
            var mstEnemyCharacterI18nDatas = DataStore.Get<MstEnemyCharacterI18nData>();

            var missingEnemyCharacterI18nIds = mstEnemyCharacterDatas
                .Where(enemy => mstEnemyCharacterI18nDatas.All(i18n => i18n.MstEnemyCharacterId != enemy.Id))
                .Select(enemy => enemy.Id)
                .ToList();
            if (missingEnemyCharacterI18nIds.Any())
            {
                errorMessage += $"MstEnemyCharacter：MstEnemyCharacterI18nに該当のMstEnemyCharacterIdが存在していません: {string.Join(", ", missingEnemyCharacterI18nIds)}\n";
            }

            Debug.LogError(errorMessage);
            return errorMessage;
        }

        /// <summary>
        ///  ゲーム起動後はじめてインゲームに遷移するときのローディング時間短縮のため
        /// （2025/4/6時点では）MstEnemyStageParameterModelだけキャッシュ方法が異なります
        ///  また全件取得が必要になった場合は他のModelとは異なる処理で実装する必要があります
        /// </summary>
        MstEnemyStageParameterModel IMstEnemyCharacterDataRepository.GetEnemyStageParameter(MasterDataId id)
        {
            if (_mstEnemyStageParameterModelDictionary.TryGetValue(id, out var model))
            {
                return model;
            }

            var newModel = CreateMstEnemyStageParameterModel(id);
            _mstEnemyStageParameterModelDictionary.Add(id, newModel);

            return newModel;
        }

        MstEnemyStageParameterModel CreateMstEnemyStageParameterModel(MasterDataId id)
        {
            try
            {
                var enemyStageParameterData = DataStore
                .Get<MstEnemyStageParameterData>()
                .Find(data => data.Id == id.Value);

                var enemyCharacterData = DataStore
                    .Get<MstEnemyCharacterData>()
                    .Find(data => data.Id == enemyStageParameterData.MstEnemyCharacterId);

                var enemyCharacterI18nData = DataStore
                    .Get<MstEnemyCharacterI18nData>()
                    .Find(data => data.MstEnemyCharacterId == enemyStageParameterData.MstEnemyCharacterId);

                var unitRoleBonusData = DataStore
                    .Get<MstUnitRoleBonusData>()
                    .Find(data => data.RoleType == enemyStageParameterData.RoleType);

                var unitAbilityData = DataStore
                    .Get<MstUnitAbilityData>()
                    .Find(data => data.Id == enemyStageParameterData.MstUnitAbilityId1);

                MstAbilityData abilityData = null;
                MstAbilityI18nData abilityI18nData = null;

                if (unitAbilityData != null)
                {
                    abilityData = DataStore
                        .Get<MstAbilityData>()
                        .Find(data => data.Id == unitAbilityData.MstAbilityId);

                    abilityI18nData = DataStore
                        .Get<MstAbilityI18nData>()
                        .Find(data => data.MstAbilityId == unitAbilityData.MstAbilityId);
                }

                var normalAttack = GetMstAttackModel(id, AttackKind.Normal);
                var specialAttack = GetMstAttackModel(id, AttackKind.Special);
                var appearanceAttack = GetMstAttackModel(id, AttackKind.Appearance);

                return EnemyStageParameterDataTranslator.ToEnemyStageParameterModel(
                    enemyCharacterData,
                    enemyStageParameterData,
                    enemyCharacterI18nData,
                    unitAbilityData,
                    abilityI18nData,
                    abilityData,
                    unitRoleBonusData,
                    normalAttack.AttackData,
                    specialAttack.AttackData,
                    appearanceAttack.AttackData);
            }
            catch (Exception e)
            {
                var errorMessage = $"CreateMstEnemyStageParameterModelにてエラーが発生しました。\n" + CheckCreateMstEnemyStageParameterModel(id);
                throw new MasterDataCreateModelFailedException(errorMessage, e);
            }
        }

        string CheckCreateMstEnemyStageParameterModel(MasterDataId id)
        {
            var errorMessage = string.Empty;
            var enemyStageParameterData = DataStore
                .Get<MstEnemyStageParameterData>()
                .Find(data => data.Id == id.Value);
            if (enemyStageParameterData == null)
            {
                errorMessage += $"MstEnemyStageParameter：MstEnemyStageParameterに該当のIdが存在していません: {id}\n";
            }
            else
            {
                var enemyCharacterData = DataStore
                    .Get<MstEnemyCharacterData>()
                    .Find(data => data.Id == enemyStageParameterData.MstEnemyCharacterId);
                if (enemyCharacterData == null)
                {
                    errorMessage += $"MstEnemyStageParameter：MstEnemyCharacterに該当のIdが存在していません: {enemyStageParameterData.MstEnemyCharacterId}\n";
                }

                var enemyCharacterI18nData = DataStore
                    .Get<MstEnemyCharacterI18nData>()
                    .Find(data => data.MstEnemyCharacterId == enemyStageParameterData.MstEnemyCharacterId);
                if (enemyCharacterI18nData == null)
                {
                    errorMessage += $"MstEnemyStageParameter：MstEnemyCharacterI18nに該当のMstEnemyCharacterIdが存在していません: {enemyStageParameterData.MstEnemyCharacterId}\n";
                }

                var unitRoleBonusData = DataStore
                    .Get<MstUnitRoleBonusData>()
                    .Find(data => data.RoleType == enemyStageParameterData.RoleType);
                if (unitRoleBonusData == null)
                {
                    errorMessage += $"MstEnemyStageParameter：MstUnitRoleBonusに該当のRoleTypeが存在していません: {enemyStageParameterData.RoleType}\n";
                }

                var unitAbilityData = DataStore
                    .Get<MstUnitAbilityData>()
                    .Find(data => data.Id == enemyStageParameterData.MstUnitAbilityId1);
                if (unitAbilityData == null)
                {
                    errorMessage += $"MstEnemyStageParameter：MstUnitAbilityに該当のIdが存在していません: {enemyStageParameterData.MstUnitAbilityId1}\n";
                }
                else
                {
                    var abilityData = DataStore
                        .Get<MstAbilityData>()
                        .Find(data => data.Id == unitAbilityData.MstAbilityId);
                    if (abilityData == null)
                    {
                        errorMessage += $"MstEnemyStageParameter：MstAbilityに該当のIdが存在していません: {unitAbilityData.MstAbilityId}\n";
                    }

                    var abilityI18nData = DataStore
                        .Get<MstAbilityI18nData>()
                        .Find(data => data.MstAbilityId == unitAbilityData.MstAbilityId);
                    if (abilityI18nData == null)
                    {
                        errorMessage += $"MstEnemyStageParameter：MstAbilityI18nに該当のMstAbilityIdが存在していません: {unitAbilityData.MstAbilityId}\n";
                    }
                }
            }

            Debug.LogError(errorMessage);
            return errorMessage;
        }

        #endregion

        #region IMstUnitRoleBonusDataRepository
        IReadOnlyList<MstUnitRoleBonusModel> IMstUnitRoleBonusDataRepository.GetUnitRoleBonuses()
        {
            return GetOrCreateModelCache(CreateMstUnitRoleBonusModels).ToList();
        }

        IReadOnlyList<MstUnitRoleBonusModel> CreateMstUnitRoleBonusModels()
        {
            return DataStore.Get<MstUnitRoleBonusData>()
                .Select(MstUnitRoleBonusDataTranslator.Translate)
                .ToList();
        }

        #endregion

        #region IMstUnitLevelUpRepository

        IReadOnlyList<MstUnitLevelUpModel> IMstUnitLevelUpRepository.GetUnitLevelUpList()
        {
            return GetOrCreateModelCache(CreateMstUnitLevelUpModels)
                .ToList();
        }
        IReadOnlyList<MstUnitLevelUpModel> IMstUnitLevelUpRepository.GetUnitLevelUpList(UnitLabel unitLabel)
        {
            return GetOrCreateModelCache(CreateMstUnitLevelUpModels)
                .Where(m => m.UnitLabel == unitLabel)
                .ToList();
        }
        MstUnitLevelUpModel IMstUnitLevelUpRepository.GetUnitMaxLevelUp(UnitLabel unitLabel)
        {
            return GetOrCreateModelCache(CreateMstUnitLevelUpModels)
                .Where(m => m.UnitLabel == unitLabel)
                .OrderByDescending(m => m.Level)
                .FirstOrDefault();
        }
        IReadOnlyList<MstUnitLevelUpModel> CreateMstUnitLevelUpModels()
        {
            return DataStore.Get<MstUnitLevelUpData>()
                .Select(MstUnitDataTranslator.TranslateLevelUp)
                .ToList();
        }
        #endregion

        #region IMstUnitRankUpRepository
        IReadOnlyList<MstUnitRankUpModel> IMstUnitRankUpRepository.GetUnitRankUpList(UnitLabel unitLabel)
        {
            return GetUnitRankUpList(unitLabel);
        }

        IReadOnlyList<MstUnitRankUpModel> GetUnitRankUpList(UnitLabel unitLabel)
        {
            return GetOrCreateModelCache(CreateMstUnitRankUpModels)
                .Where(m => m.UnitLabel == unitLabel)
                .ToList();
        }

        IReadOnlyList<MstUnitRankUpModel> CreateMstUnitRankUpModels()
        {
            return DataStore.Get<MstUnitRankUpData>()
                .Select(MstUnitDataTranslator.TranslateRankUp)
                .ToList();
        }

        #endregion

        #region IMstUnitSpecificRankUpRepository
        IReadOnlyList<MstUnitSpecificRankUpModel> IMstUnitSpecificRankUpRepository.GetUnitSpecificRankUpList(
            MasterDataId mstUnitId)
        {
            return GetUnitSpecificRankUpList(mstUnitId);
        }

        IReadOnlyList<MstUnitSpecificRankUpModel> GetUnitSpecificRankUpList(MasterDataId mstUnitId)
        {
            return GetOrCreateModelCache(CreateMstUnitSpecificRankUpModels)
                .Where(m => m.MstUnitId == mstUnitId)
                .ToList();
        }

        IReadOnlyList<MstUnitSpecificRankUpModel> CreateMstUnitSpecificRankUpModels()
        {
            return DataStore.Get<MstUnitSpecificRankUpData>()
                .Select(MstUnitSpecificRankUpDataTranslator.Translate)
                .ToList();
        }

        #endregion

        #region IMstUnitRankCoefficientRepository
        IReadOnlyList<MstUnitRankCoefficientModel> IMstUnitRankCoefficientRepository.GetUnitRankCoefficientList()
        {
            return GetOrCreateModelCache(CreateMstUnitRankCoefficientModels).ToList();
        }

        IReadOnlyList<MstUnitRankCoefficientModel> CreateMstUnitRankCoefficientModels()
        {
            return DataStore.Get<MstUnitRankCoefficientData>()
                .Select(MstUnitDataTranslator.TranslateRankCoefficient)
                .ToList();
        }
        #endregion

        #region IMstUnitGradeUpRepository
        IReadOnlyList<MstUnitGradeUpModel> IMstUnitGradeUpRepository.GetUnitGradeUpList(UnitLabel unitLabel)
        {
            return GetOrCreateModelCache(CreateMstUnitGradeUpModels)
                .Where(m => m.UnitLabel == unitLabel)
                .ToList();
        }

        IReadOnlyList<MstUnitGradeUpModel> CreateMstUnitGradeUpModels()
        {
            return DataStore.Get<MstUnitGradeUpData>()
                .Select(MstUnitDataTranslator.TranslateGradeUp)
                .ToList();
        }

        #endregion

        #region IMstUnitGradeCoefficientRepository
        IReadOnlyList<MstUnitGradeCoefficientModel> IMstUnitGradeCoefficientRepository.GetUnitGradeCoefficientList()
        {
            return GetOrCreateModelCache(CreateMstUnitGradeCoefficientModels)
                .ToList();
        }

        IReadOnlyList<MstUnitGradeCoefficientModel> CreateMstUnitGradeCoefficientModels()
        {
            return DataStore.Get<MstUnitGradeCoefficientData>()
                .Select(MstUnitDataTranslator.TranslateGradeCoefficient)
                .ToList();
        }
        #endregion

        MstAttackModel GetMstAttackModel(MasterDataId mstUnitId, AttackKind attackKind)
        {
            var targetData = DataStore.Get<MstAttackData>()
                .FirstOrDefault(d => d.MstUnitId == mstUnitId.Value && d.AttackKind == attackKind);
            if (targetData == null) return MstAttackModel.Empty;

            var attackData =  CreateAttackData(targetData);

            return new MstAttackModel(
                new MasterDataId(targetData.Id),
                attackData);
        }

        AttackData CreateAttackData(MstAttackData attackData)
        {
            var elements = DataStore.Get<MstAttackElementData>()
                .Where(d => d.MstAttackId == attackData.Id)
                .ToList();

            var attackHitEffectDataList = DataStore.Get<MstAttackHitEffectData>()
                .ToList();

            return AttackDataTranslator.ToAttackData(attackData, elements, attackHitEffectDataList);
        }

        IReadOnlyList<SpecialRoleLevelUpAttackElement> CreateSpecialRoleLevelUpAttackElements(AttackData attackData)
        {
            var dataList = DataStore.Get<MstSpecialRoleLevelUpAttackElementData>().ToList();
            var attackElementIds = attackData.GetAllElementIds();

            var specialRoleLevelUpAttackElementList = attackElementIds
                .SelectMany(id => dataList.Where(data => data.MstAttackElementId == id.Value))
                .ToList();

            return MstSpecialRoleLevelUpAttackElementDataTranslator
                .ToSpecialRoleLevelUpAttackElements(specialRoleLevelUpAttackElementList);
        }

        #region IMstEnemyOutpostDataRepository
        MstEnemyOutpostModel IMstEnemyOutpostDataRepository.GetEnemyOutpost(MasterDataId id)
        {
            try
            {
                return GetOrCreateModelCache(CreateMstEnemyOutpostModels)
                    .First(m => m.Id == id);
            }
            catch(Exception e)
            {
                var errorMessage = $"MstEnemyOutpostに存在しないIdを指定しました\n該当のデータが無いか生成に失敗した可能性があります:{id}。\n";
                throw new MasterDataNotFoundException(errorMessage, e);
            }
        }

        IReadOnlyList<MstEnemyOutpostModel> CreateMstEnemyOutpostModels()
        {
            return DataStore.Get<MstEnemyOutpostData>()
                .Select(EnemyOutpostDataTranslator.ToEnemyOutpostModel)
                .ToList();
        }
        #endregion

        # region IMstOutpostEnhanceDataRepository
        MstOutpostModel IMstOutpostEnhanceDataRepository.GetOutpostModel(MasterDataId id)
        {
            try
            {
                return GetOrCreateModelCache(CreateMstOutpostModels).First(m => m.Id == id);
            }
            catch (MasterDataCreateModelFailedException e)
            {
                throw;
            }
            catch (Exception e)
            {
                var errorMessage = $"MstOutpostに存在しないIdを指定しました\n該当のデータが無いか生成に失敗した可能性があります:{id}。\n";
                errorMessage += CheckCreateMstOutpostModelsError();
                throw new MasterDataNotFoundException(errorMessage, e);
            }
        }

        IReadOnlyList<MstOutpostModel> CreateMstOutpostModels()
        {
            try
            {
                var levelI18ns = DataStore.Get<MstOutpostEnhancementLevelI18nData>();
                var levels = DataStore.Get<MstOutpostEnhancementLevelData>()
                    .Join(
                        levelI18ns,
                        level => level.Id,
                        i18n => i18n.MstOutpostEnhancementLevelId,
                        (level, i18n) => (level, i18n))
                    .ToList();

                var enhancementI18ns = DataStore.Get<MstOutpostEnhancementI18nData>();
                // enhance : level = 1 : nの関係
                var enhances = DataStore.Get<MstOutpostEnhancementData>()
                    .Join(
                        enhancementI18ns,
                        enhancement => enhancement.Id,
                        i18n => i18n.MstOutpostEnhancementId,
                        (data, i18n) => (data, i18n))
                    .GroupJoin(
                        levels,
                        enhancement => enhancement.data.Id,
                        level => level.level.MstOutpostEnhancementId,
                        (enhancement, levels) => (enhancement, levels))
                    .ToList();

                // outpost : enhance : level = 1 : n : mの関係
                return DataStore.Get<MstOutpostData>()
                    .GroupJoin(
                        enhances,
                        outpost => outpost.Id,
                        enhancement => enhancement.enhancement.data.MstOutpostId,
                        (outpost, enhancements) => (outpost, enhancements))
                    .Select(data =>
                    {
                        return OutpostEnhanceDataTranslator.ToOutpostModel(
                            data.outpost,
                            data.enhancements.Select(e => e.enhancement).ToList(),
                            data.enhancements.SelectMany(e => e.levels).ToList()
                        );
                    })
                    .ToList();
            }
            catch (Exception e)
            {
                var errorMessage = CheckCreateMstOutpostModelsError();
                throw new MasterDataCreateModelFailedException(errorMessage, e);
            }
        }

        string CheckCreateMstOutpostModelsError()
        {
            var errorMessage = string.Empty;
            var outpostEnhancementDataList = DataStore.Get<MstOutpostEnhancementData>().ToList();
            var outpostEnhancementLevelDataList = DataStore.Get<MstOutpostEnhancementLevelData>().ToList();
            var outpostEnhancementLevelI18nDataList = DataStore.Get<MstOutpostEnhancementLevelI18nData>();
            var outpostEnhancementI18nDataList = DataStore.Get<MstOutpostEnhancementI18nData>();
            var outpostDataList = DataStore.Get<MstOutpostData>();

            var missingLevelI18nIds = outpostEnhancementLevelDataList
                .Where(level => outpostEnhancementLevelI18nDataList.All(i18n => i18n.MstOutpostEnhancementLevelId != level.Id))
                .Select(level => level.Id)
                .ToList();
            if (missingLevelI18nIds.Any())
            {
                errorMessage += $"MstOutpostEnhancementLevel：MstOutpostEnhancementLevelI18nに該当のMstOutpostEnhancementLevelIdが存在していません: {string.Join(", ", missingLevelI18nIds)}\n";
            }

            var missingI18nIds = outpostEnhancementDataList
                .Where(enhance => outpostEnhancementI18nDataList.All(i18n => i18n.MstOutpostEnhancementId != enhance.Id))
                .Select(enhance => enhance.Id)
                .ToList();
            if (missingI18nIds.Any())
            {
                errorMessage += $"MstOutpostEnhancement：MstOutpostEnhancementI18nに該当のMstOutpostEnhancementIdが存在していません: {string.Join(", ", missingI18nIds)}\n";
            }

            // enhance : level = 1 : nの関係。levelが1つ以上はあるはず
            var missingLevelIds = outpostEnhancementDataList
                .Where(enhance => outpostEnhancementLevelDataList.All(level => level.MstOutpostEnhancementId != enhance.Id))
                .Select(enhance => enhance.Id)
                .ToList();
            if (missingLevelIds.Any())
            {
                errorMessage += $"MstOutpostEnhancement：MstOutpostEnhancementLevelに該当のIdが一つも存在していません: {string.Join(", ", missingLevelIds)}\n";
            }

            // outpost : enhance : level = 1 : n : mの関係。enhanceが一つ以上はあるはず
            var missingOutpostIds = outpostDataList
                .Where(outpost => outpostEnhancementDataList.All(enhance => enhance.MstOutpostId != outpost.Id))
                .Select(outpost => outpost.Id)
                .ToList();
            if (missingOutpostIds.Any())
            {
                errorMessage += $"MstOutpost：MstOutpostEnhancementに該当のMstOutpostIdが一つも存在していません: {string.Join(", ", missingOutpostIds)}\n";
            }

            Debug.LogError(errorMessage);
            return errorMessage;
        }

        #endregion

        # region IMstItemDataRepository
        IReadOnlyList<MstItemModel> IMstItemDataRepository.GetItems()
        {
            return GetOrCreateModelCache(CreateItems).ToList();
        }

        MstItemModel IMstItemDataRepository.GetItem(MasterDataId id)
        {
            try
            {
                return GetOrCreateModelCache(CreateItems).First(m => m.Id == id);
            }
            catch (MasterDataCreateModelFailedException e)
            {
                throw;
            }
            catch (Exception e)
            {
                var errorMessage = $"MstItemに存在しないIdを指定しました\n該当のデータが無いか生成に失敗した可能性があります:{id}。\n";
                errorMessage += CheckCreateItemsError();
                throw new MasterDataNotFoundException(errorMessage, e);
            }
        }
        IReadOnlyList<MstItemModel> CreateItems()
        {
            try
            {
                var i18ns = DataStore.Get<MstItemI18nData>();

                return DataStore.Get<MstItemData>()
                    .Join(i18ns, d => d.Id, i => i.MstItemId, (d, i) => (d, i))
                    .Select(mst => ItemDataTranslator.ToItemModel(mst.d, mst.i))
                    .ToList();
            }
            catch (Exception e)
            {
                var errorMessage = CheckCreateItemsError();
                throw new MasterDataCreateModelFailedException(errorMessage, e);
            }
        }

        string CheckCreateItemsError()
        {
            var errorMessage = string.Empty;
            var itemDataList = DataStore.Get<MstItemData>();
            var itemI18nDataList = DataStore.Get<MstItemI18nData>();

            var missingItemIds = itemDataList
                .Where(item => itemI18nDataList.All(i18n => i18n.MstItemId != item.Id))
                .Select(item => item.Id)
                .ToList();
            if (missingItemIds.Any())
            {
                errorMessage += $"MstItem：MstItemI18nに該当のItemIdが存在していません: {string.Join(", ", missingItemIds)}\n";
            }

            Debug.LogError(errorMessage);
            return errorMessage;
        }

        #endregion

        #region IMstFragmentBoxGroupDataRepository
        IReadOnlyList<MstFragmentBoxGroupModel> IMstFragmentBoxGroupDataRepository.GetFragmentBoxGroups(MasterDataId id)
        {
            return GetOrCreateModelCache(CreateMstFragmentBoxGroupModels)
                .Where(m => m.GroupId == id)
                .ToList();
        }
        IReadOnlyList<MstFragmentBoxGroupModel> CreateMstFragmentBoxGroupModels()
        {
            return DataStore.Get<MstFragmentBoxGroupData>()
                .Select(FragmentBoxGroupDataTranslator.ToFragmentBoxGroupModel)
                .ToList();
        }
        #endregion

        #region IMstFragmentBoxDataRepository
        MstFragmentBoxModel IMstFragmentBoxDataRepository.GetFragmentBox(MasterDataId itemId)
        {
            try
            {
                return GetOrCreateModelCache(CreateMstFragmentBoxModels).First(m => m.ItemId == itemId);
            }
            catch (Exception e)
            {
                var errorMessage = $"MstFragmentBoxに存在しないIdを指定しました\n該当のデータが無いか生成に失敗した可能性があります:{itemId}。\n";
                throw new MasterDataNotFoundException(errorMessage, e);
            }
        }
        IReadOnlyList<MstFragmentBoxModel> CreateMstFragmentBoxModels()
        {
            return DataStore.Get<MstFragmentBoxData>()
                .Select(FragmentBoxDataTranslator.ToFragmentBoxModel)
                .ToList();
        }

        #endregion

        #region IMstItemTransitionDataRepository
        MstItemTransitionModel IMstItemTransitionDataRepository.GetEarnLocationFirstOrDefault(MasterDataId mstItemId)
        {
            return GetOrCreateModelCache(CreateMstItemTransitionModels)
                .FirstOrDefault(m => m.MstItemId == mstItemId, MstItemTransitionModel.Empty);
        }

        IReadOnlyList<MstItemTransitionModel> CreateMstItemTransitionModels()
        {
            return DataStore.Get<MstItemTransitionData>().Select(EarnLocationDataTranslator.ToEarnLocationModel).ToList();
        }

        #endregion

        #region IMstUserLevelDataRepository

        MstUserLevelModel IMstUserLevelDataRepository.GetUserLevelModel(UserLevel level)
        {
            try
            {
                return GetOrCreateModelCache(CreateMstUserLevelModels).First(m => m.Level == level);
            }
            catch (MasterDataCreateModelFailedException e)
            {
                throw;
            }
            catch (Exception e)
            {
                var errorMessage = $"MstUserLevelに存在しないUserLevelを指定しました\n該当のデータが無いか生成に失敗した可能性があります:{level}。\n";
                throw new MasterDataNotFoundException(errorMessage, e);
            }
        }

        MstUserLevelModel IMstUserLevelDataRepository.GetMaxUserLevelModel()
        {
            return GetOrCreateModelCache(CreateMstUserLevelModels).MaxBy(m => m.Level);
        }

        IReadOnlyList<MstUserLevelModel> CreateMstUserLevelModels()
        {
            var joinedDatas = DataStore.Get<MstUserLevelData>();
            return DataStore.Get<MstUserLevelData>()
                .GroupJoin(joinedDatas,
                    d => d.Level + 1,
                    joined => joined.Level,
                    (d, n) => new {d, next = n.FirstOrDefault()})//MaxLevelは次居ないのでnullになる
                .Select(d => UserLevelDataTranslator.ToUserLevelModel(d.d, d.next ?? d.d))
                .ToList();
        }

        #endregion

        IReadOnlyList<MstShopItemModel> IMstShopProductDataRepository.GetShopProducts()
        {
            return DataStore.Get<MstShopItemData>()
                .Select(ShopProductDataTranslator.ToMstShopProductModel).ToList();
        }

        IReadOnlyList<MstStoreProductModel> IMstShopProductDataRepository.GetStoreProducts()
        {
            return GetOrCreateModelCache(CreateMstStoreProductModels).ToList();
        }

        IReadOnlyList<MstStoreProductModel> CreateMstStoreProductModels()
        {
            try
            {
                var oprProductDataList = DataStore.Get<OprProductData>();
                var storeProductDataList = DataStore.Get<MstStoreProductData>();
                var storeProductI18nDataList = DataStore.Get<MstStoreProductI18nData>();
                var oprProductI18nDataList = DataStore.Get<OprProductI18nData>();

                return oprProductDataList
                    .Join(
                        storeProductDataList,
                        oprData => oprData.MstStoreProductId,
                        storeProductData => storeProductData.Id,
                        (oprData, storeProductData) => (oprData, storeProductData))
                    .Join(
                        storeProductI18nDataList,
                        d => d.oprData.MstStoreProductId,
                        storeProductI18nData => storeProductI18nData.MstStoreProductId,
                        (d, storeProductI18nData) => (d.oprData, d.storeProductData, storeProductI18nData))
                    .Join(
                        oprProductI18nDataList,
                        d => d.oprData.Id,
                        oprProductI18nData => oprProductI18nData.OprProductId,
                        (data, oprProductI18nData) => (
                            data.storeProductData,
                            data.storeProductI18nData,
                            data.oprData,
                            oprProductI18nData))
                    .Select(data => StoreProductDataTranslator.ToStoreProductModel(
                        data.storeProductData,
                        data.storeProductI18nData,
                        data.oprData,
                        data.oprProductI18nData,
                        SystemInfoProvider))
                    .ToList();
            }
            catch (Exception e)
            {
                var errorMessage = CheckCreateMstStoreProductModels();
                throw new MasterDataCreateModelFailedException(errorMessage, e);
            }
        }

        string CheckCreateMstStoreProductModels()
        {
            var errorMessage = string.Empty;
            var oprProductDataList = DataStore.Get<OprProductData>();
            var storeProductDataList = DataStore.Get<MstStoreProductData>();
            var storeProductI18nDataList = DataStore.Get<MstStoreProductI18nData>();

            var missingStoreProductIds = oprProductDataList
                .Where(opr => storeProductDataList.All(store => store.Id != opr.MstStoreProductId))
                .Select(opr => opr.MstStoreProductId)
                .ToList();
            if (missingStoreProductIds.Any())
            {
                errorMessage += $"OprProduct：MstStoreProductに該当のIdが存在していません: {string.Join(", ", missingStoreProductIds)}\n";
            }

            var missingI18nIds = oprProductDataList
                .Where(opr => storeProductI18nDataList.All(i18n => i18n.MstStoreProductId != opr.MstStoreProductId))
                .Select(opr => opr.MstStoreProductId)
                .ToList();
            if (missingI18nIds.Any())
            {
                errorMessage += $"OprProduct：MstStoreProductI18nに該当のMstStoreProductIdが存在していません: {string.Join(", ", missingI18nIds)}\n";
            }

            Debug.LogError(errorMessage);
            return errorMessage;
        }

        IReadOnlyList<MstShopPassModel> IMstShopProductDataRepository.GetShopPasses()
        {
            return GetOrCreateModelCache(CreateMstShopPasses).ToList();
        }

        MstShopPassModel IMstShopProductDataRepository.GetShopPass(MasterDataId mstShopPassId)
        {
            try
            {
                return GetOrCreateModelCache(CreateMstShopPasses).First(m => m.MstShopPassId == mstShopPassId);
            }
            catch (MasterDataCreateModelFailedException e)
            {
                throw;
            }
            catch (Exception e)
            {
                var errorMessage = $"MstShopProductに存在しないMstShopPassIdを指定しました\n該当のデータが無いか生成に失敗した可能性があります:{mstShopPassId}。\n";
                errorMessage += CheckCreateMstShopPassesError();
                throw new MasterDataNotFoundException(errorMessage, e);
            }
        }

        IReadOnlyList<MstShopPassModel> CreateMstShopPasses()
        {
            try
            {
                var datas = DataStore.Get<MstShopPassData>();
                var i18nDatas = DataStore.Get<MstShopPassI18nData>();
                var mstStoreProductModels = GetOrCreateModelCache(CreateMstStoreProductModels);

                return datas
                    .Join(i18nDatas, data => data.Id, i18nData => i18nData.MstShopPassId,
                        (data, i18nData) => (data, i18nData))
                    .Join(mstStoreProductModels, d => d.data.OprProductId,
                        mstStoreProductModel => mstStoreProductModel.OprProductId.ToString(),
                        (d, mstStoreProductModel) => (d.data, d.i18nData, mstStoreProductModel))
                    .Select(data =>
                        MstShopPassDataTranslator.ToMstShopPassModel(data.data, data.i18nData,
                            data.mstStoreProductModel))
                    .ToList();
            }
            catch (MasterDataCreateModelFailedException e)
            {
                // CreateMstStoreProductModelsでエラーが発生した場合はエラー原因検証済みのためそのまま返す
                var errorMessage = $"CreateMstShopPasses内の" + e.Message;
                throw new MasterDataCreateModelFailedException(errorMessage, e);
            }
            catch (Exception e)
            {
                var errorMessage = CheckCreateMstShopPassesError();
                throw new MasterDataCreateModelFailedException(errorMessage, e);
            }
        }

        string CheckCreateMstShopPassesError()
        {
            var errorMessage = string.Empty;
            var shopPassDataList = DataStore.Get<MstShopPassData>().ToList();
            var shopPassI18nDataList = DataStore.Get<MstShopPassI18nData>();
            var mstStoreProductModels = GetOrCreateModelCache(CreateMstStoreProductModels);

            var missingI18nIds = shopPassDataList
                .Where(pass => shopPassI18nDataList.All(i18n => i18n.MstShopPassId != pass.Id))
                .Select(pass => pass.Id)
                .ToList();
            if (missingI18nIds.Any())
            {
                errorMessage += $"MstShopPass：MstShopPassI18nに該当のMstShopPassIdが存在していません: {string.Join(", ", missingI18nIds)}\n";
            }

            var missingStoreProductIds = shopPassDataList
                .Where(pass => mstStoreProductModels.All(product => product.OprProductId.ToString() != pass.OprProductId))
                .Select(pass => pass.OprProductId)
                .ToList();
            if (missingStoreProductIds.Any())
            {
                errorMessage += $"MstShopPass：MstStoreProductに該当のOprProductIdが存在していません: {string.Join(", ", missingStoreProductIds)}\n";
            }

            Debug.LogError(errorMessage);
            return errorMessage;
        }

        IReadOnlyList<MstShopPassEffectModel> IMstShopProductDataRepository.GetShopPassEffects(MasterDataId mstShopPassId)
        {
            return GetOrCreateModelCache(CreateMstShopPassEffects)
                .Where(model => model.MstShopPassId == mstShopPassId)
                .ToList();
        }

        IReadOnlyList<MstShopPassEffectModel> CreateMstShopPassEffects()
        {
            return DataStore.Get<MstShopPassEffectData>()
                .Select(MstShopPassEffectDataTranslator.ToMstShopPassEffectModel)
                .ToList();
        }

        IReadOnlyList<MstShopPassRewardModel> IMstShopProductDataRepository.GetShopPassRewards(MasterDataId mstShopPassId)
        {
            return GetOrCreateModelCache(CreateMstShopPassRewards)
                .Where(model => model.MstShopPassId == mstShopPassId)
                .ToList();
        }

        IReadOnlyList<MstShopPassRewardModel> CreateMstShopPassRewards()
        {
            return DataStore.Get<MstShopPassRewardData>()
                .Select(MstShopPassRewardDataTranslator.ToMstShopPassRewardModel)
                .ToList();
        }

        IReadOnlyList<MstPackModel> IMstShopProductDataRepository.GetPacks()
        {
            try
            {
                return DataStore.Get<MstPackData>()
                    .Select(d =>
                    {
                        var i18n = DataStore.Get<MstPackI18nData>().First(i => i.MstPackId == d.Id);
                        return PackDataTranslator.ToPackModel(d, i18n);
                    })
                    .ToList();
            }
            catch (Exception e)
            {
                var errorMessage = CheckGetPacks();
                throw new MasterDataCreateModelFailedException(errorMessage, e);
            }
        }

        string CheckGetPacks()
        {
            var errorMessage = string.Empty;
            var packDataList = DataStore.Get<MstPackData>().ToList();
            var packI18nDataList = DataStore.Get<MstPackI18nData>();

            // Check for missing I18n data
            var missingI18nIds = packDataList
                .Where(pack => packI18nDataList.All(i18n => i18n.MstPackId != pack.Id))
                .Select(pack => pack.Id)
                .ToList();
            if (missingI18nIds.Any())
            {
                errorMessage += $"MstPack：MstPackI18nに該当のMstPackIdが存在していません: {string.Join(", ", missingI18nIds)}\n";
            }

            Debug.LogError(errorMessage);
            return errorMessage;
        }

        IReadOnlyList<MstPackContentModel> IMstShopProductDataRepository.GetPackContents(MasterDataId id)
        {
            return DataStore.Get<MstPackContentData>()
                .Where(d => d.MstPackId == id.Value)
                .Select(PackContentDataTranslator.ToPackContentModel)
                .ToList();
        }

        MstIdleIncentiveModel IMstIdleIncentiveRepository.GetMstIdleIncentive()
        {
            var targetData = DataStore.Get<MstIdleIncentiveData>().First();
            return MstIdleIncentiveTranslator.ToMstIdleIncentiveModel(targetData);
        }

        IReadOnlyList<MstIdleIncentiveRewardModel> IMstIdleIncentiveRepository.GetMstIncentiveRewards()
        {
            return DataStore.Get<MstIdleIncentiveRewardData>()
                .Select(MstIdleIncentiveTranslator.ToMstIdleIncentiveRewardModel)
                .ToList();
        }

        IReadOnlyList<MstIdleIncentiveItemModel> IMstIdleIncentiveRepository.GetMstIncentiveItems(MasterDataId groupId)
        {
            return DataStore.Get<MstIdleIncentiveItemData>()
                .Where(d => d.MstIdleIncentiveItemGroupId == groupId.Value)
                .Select(MstIdleIncentiveTranslator.ToMstIdleIncentiveItemModel)
                .ToList();
        }

        #region IMstMissionDataRepository
        IReadOnlyList<MstMissionDailyModel> IMstMissionDailyDataRepository.GetMstMissionDailyModels()
        {
            return GetOrCreateModelCache(CreateMstMissionDailyModels).ToList();
        }

        IReadOnlyList<MstMissionDailyModel> CreateMstMissionDailyModels()
        {
            var i18ns = DataStore.Get<MstMissionDailyI18nData>();
            return DataStore.Get<MstMissionDailyData>()
                .Join(i18ns, d => d.Id, i => i.MstMissionDailyId, (d, i) => (d, i))
                .Select(dAndI => MstMissionDataTranslator.ToMstMissionDailyModel(dAndI.d, dAndI.i))
                .ToList();
        }

        IReadOnlyList<MstMissionWeeklyModel> IMstMissionWeeklyDataRepository.GetMstMissionWeeklyModels()
        {
            return GetOrCreateModelCache(CreateMstMissionWeeklyModel).ToList();
        }

        IReadOnlyList<MstMissionWeeklyModel> CreateMstMissionWeeklyModel()
        {
            var i18ns = DataStore.Get<MstMissionWeeklyI18nData>();
            return DataStore.Get<MstMissionWeeklyData>()
                .Join(i18ns, d => d.Id, i => i.MstMissionWeeklyId, (d, i) => (d, i))
                .Select(dAndI => MstMissionDataTranslator.ToMstMissionWeeklyModel(dAndI.d, dAndI.i))
                .ToList();
        }

        IReadOnlyList<MstMissionDailyBonusModel> IMstMissionDailyDataRepository.GetMstMissionDailyBonusModels()
        {
            return GetOrCreateModelCache(MstMissionDailyBonusModels).ToList();
        }

        IReadOnlyList<MstMissionDailyBonusModel> MstMissionDailyBonusModels()
        {
            return DataStore.Get<MstMissionDailyBonusData>()
                .Select(MstMissionDataTranslator.ToMstMissionDailyBonusModel)
                .ToList();
        }

        IReadOnlyList<MstMissionAchievementModel> IMstMissionAchievementDataRepository.GetMstMissionAchievementModels()
        {
            return GetOrCreateModelCache(CreateMstMissionAchievementModels).ToList();
        }

        IReadOnlyList<MstMissionAchievementModel> CreateMstMissionAchievementModels()
        {
            var i18ns = DataStore.Get<MstMissionAchievementI18nData>();
            var dependencies = DataStore.Get<MstMissionAchievementDependencyData>();
            return DataStore.Get<MstMissionAchievementData>()
                .Join(
                    i18ns,
                    d => d.Id,
                    i => i.MstMissionAchievementId,
                    (d, i) => (d, i))
                .GroupJoin(
                    dependencies,
                    d => d.d.Id,
                    d => d.MstMissionAchievementId,
                    (dAndI, ds) => new {dAndI, d = ds.FirstOrDefault()})
                .Select(result => MstMissionDataTranslator.ToMstMissionAchievementModel(result.dAndI.d, result.dAndI.i, result.d))
                .ToList();
        }

        IReadOnlyList<MstMissionBeginnerModel> IMstMissionBeginnerDataRepository.GetMstMissionBeginnerModels()
        {
            return GetOrCreateModelCache(CreateMstMissionBeginnerModels).ToList();
        }

        IReadOnlyList<MstMissionBeginnerModel> CreateMstMissionBeginnerModels()
        {
            var i18ns = DataStore.Get<MstMissionBeginnerI18nData>();
            return DataStore.Get<MstMissionBeginnerData>()
                .Join(i18ns, d => d.Id, i => i.MstMissionBeginnerId, (d, i) => (d, i))
                .Select(dAndI => MstMissionDataTranslator.ToMstMissionBeginnerModel(dAndI.d, dAndI.i)).ToList();
        }

        IReadOnlyList<MstMissionBeginnerPromptPhraseModel> IMstMissionBeginnerDataRepository.GetMstMissionBeginnerPromptPhraseModels()
        {
            return GetOrCreateModelCache(CreateMstMissionBeginnerPromptPhraseModels).ToList();
        }

        IReadOnlyList<MstMissionBeginnerPromptPhraseModel> CreateMstMissionBeginnerPromptPhraseModels()
        {
            return DataStore.Get<MstMissionBeginnerPromptPhraseI18nData>()
                .Select(MstMissionDataTranslator.ToMstMissionBeginnerPromptPhraseModel).ToList();
        }

        IReadOnlyList<MstMissionAchievementDependencyModel> IMstMissionAchievementDataRepository.GetMstMissionAchievementDependencyModels()
        {
            return GetOrCreateModelCache(CreateMstMissionAchievementDependencyModel).ToList();
        }

        IReadOnlyList<MstMissionAchievementDependencyModel> CreateMstMissionAchievementDependencyModel()
        {
            return DataStore.Get<MstMissionAchievementDependencyData>()
                .Select(MstMissionDataTranslator.ToMstMissionAchievementDependencyModel)
                .ToList();
        }

        IReadOnlyList<MstMissionEventModel> IMstMissionEventDataRepository.GetMstMissionEventModels()
        {
            return GetOrCreateModelCache(CreateMstMissionEventModels).ToList();
        }

        IReadOnlyList<MstMissionEventModel> CreateMstMissionEventModels()
        {
            var i18ns = DataStore.Get<MstMissionEventI18nData>();
            var dependencies = DataStore.Get<MstMissionEventDependencyData>();
            return DataStore.Get<MstMissionEventData>()
                .Join(
                    i18ns,
                    d => d.Id,
                    i => i.MstMissionEventId,
                    (d, i) => (d, i))
                .GroupJoin(
                    dependencies,
                    d => d.d.Id,
                    d => d.MstMissionEventId,
                    (dAndI, ds) => new {dAndI, d = ds.FirstOrDefault()})
                .Select(result => MstMissionDataTranslator.ToMstMissionEventModel(result.dAndI.d, result.dAndI.i, result.d))
                .ToList();
        }

        IReadOnlyList<MstMissionEventDependencyModel> IMstMissionEventDataRepository.GetMstMissionEventDependencyModels()
        {
            return GetOrCreateModelCache(CreateMstMissionEventDependencyModels).ToList();
        }

        IReadOnlyList<MstMissionEventDependencyModel> CreateMstMissionEventDependencyModels()
        {
            return DataStore.Get<MstMissionEventDependencyData>()
                .Select(MstMissionDataTranslator.ToMstMissionEventDependencyModel)
                .ToList();
        }

        IReadOnlyList<MstMissionEventDailyBonusModel> IMstMissionEventDataRepository.GetMstMissionEventDailyBonusModels(
            MasterDataId mstEventDailyBonusScheduleId)
        {
            return GetOrCreateModelCache(CreateMstMissionEventDailyBonusModels)
                .Where(mst => mst.MstMissionEventDailyBonusScheduleId == mstEventDailyBonusScheduleId)
                .ToList();
        }

        IReadOnlyList<MstMissionEventDailyBonusModel> CreateMstMissionEventDailyBonusModels()
        {
            return DataStore.Get<MstMissionEventDailyBonusData>()
                .Select(MstMissionEventDailyBonusTranslator.ToMstMissionEventDailyBonusModel)
                .ToList();
        }

        MstMissionEventDailyBonusScheduleModel IMstMissionEventDataRepository.GetMstMissionEventDailyBonusScheduleModelFirstOrDefault(
            MasterDataId mstEventId)
        {
            return GetOrCreateModelCache(CreateMstMissionEventDailyBonusScheduleModels)
                .FirstOrDefault(d => d.MstEventId == mstEventId, MstMissionEventDailyBonusScheduleModel.Empty);
        }

        IReadOnlyList<MstMissionLimitedTermModel> IMstMissionLimitedDataRepository.GetMstMissionLimitedTermModels()
        {
            return GetOrCreateModelCache(CreateMstMissionLimitedTermModels).ToList();
        }

        IReadOnlyList<MstMissionLimitedTermModel> CreateMstMissionLimitedTermModels()
        {
            var i18nList = DataStore.Get<MstMissionLimitedTermI18nData>();
            var dependencies = DataStore.Get<MstMissionLimitedTermDependencyData>();
            return DataStore.Get<MstMissionLimitedTermData>()
                .Join(
                    i18nList,
                    d => d.Id,
                    i => i.MstMissionLimitedTermId,
                    (d, i) => (d, i))
                .GroupJoin(
                    dependencies,
                    d => d.d.Id,
                    d => d.MstMissionLimitedTermId,
                    (dAndI, ds) => new {dAndI, d = ds.FirstOrDefault()})
                .Select(result => MstMissionDataTranslator.ToMstMissionLimitedTermModel(result.dAndI.d, result.dAndI.i, result.d))
                .ToList();
        }

        IReadOnlyList<MstMissionLimitedTermDependencyModel> IMstMissionLimitedDataRepository.GetMstMissionLimitedTermDependencyModels()
        {
            return GetOrCreateModelCache(CreateMstMissionLimitedTermDependencyModels).ToList();
        }

        IReadOnlyList<MstMissionLimitedTermDependencyModel> CreateMstMissionLimitedTermDependencyModels()
        {
            return DataStore.Get<MstMissionLimitedTermDependencyData>()
                .Select(MstMissionDataTranslator.ToMstMissionLimitedTermDependencyModel)
                .ToList();
        }

        IReadOnlyList<MstMissionEventDailyBonusScheduleModel> CreateMstMissionEventDailyBonusScheduleModels()
        {
            return DataStore.Get<MstMissionEventDailyBonusScheduleData>()
                .Select(MstMissionEventDailyBonusTranslator.ToMstMissionEventDailyBonusScheduleModel)
                .ToList();
        }

        IReadOnlyList<MstMissionRewardModel> IMstMissionRewardDataRepository.GetMissionRewardModelList(MasterDataId groupId)
        {
            return GetOrCreateModelCache(CreateMissionRewardModels)
                .Where(mst => mst.GroupId == groupId)
                .ToList();
        }

        IReadOnlyList<MstMissionRewardModel> CreateMissionRewardModels()
        {
            return DataStore.Get<MstMissionRewardData>()
                .Select(MstMissionRewardTranslator.ToMMissionRewardModel)
                .ToList();
        }


        IReadOnlyList<MstComebackBonusModel> IMstComebackBonusDataRepository.GetMstComebackBonusModels(MasterDataId mstComebackBonusScheduleId)
        {
            return GetOrCreateModelCache(CreateMstComebackBonusModels)
                .Where(model => model.MstComebackBonusScheduleId == mstComebackBonusScheduleId)
                .ToList();
        }

        IReadOnlyList<MstComebackBonusModel> CreateMstComebackBonusModels()
        {
            return DataStore.Get<MstComebackBonusData>()
                .Select(MstComebackBonusDataTranslator.ToMstComebackBonusModel)
                .ToList();
        }

        MstComebackBonusScheduleModel IMstComebackBonusDataRepository.GetMstComebackBonusScheduleModelFirstOrDefault(MasterDataId mstComebackBonusScheduleId)
        {
            return GetOrCreateModelCache(CreateMstComebackBonusScheduleModels)
                .FirstOrDefault(
                    model => model.MstComebackBonusScheduleId == mstComebackBonusScheduleId,
                    MstComebackBonusScheduleModel.Empty);
        }

        IReadOnlyList<MstComebackBonusScheduleModel> CreateMstComebackBonusScheduleModels()
        {
            return DataStore.Get<MstComebackBonusScheduleData>()
                .Select(MstComebackBonusScheduleDataTranslator.ToMstComebackBonusModel)
                .ToList();
        }

        MstDailyBonusRewardModel IMstComebackBonusDataRepository.GetMstDailyBonusRewardModelFirstOrDefault(MasterDataId mstDailyBonusRewardGroupId)
        {
            return GetOrCreateModelCache(CreateMstDailyBonusRewardModels)
                .FirstOrDefault(
                    model => model.GroupId == mstDailyBonusRewardGroupId,
                    MstDailyBonusRewardModel.Empty);
        }

        IReadOnlyList<MstDailyBonusRewardModel> CreateMstDailyBonusRewardModels()
        {
            return DataStore.Get<MstDailyBonusRewardData>()
                .Select(MstDailyBonusRewardDataTranslator.ToMstDailyBonusRewardModel)
                .ToList();
        }
        #endregion

        MstConfigModel IMstConfigRepository.GetConfig(MstConfigKey key)
        {
            var mstConfigData = DataStore.Get<MstConfigData>()
                .FirstOrDefault(mst => mst.Key == key.Value);
            if (null == mstConfigData) return MstConfigModel.Empty;

            return new MstConfigModel(new MstConfigKey(mstConfigData.Key), new MstConfigValue(mstConfigData.Value));
        }

        IReadOnlyList<MstArtworkModel> IMstArtworkDataRepository.GetArtworks()
        {
            return DataStore.Get<MstArtworkData>()
                .Select(d =>
                {
                    var i18n = DataStore.Get<MstArtworkI18nData>().First(i => i.MstArtworkId == d.Id);
                    return MstArtworkDataTranslator.Translate(d, i18n);
                })
                .ToList();
        }

        IReadOnlyList<MstArtworkModel> IMstArtworkDataRepository.GetSeriesArtwork(MasterDataId mstSeriesId)
        {
            return DataStore.Get<MstArtworkData>()
                .Where(mst => mst.MstSeriesId == mstSeriesId.Value)
                .Select(d =>
                {
                    var i18n = DataStore.Get<MstArtworkI18nData>().First(i => i.MstArtworkId == d.Id);
                    return MstArtworkDataTranslator.Translate(d, i18n);
                })
                .ToList();
        }

        MstArtworkModel IMstArtworkDataRepository.GetArtwork(MasterDataId key)
        {
            var mst = DataStore.Get<MstArtworkData>()
                .Find(mst => mst.Id == key.Value);
            var i18n = DataStore.Get<MstArtworkI18nData>()
                .Find(i18n => i18n.MstArtworkId == mst.Id);

            return MstArtworkDataTranslator.Translate(mst, i18n);
        }

#if GLOW_DEBUG
        void IMstCharacterDataRepository.RefreshCharacterModelCache(List<MstCharacterModel> mstDebugCharacterModels)
        {
            RemoveModelCache<MstCharacterModel>();
            GetOrCreateModelCache(() => CreateDebugMstCharacterModels(mstDebugCharacterModels));
        }

        IReadOnlyList<MstCharacterModel> CreateDebugMstCharacterModels(IReadOnlyList<MstCharacterModel> mstDebugCharacterModels)
        {
            var models = CreateMstCharacterModels().ToList();
            models.AddRange(mstDebugCharacterModels);
            return models;
        }

        void IMstAutoPlayerSequenceRepository.RefreshSequenceElementModelCache(IReadOnlyList<MstAutoPlayerSequenceElementModel> debugModels)
        {
            RemoveModelCache<MstAutoPlayerSequenceElementModel>();
            GetOrCreateModelCache(() => CreateDebugMstAutoPlayerSequenceElementModels(debugModels));
        }

        void IMstAutoPlayerSequenceRepository.AddEnemyStageParameterModel(
            MstEnemyStageParameterModel mstDebugEnemyStageParameterModel)
        {
            _mstEnemyStageParameterModelDictionary[mstDebugEnemyStageParameterModel.Id] =
                mstDebugEnemyStageParameterModel;
        }

        IReadOnlyList<MstAutoPlayerSequenceElementModel> CreateDebugMstAutoPlayerSequenceElementModels(IReadOnlyList<MstAutoPlayerSequenceElementModel> debugModels)
        {
            // デバッグを行うときは、デバッグ用のオートプレイヤーシーケンス要素モデルを追加する
            var models = CreateMstAutoPlayerSequenceElementModels().ToList();
            models.AddRange(debugModels);
            return models;
        }
#endif

        #region IMstArtworkFragmentDataRepository

        IReadOnlyList<MstArtworkFragmentModel> IMstArtworkFragmentDataRepository.GetArtworkFragmentModels()
        {
            return GetOrCreateModelCache(CreateMstArtworkFragmentModels).ToList();
        }

        IReadOnlyList<MstArtworkFragmentModel> IMstArtworkFragmentDataRepository.GetArtworkFragments(
            MasterDataId artworkId)
        {
            return GetOrCreateModelCache(CreateMstArtworkFragmentModels)
                .Where(mst => mst.MstArtworkId == artworkId)
                .ToList();
        }

        MstArtworkFragmentModel IMstArtworkFragmentDataRepository.GetArtworkFragment(MasterDataId artworkFragmentId)
        {
            return GetOrCreateModelCache(CreateMstArtworkFragmentModels).First(m => m.Id == artworkFragmentId);
        }

        IReadOnlyList<MstArtworkFragmentModel> IMstArtworkFragmentDataRepository.GetDropGroupArtworkFragments(
            MasterDataId dropGroupId)
        {
            return GetOrCreateModelCache(CreateMstArtworkFragmentModels)
                .Where(mst => mst.MstDropGroupId == dropGroupId)
                .ToList();
        }
        IReadOnlyList<IGrouping<MasterDataId,MstArtworkFragmentModel>> IMstArtworkFragmentDataRepository.GetArtworkFragmentsGroupByMstDropGroupId()
        {
            return GetOrCreateModelCache(CreateMstArtworkFragmentModelsGroupByDropGroupId).ToList();
        }
        IReadOnlyList<MstArtworkFragmentModel> CreateMstArtworkFragmentModels()
        {
            var i18ns = DataStore.Get<MstArtworkFragmentI18nData>();
            var poss = DataStore.Get<MstArtworkFragmentPositionData>();

            return DataStore.Get<MstArtworkFragmentData>()
                .Join(
                    i18ns,
                    d => d.Id,
                    i => i.MstArtworkFragmentId,
                    (d, i18n) => (d, i18n))
                .Join(
                    poss,
                    dataAndI18n => dataAndI18n.d.Id,
                    pos => pos.MstArtworkFragmentId,
                    (dataAndI18n, pos) => (dataAndI18n.d, dataAndI18n.i18n, pos))
                .Select(dataAndI18nAndPos => MstArtworkFragmentDataTranslator.Translate(
                    dataAndI18nAndPos.d,
                    dataAndI18nAndPos.i18n,
                    dataAndI18nAndPos.pos))
                .OrderBy(mst => mst.AssetNum.Value)
                .ToList();
        }

        IReadOnlyList<IGrouping<MasterDataId,MstArtworkFragmentModel>> CreateMstArtworkFragmentModelsGroupByDropGroupId()
        {
            return GetOrCreateModelCache(CreateMstArtworkFragmentModels)
                .GroupBy(mst => mst.MstDropGroupId)
                .ToList();
        }
        #endregion

        #region IMstEmblemRepository
        MstEmblemModel IMstEmblemRepository.GetMstEmblemFirstOrDefault(MasterDataId mstEmblemId)
        {
            // 運用想定でマスターから内容が削除される可能性があるためFirstOrDefaultにしている
            return GetOrCreateModelCache(CreateMstEmblems)
                .FirstOrDefault(mst => mst.Id == mstEmblemId, MstEmblemModel.Empty);
        }

        IReadOnlyList<MstEmblemModel> IMstEmblemRepository.GetSeriesEmblems(MasterDataId mstSeriesId)
        {
            return GetOrCreateModelCache(CreateMstEmblems)
                .Where(mst => mst.MstSeriesId == mstSeriesId)
                .ToList();

        }

        IReadOnlyList<MstEmblemModel> IMstEmblemRepository.GetMstEmblems()
        {
            return GetOrCreateModelCache(CreateMstEmblems).ToList();
        }

        IReadOnlyList<MstEmblemModel> CreateMstEmblems()
        {
            var mstEmblemI18Ns = DataStore.Get<MstEmblemI18nData>();
            return DataStore.Get<MstEmblemData>()
                .Join(mstEmblemI18Ns, mst => mst.Id, i18n => i18n.MstEmblemId, (mst, i18n) => (mst, i18n))
                .Select(dataAndI18n => MstEmblemDataTranslator.Translate(dataAndI18n.mst, dataAndI18n.i18n))
                .ToList();
        }
        #endregion

        #region IMstMangaAnimationDataRepository
        IReadOnlyList<MstMangaAnimationModel> IMstMangaAnimationDataRepository.GetMangaAnimationsByStageId(MasterDataId id)
        {
            return GetOrCreateModelCache(CreateMstMangaAnimationModels)
                .Where(m => m.MstStageId == id)
                .ToList();
        }

        IReadOnlyList<MstMangaAnimationModel> CreateMstMangaAnimationModels()
        {
            return DataStore.Get<MstMangaAnimationData>()
                .Select(MstMangaAnimationDataTranslator.ToMangaAnimationModel)
                .ToList();
        }

        #endregion

        #region IMstUnitEncyclopediaRewardDataRepository
        IReadOnlyList<MstUnitEncyclopediaRewardModel> IMstUnitEncyclopediaRewardDataRepository.
            GetUnitEncyclopediaRewards()
        {
            return GetOrCreateModelCache(CreateMstUnitEncyclopediaRewardModels).ToList();
        }

        IReadOnlyList<MstUnitEncyclopediaRewardModel> CreateMstUnitEncyclopediaRewardModels()
        {
            return DataStore.Get<MstUnitEncyclopediaRewardData>()
                .Select(MstUnitEncyclopediaRewardDataTranslator.ToUnitEncyclopediaRewardModel)
                .ToList();
        }

        #endregion

        #region IMstUnitEncyclopediaEffectDataRepository
        MstUnitEncyclopediaEffectModel IMstUnitEncyclopediaEffectDataRepository.GetUnitEncyclopediaEffect(
            MasterDataId mstUnitEncyclopediaRewardId)
        {
            try
            {
                return GetOrCreateModelCache(CreateMstUnitEncyclopediaEffectModels)
                    .First(m => m.MstUnitEncyclopediaRewardId == mstUnitEncyclopediaRewardId);
            }
            catch (Exception e)
            {
                var message = $"MasterData not found. id: {mstUnitEncyclopediaRewardId} . Target is...\n";
                message += $"{(typeof(MstUnitEncyclopediaEffectData))} \n";
                throw new MasterDataCreateModelFailedException(message, e);
            }
        }

        IReadOnlyList<MstUnitEncyclopediaEffectModel> IMstUnitEncyclopediaEffectDataRepository.
            GetUnitEncyclopediaEffects()
        {
            return GetOrCreateModelCache(CreateMstUnitEncyclopediaEffectModels).ToList();
        }

        IReadOnlyList<MstUnitEncyclopediaEffectModel> CreateMstUnitEncyclopediaEffectModels()
        {
            return DataStore.Get<MstUnitEncyclopediaEffectData>()
                .Select(MstUnitEncyclopediaEffectDataTranslator.ToUnitEncyclopediaEffectModel)
                .ToList();
        }

        #endregion

        #region IOprGachaRepository
        IReadOnlyList<OprGachaModel> IOprGachaRepository.GetOprGachaModelsByDataTime(DateTimeOffset dateTime)
        {
            return GetOrCreateModelCache(CreateOprGachaModels)
                .Where(m => CalculateTimeCalculator.IsValidTime(dateTime, m.StartAt, m.EndAt))
                .ToList();
        }
        OprGachaModel IOprGachaRepository.GetOprGachaModelFirstOrDefaultById(MasterDataId gachaId)
        {
            return GetOrCreateModelCache(CreateOprGachaModels)
                .FirstOrDefault(m => m.GachaId == gachaId, OprGachaModel.Empty);
        }

        IReadOnlyList<OprGachaModel> CreateOprGachaModels()
        {
            var gachaI18nData = DataStore.Get<OprGachaI18nData>();
            return DataStore.Get<OprGachaData>()
                .Join(gachaI18nData, data => data.Id, i18n => i18n.OprGachaId, (data, i18n) => (data, i18n))
                .Select(dataAndI18n => OprGachaDataTranslator.Translate(dataAndI18n.data, dataAndI18n.i18n))
                .ToList();
        }

        IReadOnlyList<OprGachaDisplayUnitI18nModel> IOprGachaRepository.GetOprGachaDisplayUnitI18nModelsById(MasterDataId gachaId)
        {
            return GetOrCreateModelCache(CreateOprGachaDisplayUnitI18nModels)
                .Where(m => m.OprGachaId == gachaId)
                .ToList();
        }
        IReadOnlyList<OprGachaDisplayUnitI18nModel> IOprGachaRepository.GetOprGachaDisplayUnitI18nModels()
        {
            return GetOrCreateModelCache(CreateOprGachaDisplayUnitI18nModels)
                .ToList();
        }

        IReadOnlyList<OprGachaDisplayUnitI18nModel> CreateOprGachaDisplayUnitI18nModels()
        {
            return DataStore.Get<OprGachaDisplayUnitI18nData>()
                .Select(OprGachaI18nDataTranslator.Translate)
                .ToList();
        }

        #endregion

        #region IOprGachaUpperRepository
        IReadOnlyList<OprDrawCountThresholdModel> IOprGachaUpperRepository.FindByDrawCountThresholdGroupId(
            DrawCountThresholdGroupId drawCountThresholdGroupId)
        {
            return GetOrCreateModelCache(CreateOprDrawCountThresholdModels)
                .Where(m => m.DrawCountThresholdGroupId == drawCountThresholdGroupId)
                .ToList();
        }

        IReadOnlyList<OprDrawCountThresholdModel> CreateOprDrawCountThresholdModels()
        {
            return DataStore.Get<OprGachaUpperData>()
                .Select(OprGachaUpperDataTranslator.Translate)
                .ToList();
        }

        #endregion

        #region IOprGachaUseResourceRepository
        IReadOnlyList<OprGachaUseResourceModel> IOprGachaUseResourceRepository.FindByGachaId(MasterDataId gachaId)
        {
            return GetOrCreateModelCache(CreateOprGachaUseResourceModels)
                .Where(m => m.OprGachaId == gachaId)
                .ToList();
        }

        IReadOnlyList<OprGachaUseResourceModel> IOprGachaUseResourceRepository.GetOprGachaUseResourceModelsByItemId(
            MasterDataId mstCostId)
        {
            return GetOrCreateModelCache(CreateOprGachaUseResourceModels)
                .Where(m => m.MstCostId == mstCostId)
                .ToList();
        }

        IReadOnlyList<OprGachaUseResourceModel> CreateOprGachaUseResourceModels()
        {
            return DataStore.Get<OprGachaUseResourceData>()
                .Select(OprGachaUseResourceDataTranslator.Translate)
                .ToList();
        }

        #endregion

        #region IMstEventDataRepository
        MstEventModel IMstEventDataRepository.GetEvent(MasterDataId id)
        {
            try
            {
                return GetOrCreateModelCache(CreateMstEventModel).First(m => m.Id == id);
            }
            catch (Exception e)
            {
                var message = $"MasterData not found. id: {id} . Target is...\n";
                message += $"{(typeof(MstEventData))} \n";
                message += $"{(typeof(MstEventI18nData))} \n";
                throw new MasterDataCreateModelFailedException(message, e);
            }
        }

        MstEventModel IMstEventDataRepository.GetEventFirstOrDefault(MasterDataId mstEventId)
        {
            return GetOrCreateModelCache(CreateMstEventModel)
                .FirstOrDefault(m => m.Id == mstEventId, MstEventModel.Empty);
        }

        IReadOnlyList<MstEventModel> IMstEventDataRepository.GetEvents()
        {
            return GetOrCreateModelCache(CreateMstEventModel).ToList();
        }

        IReadOnlyList<MstEventModel> CreateMstEventModel()
        {
            var i18ns = DataStore.Get<MstEventI18nData>();
            return DataStore.Get<MstEventData>()
                .Join(i18ns, d => d.Id, i => i.MstEventId, (d, i) => (d, i))
                .Select(dAndI => MstEventTranslator.Translate(dAndI.d, dAndI.i))
                .ToList();
        }

        IReadOnlyList<MstEventDisplayRewardModel> IMstEventDataRepository.GetEventDisplayRewards()
        {
            return GetOrCreateModelCache(CreateMstEventDisplayRewardModel).ToList();
        }

        IReadOnlyList<MstEventDisplayRewardModel> CreateMstEventDisplayRewardModel()
        {
            return DataStore.Get<MstEventDisplayRewardData>()
                .Select(MstEventDisplayRewardDataTranslator.Translate)
                .ToList();
        }

        #endregion

        #region IMstResultTipsDataRepository
        MstResultTipsModel IMstResultTipsDataRepository.GetMstResultTipsFirstOrDefault(UserLevel userLevel)
        {
            return GetOrCreateModelCache(CreateMstResultTipsModels).FirstOrDefault(
                mst => mst.UserLevel >= userLevel,
                MstResultTipsModel.Empty);
        }

        IReadOnlyList<MstResultTipsModel> CreateMstResultTipsModels()
        {
            // レベル順保証
            return DataStore.Get<MstResultTipI18nData>()
                .OrderBy(mst => mst.UserLevel)
                .Select(MstResultTipsI18nDataTranslator.Translate)
                .ToList();
        }
        #endregion

        #region IMstStageEventSettingDataRepository
        IReadOnlyList<MstStageEventSettingModel> IMstStageEventSettingDataRepository.GetStageEventSettings()
        {
            return GetOrCreateModelCache(CreateMstStageEventSettingModels).ToList();
        }

        MstStageEventSettingModel IMstStageEventSettingDataRepository.GetStageEventSettingFirstOrDefault(MasterDataId mstStageId)
        {
            return GetOrCreateModelCache(CreateMstStageEventSettingModels)
                .FirstOrDefault(m => m.MstStageId == mstStageId, MstStageEventSettingModel.Empty);
        }

        IReadOnlyList<MstStageEventSettingModel> CreateMstStageEventSettingModels()
        {
            return DataStore.Get<MstStageEventSettingData>()
                .Select(MstStageEventSettingModelTranslator.Translate)
                .ToList();
        }
        #endregion

        #region IMstStageEventRuleDataRepository
        IReadOnlyList<MstInGameSpecialRuleModel> IMstInGameSpecialRuleDataRepository.GetInGameSpecialRuleModels(
            MasterDataId specialRuleTargetMstId,
            InGameContentType specialRuleContentType)
        {
            return GetOrCreateModelCache(CreateMstInGameSpecialRuleModels)
                .Where(d => d.TargetId == specialRuleTargetMstId)
                .Where(d => d.ContentType == specialRuleContentType)
                .ToList();
        }

        IReadOnlyList<MstInGameSpecialRuleModel> CreateMstInGameSpecialRuleModels()
        {
            return DataStore.Get<MstInGameSpecialRuleData>()
                .Select(InGameSpecialRuleDataTranslator.ToInGameSpecialRuleModel)
                .ToList();
        }
        #endregion

        #region MstInGameSpecialRuleUnitStatus
        IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> IMstInGameSpecialRuleUnitStatusDataRepository.GetInGameSpecialRuleUnitStatusModels(
            MasterDataId groupId)
        {
            return GetOrCreateModelCache(CreateMstInGameSpecialRuleUnitStatusModels)
                .Where(d => d.GroupId == groupId)
                .ToList();
        }

        IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> IMstInGameSpecialRuleUnitStatusDataRepository.GetInGameSpecialRuleUnitStatusModels(
            IReadOnlyList<MasterDataId> groupIdList)
        {
            return GetOrCreateModelCache(CreateMstInGameSpecialRuleUnitStatusModels)
                .Where(d => groupIdList.Contains(d.GroupId))
                .ToList();
        }

        IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> CreateMstInGameSpecialRuleUnitStatusModels()
        {
            return DataStore.Get<MstInGameSpecialRuleUnitStatusData>()
                .Select(InGameSpecialRuleUnitStatusDataTranslator.ToInGameSpecialRuleUnitStatusModel)
                .ToList();
        }

        #endregion

        # region IMstStageClearTimeRewardRepository
        IReadOnlyList<MstStageClearTimeRewardModel> IMstStageClearTimeRewardRepository.GetClearTimeRewards(
            MasterDataId mstStageId)
        {
            return GetOrCreateModelCache(CreateMstStageClearTimeRewardModels)
                .Where(mst => mst.MstStageId == mstStageId)
                .ToList();
        }

        IReadOnlyList<MstStageClearTimeRewardModel> CreateMstStageClearTimeRewardModels()
        {
            return DataStore.Get<MstStageClearTimeRewardData>()
                .Select(MstStageClearTimeRewardDataTranslator.Translate)
                .ToList();
        }

        #endregion

        #region IMstAbilityDescriptionDataRepository
        IReadOnlyList<MstAbilityDescriptionModel> IMstAbilityDescriptionDataRepository.GetAbilityDescriptionModels()
        {
            return GetOrCreateModelCache(CreateMstAbilityDescriptionModels).ToList();
        }

        IReadOnlyList<MstAbilityDescriptionModel> CreateMstAbilityDescriptionModels()
        {
            var abilityI18NData = DataStore.Get<MstAbilityI18nData>();
            return DataStore.Get<MstAbilityData>()
                .Join(abilityI18NData, ability => ability.Id, i18n => i18n.MstAbilityId, (ability, i18n) => (ability, i18n))
                .Select(dataAndI18n => MstAbilityI18nDataTranslator.Translate(dataAndI18n.ability, dataAndI18n.i18n))
                .ToList();
        }

        #endregion

        #region IMstPartyUnitCountDataRepository
        IReadOnlyList<MstPartyUnitCountModel> IMstPartyUnitCountDataRepository.GetPartyUnitCounts()
        {
            return GetOrCreateModelCache(CreateMstPartyUnitCountModels).ToList();
        }

        IReadOnlyList<MstPartyUnitCountModel> CreateMstPartyUnitCountModels()
        {
            return DataStore.Get<MstPartyUnitCountData>()
                .Select(MstPartyUnitCountDataTranslator.ToMstPartyUnitCountModel)
                .ToList();
        }
        #endregion

        #region IMstEventBonusUnitDataRepository
        IReadOnlyList<MstEventBonusUnitModel> IMstEventBonusUnitDataRepository.GetEventBonuses(
            EventBonusGroupId groupId)
        {
            return GetOrCreateModelCache(CreateMstEventBonusUnitModels)
                .Where(m => m.EventBonusGroupId == groupId)
                .ToList();
        }

        IReadOnlyList<MstEventBonusUnitModel> CreateMstEventBonusUnitModels()
        {
            return DataStore.Get<MstEventBonusUnitData>()
                .Select(MstEventBonusUnitDataTranslator.Translate)
                .ToList();
        }
        #endregion

        #region IMstQuestEventBonusScheduleDataRepository
        IReadOnlyList<MstQuestEventBonusScheduleModel> IMstQuestEventBonusScheduleDataRepository.
            GetQuestEventBonusSchedules(MasterDataId mstQuestId)
        {
            return GetOrCreateModelCache(CreateMstQuestEventBonusScheduleModels)
                .Where(m => m.MstQuestId == mstQuestId)
                .ToList();
        }

        IReadOnlyList<MstQuestEventBonusScheduleModel> IMstQuestEventBonusScheduleDataRepository.
            GetQuestEventBonusSchedules(EventBonusGroupId groupId)
        {
            return GetOrCreateModelCache(CreateMstQuestEventBonusScheduleModels)
                .Where(m => m.EventBonusGroupId == groupId)
                .ToList();
        }

        IReadOnlyList<MstQuestEventBonusScheduleModel> CreateMstQuestEventBonusScheduleModels()
        {
            return DataStore.Get<MstQuestEventBonusScheduleData>()
                .Select(MstQuestEventBonusScheduleDataTranslator.Translate)
                .ToList();
        }
        #endregion

        #region IMstQuestBonusUnitRepository
        IReadOnlyList<MstQuestBonusUnitModel> IMstQuestBonusUnitRepository.GetQuestBonusUnits(MasterDataId mstQuestId)
        {
            return GetOrCreateModelCache(CreateMstQuestBonusUnitModels)
                .Where(m => m.MstQuestId == mstQuestId)
                .ToList();
        }
        IReadOnlyList<MstQuestBonusUnitModel> CreateMstQuestBonusUnitModels()
        {
            return DataStore.Get<MstQuestBonusUnitData>()
                .Select(MstQuestBonusUnitDataTranslator.Translate)
                .ToList();
        }
        #endregion

        #region IMstAdventBattleDataRepository
        IReadOnlyList<MstAdventBattleModel> IMstAdventBattleDataRepository.GetMstAdventBattleModels()
        {
            return GetOrCreateModelCache(CreateMstAdventBattleModels).ToList();
        }

        MstAdventBattleModel IMstAdventBattleDataRepository.GetMstAdventBattleModel(MasterDataId mstAdventBattleId)
        {
            try
            {
                return GetOrCreateModelCache(CreateMstAdventBattleModels)
                    .First(mst => mst.Id == mstAdventBattleId);
            }
            catch (Exception e)
            {
                var message = $"MasterData not found. id: {mstAdventBattleId} . Target is...\n";
                message += $"{(typeof(MstAdventBattleData))} \n";
                message += $"{(typeof(MstInGameData))} \n";
                message += $"{(typeof(MstInGameI18nData))} \n";
                throw new MasterDataCreateModelFailedException(message, e);
            }
        }

        MstAdventBattleModel IMstAdventBattleDataRepository.GetMstAdventBattleModelFirstOrDefault(MasterDataId mstAdventBattleId)
        {
            return GetOrCreateModelCache(CreateMstAdventBattleModels)
                .FirstOrDefault(mst => mst.Id == mstAdventBattleId, MstAdventBattleModel.Empty);
        }
        IReadOnlyList<MstAdventBattleModel> CreateMstAdventBattleModels()
        {
            var datas = DataStore.Get<MstAdventBattleData>();
            var inGameDatas = DataStore.Get<MstInGameData>();
            var inGameI18nDatas = DataStore.Get<MstInGameI18nData>();
            var i18nData = DataStore.Get<MstAdventBattleI18nData>();

            return datas
                .Join(
                    inGameDatas,
                    datas => datas.MstInGameId,
                    inGame => inGame.Id,
                    (advent, inGameData) => (advent, inGameData))
                .Join(
                    inGameI18nDatas,
                    d => d.inGameData.Id,
                    i => i.MstInGameId,
                    (d, inGameI18n) => (d.advent, d.inGameData, inGameI18n))
                .Join(
                    i18nData,
                    d => d.advent.Id,
                    i => i.MstAdventBattleId,
                    (d, i18n) => (d.advent, d.inGameData, d.inGameI18n, i18n))
                .Select(data => MstAdventBattleModelTranslator.ToMstAdventBattleModel(
                    data.advent,
                    data.inGameData,
                    data.inGameI18n, data.i18n))
                .ToList();
        }

        IReadOnlyList<MstAdventBattleRewardGroupModel> IMstAdventBattleDataRepository.GetMstAdventBattleRewardGroups(
            MasterDataId mstAdventBattleId)
        {
            return GetOrCreateModelCache(CreateMstAdventBattleRewardGroupModels)
                .Where(m => m.MstAdventBattleId == mstAdventBattleId)
                .ToList();
        }

        IReadOnlyList<MstAdventBattleRewardGroupModel> CreateMstAdventBattleRewardGroupModels()
        {
            var rewardGrouped = DataStore.Get<MstAdventBattleRewardData>()
                .GroupBy(data => data.MstAdventBattleRewardGroupId, data => data);

            return DataStore.Get<MstAdventBattleRewardGroupData>()
                .GroupJoin(
                    rewardGrouped,
                    group => group.Id,
                    data => data.Key,
                    (group, data) => new { group, rewards = data.FirstOrDefault() }
                    )
                .Select(data =>
                {
                    var rewards = data.rewards != null ? data.rewards.ToList() : new List<MstAdventBattleRewardData>();
                    return MstAdventBattleModelTranslator.ToMstAdventBattleRewardGroupModel(data.group, rewards);
                })
                .ToList();
        }

        IReadOnlyList<MstAdventBattleScoreRankModel> IMstAdventBattleDataRepository.GetMstAdventBattleScoreRanks(
            MasterDataId mstAdventBattleId)
        {
            return GetOrCreateModelCache(CreateMstAdventBattleScoreRankModels)
                .Where(m => m.MstAdventBattleId == mstAdventBattleId)
                .ToList();
        }

        MstAdventBattleScoreRankModel IMstAdventBattleDataRepository.GetMstAdventBattleScoreRank(
            MasterDataId mstAdventBattleScoreRankId)
        {
            return GetOrCreateModelCache(CreateMstAdventBattleScoreRankModels)
                .FirstOrDefault(m => m.Id == mstAdventBattleScoreRankId, MstAdventBattleScoreRankModel.Empty);
        }

        IReadOnlyList<MstAdventBattleScoreRankModel> CreateMstAdventBattleScoreRankModels()
        {
            return DataStore.Get<MstAdventBattleRankData>()
                .Select(MstAdventBattleModelTranslator.ToMstAdventBattleScoreRankModel)
                .ToList();
        }

        IReadOnlyList<MstAdventBattleClearRewardModel> IMstAdventBattleDataRepository.GetMstAdventBattleClearRewardModels(
            MasterDataId mstAdventBattleId)
        {
            return GetOrCreateModelCache(CreateMstAdventBattleClearRewardModels)
                .Where(m => m.MstAdventBattleId == mstAdventBattleId)
                .ToList();
        }

        IReadOnlyList<MstAdventBattleClearRewardModel> CreateMstAdventBattleClearRewardModels()
        {
            return DataStore.Get<MstAdventBattleClearRewardData>()
                .Select(MstAdventBattleClearRewardModelTranslator.ToMstAdventBattleClearRewardModel)
                .ToList();
        }

        #endregion

        #region IMstHomeBannerRepository
        IReadOnlyList<MstHomeBannerModel> IMstHomeBannerRepository.GetMstHomeBanners()
        {
            return GetOrCreateModelCache(CreateMstHomeBannerModels).ToList();
        }

        IReadOnlyList<MstHomeBannerModel> CreateMstHomeBannerModels()
        {
            return DataStore.Get<MstHomeBannerData>()
                .Select(MstHomeBannerTranslator.Translate)
                .ToList();
        }
        #endregion

        #region IMstItemRarityTradeRepository
        IReadOnlyList<MstItemRarityTradeModel> IMstItemRarityTradeRepository.GetMstItemRarityTradeList()
        {
            return GetOrCreateModelCache(CreateMstItemRarityTradeModels).ToList();
        }

        IReadOnlyList<MstItemRarityTradeModel> CreateMstItemRarityTradeModels()
        {
            return DataStore.Get<MstItemRarityTradeData>()
                .Select(MstItemRarityTradeModelTranslator.ToItemRarityTradeModel)
                .ToList();
        }

        #endregion

        #region IMstAutoPlayerSequenceRepository
        MstAutoPlayerSequenceModel IMstAutoPlayerSequenceRepository.GetMstAutoPlayerSequence(AutoPlayerSequenceSetId mstAutoPlayerSequenceSetId)
        {
            var elements = GetOrCreateModelCache(CreateMstAutoPlayerSequenceElementModels)
                .Where(m => m.SequenceSetId == mstAutoPlayerSequenceSetId)
                .ToList();

            return MstAutoPlayerSequenceDataTranslator.Translate(mstAutoPlayerSequenceSetId, elements);
        }

        IReadOnlyList<MstAutoPlayerSequenceElementModel> CreateMstAutoPlayerSequenceElementModels()
        {
            var mstAutoPlayerSequenceElementModels = DataStore.Get<MstAutoPlayerSequenceData>()
                .Select(MstAutoPlayerSequenceDataTranslator.TranslateToElement)
                .ToList();

            return mstAutoPlayerSequenceElementModels;
        }

        #endregion

        #region IMstDefenseTargetRepository

        MstDefenseTargetModel IMstDefenseTargetDataRepository.GetMstDefenseTargetModel(MasterDataId id)
        {
            try
            {
                return GetOrCreateModelCache(CreateMstDefenseTargetModels).First(m => m.Id == id);
            }
            catch (Exception e)
            {
                var message = $"MasterData not found. id: {id} . Target is...\n";
                message += $"{(typeof(MstDefenseTargetData))} \n";
                throw new MasterDataCreateModelFailedException(message, e);
            }
        }

        IReadOnlyList<MstDefenseTargetModel> IMstDefenseTargetDataRepository.GetMstDefenseTargetModels()
        {
            return GetOrCreateModelCache(CreateMstDefenseTargetModels).ToList();
        }

        IReadOnlyList<MstDefenseTargetModel> CreateMstDefenseTargetModels()
        {
            return DataStore.Get<MstDefenseTargetData>()
                .Select(DefenseTargetDataTranslator.Translate)
                .ToList();
        }

        #endregion

        #region IMstInGameGimmickObjectRepository

        MstInGameGimmickObjectModel IMstInGameGimmickObjectDataRepository.GetMstInGameGimmickObjectModel(MasterDataId id)
        {
            try
            {
                return GetOrCreateModelCache(CreateMstInGameGimmickObjectModels).First(m => m.Id == id);
            }
            catch (Exception e)
            {
                var message = $"MasterData not found. id: {id} . Target is...\n";
                message += $"{(typeof(MstInGameGimmickObjectData))} \n";
                throw new MasterDataCreateModelFailedException(message, e);
            }
        }

        IReadOnlyList<MstInGameGimmickObjectModel> IMstInGameGimmickObjectDataRepository.GetMstInGameGimmickObjectModels()
        {
            return GetOrCreateModelCache(CreateMstInGameGimmickObjectModels).ToList();
        }

        IReadOnlyList<MstInGameGimmickObjectModel> CreateMstInGameGimmickObjectModels()
        {
            return DataStore.Get<MstInGameGimmickObjectData>()
                .Select(InGameGimmickObjectDataTranslator.Translate)
                .ToList();
        }

        #endregion

        #region IMstTutorialRepository

        IReadOnlyList<MstTutorialTipModel> IMstTutorialRepository.GetMstTutorialTipModels(MasterDataId tutorialTipId)
        {
            return GetOrCreateModelCache(CreateMstTutorialTipModels)
                .Where(m => m.TutorialTipId == tutorialTipId)
                .OrderBy(m => m.SortOrder)
                .ToList();
        }

        IReadOnlyList<MstTutorialTipModel> CreateMstTutorialTipModels()
        {
            return DataStore.Get<MstTutorialTipI18nData>()
                .Select(TutorialTipI18nDataTranslator.Translate)
                .ToList();
        }

        IReadOnlyList<MstTutorialModel> IMstTutorialRepository.GetMstTutorialModels()
        {
            return GetOrCreateModelCache(CreateMstTutorialModels).ToList();
        }

        IReadOnlyList<MstTutorialModel> CreateMstTutorialModels()
        {
            return DataStore.Get<MstTutorialData>()
                .Select(TutorialDataTranslator.Translate)
                .ToList();
        }

        #endregion

        #region IOprCampaignRepository

        IReadOnlyList<OprCampaignModel> IOprCampaignRepository.GetOprCampaignModelsByDataTime(DateTimeOffset dateTime)
        {
            return GetOrCreateModelCache(CreateOprCampaignModels)
                .Where(m => CalculateTimeCalculator.IsValidTime(dateTime, m.StartAt.Value, m.EndAt.Value))
                .ToList();
        }

        IReadOnlyList<OprCampaignModel> CreateOprCampaignModels()
        {
            var campaignI18nData = DataStore.Get<OprCampaignI18nData>();
            return DataStore.Get<OprCampaignData>()
                .Join(campaignI18nData, data => data.Id, i18n => i18n.OprCampaignId, (data, i18n) => (data, i18n))
                .Select(dataAndI18N => OprCampaignDataTranslator.Translate(dataAndI18N.data, dataAndI18N.i18n))
                .ToList();
        }

        OprCampaignModel IOprCampaignRepository.GetOprCampaignModelFirstOrDefaultById(MasterDataId campaignId)
        {
            return GetOrCreateModelCache(CreateOprCampaignModels)
                .FirstOrDefault(m => m.CampaignId == campaignId, OprCampaignModel.Empty);
        }

        IReadOnlyList<OprCampaignModel> IOprCampaignRepository.GetOprCampaignModelByIds(IReadOnlyList<MasterDataId> campaignIds)
        {
            return GetOrCreateModelCache(CreateOprCampaignModels)
                .Where(m => campaignIds.Any(id => id == m.CampaignId))
                .ToList();
        }

        #endregion

        #region IMstPvpDataRepository

        IReadOnlyList<MstPvpModel> IMstPvpDataRepository.GetMstPvpModels()
        {
            return GetOrCreateModelCache(CreateMstPvpModels).ToList();
        }

        MstPvpModel IMstPvpDataRepository.GetMstPvpModelFirstOrDefault(ContentSeasonSystemId sysPvpSeasonId)
        {
            return GetOrCreateModelCache(CreateMstPvpModels)
                .FirstOrDefault(m => m.Id == sysPvpSeasonId, MstPvpModel.Empty);
        }

        IReadOnlyList<MstPvpModel> CreateMstPvpModels()
        {
            var i18ns = DataStore.Get<MstPvpI18nData>();
            return DataStore.Get<MstPvpData>()
                .Join(i18ns, d => d.Id, i => i.MstPvpId, (d, i) => (d, i))
                .Select(dAndI => MstPvpDataTranslator.TranslateMstPvpModel(dAndI.d, dAndI.i))
                .ToList();
        }

        MstPvpBattleModel IMstPvpDataRepository.GetMstPvpBattleModelFirstOrDefault(ContentSeasonSystemId sysPvpSeasonId)
        {
            var target = GetOrCreateModelCache(CreateMstPvpBattleModels).FirstOrDefault(m => m.Id == sysPvpSeasonId);
            return target ?? MstPvpBattleModel.Empty;
        }

        IEnumerable<MstPvpBattleModel> CreateMstPvpBattleModels()
        {
            try
            {
                var datas = DataStore.Get<MstPvpData>();
                var i18ns = DataStore.Get<MstPvpI18nData>();
                var inGameDatas = DataStore.Get<MstInGameData>();
                var inGameI18nDatas = DataStore.Get<MstInGameI18nData>();

                return datas
                    .Join(i18ns, d => d.Id, i => i.MstPvpId, (data, i18n) => (data, i18n))
                    .Join(inGameDatas,
                        pvps => pvps.data.MstInGameId, inGame => inGame.Id,
                        (pvps, inGameData) => (pvps.data, pvps.i18n, inGameData))
                    .Join(inGameI18nDatas,
                        d => d.inGameData.Id, i18n => i18n.MstInGameId,
                        (dAndInGame, inGameI18n) => (dAndInGame.data, dAndInGame.i18n, dAndInGame.inGameData, inGameI18n))
                    .Select(pvpAndInGame =>
                        MstPvpBattleModelTranslator.ToMstPvpBattleModel(
                            pvpAndInGame.data,
                            pvpAndInGame.i18n,
                            pvpAndInGame.inGameData,
                            pvpAndInGame.inGameI18n));
            }
            catch (Exception e)
            {
                var errorMessage = CheckCreateMstPvpBattleModels();
                throw new MasterDataCreateModelFailedException(errorMessage, e);
            }
        }

        string CheckCreateMstPvpBattleModels()
        {
            var errorMessage = string.Empty;
            var mstPvpDatas = DataStore.Get<MstPvpData>().ToList();
            var mstPvpI18nDatas = DataStore.Get<MstPvpI18nData>();
            var mstInGameDatas = DataStore.Get<MstInGameData>().ToList();
            var mstInGameI18nDatas = DataStore.Get<MstInGameI18nData>().ToList();

            var missingPvpI18nIds = mstPvpDatas
                .Where(pvp => mstPvpI18nDatas.All(i18n => i18n.MstPvpId != pvp.Id))
                .Select(pvp => pvp.Id)
                .ToList();
            if (missingPvpI18nIds.Any())
            {
                errorMessage += $"MstPvp：MstPvpI18nに該当のMstPvpIDが存在していません: {string.Join(", ", missingPvpI18nIds)}\n";
            }

            var missingInGameIds = new List<string>();
            var missingInGameI18nIds = new List<string>();
            foreach (var pvp in mstPvpDatas)
            {
                var inGameData = mstInGameDatas.FirstOrDefault(inGame => inGame.Id == pvp.MstInGameId);
                if (inGameData == null)
                {
                    missingInGameIds.Add(ZString.Format("{0}:{1}", pvp.Id, pvp.MstInGameId));
                    continue;
                }

                var inGameI18nData = mstInGameI18nDatas.FirstOrDefault(i18n => i18n.MstInGameId == inGameData.Id);
                if (inGameI18nData == null)
                {
                    missingInGameI18nIds.Add(ZString.Format("{0}:{1}", pvp.Id, inGameData.Id));
                }
            }

            if (missingInGameIds.Any())
            {
                errorMessage += $"MstPvp：MstInGameに該当のIdが存在していません: {string.Join(", ", missingInGameIds)}\n";
            }

            if (missingInGameI18nIds.Any())
            {
                errorMessage += $"MstPvp：MstInGameI18nに該当のMstInGameIdが存在していません: {string.Join(", ", missingInGameI18nIds)}\n";
            }

            Debug.LogError(errorMessage);
            return errorMessage;
        }

        IReadOnlyList<MstPvpRewardGroupModel> IMstPvpDataRepository.GetMstPvpRewardGroups(ContentSeasonSystemId sysPvpSeasonId)
        {
            return GetOrCreateModelCache(CreateMstPvpRewardGroupModels)
                .Where(m => m.MstPvpId == sysPvpSeasonId)
                .ToList();
        }

        IReadOnlyList<MstPvpRewardGroupModel> CreateMstPvpRewardGroupModels()
        {
            var rewardGrouped = DataStore.Get<MstPvpRewardData>()
                .GroupBy(data => data.MstPvpRewardGroupId, data => data);

            return DataStore.Get<MstPvpRewardGroupData>()
                .GroupJoin(
                    rewardGrouped,
                    group => group.Id,
                    data => data.Key,
                    (group, data) => new { group, rewards = data.FirstOrDefault() }
                )
                .Select(data =>
                {
                    var rewards = data.rewards != null ? data.rewards.ToList() : new List<MstPvpRewardData>();
                    return MstPvpRewardGroupDataTranslator.ToMstPvpRewardGroupModel(data.group, rewards);
                })
                .ToList();
        }

        IReadOnlyList<MstPvpRankModel> CreateMstPvpRankModels()
        {
            return DataStore.Get<MstPvpRankData>()
                .Select(MstPvpRankDataTranslator.ToMstPvpRankModel)
                .ToList();
        }

        IReadOnlyList<MstPvpRankModel> IMstPvpDataRepository.GetMstPvpRanks()
        {
            return GetOrCreateModelCache(CreateMstPvpRankModels).ToList();
        }

        MstPvpRankModel IMstPvpDataRepository.GetCurrentPvpRankModel(PvpPoint pvpPoint)
        {
            var mstModels = GetOrCreateModelCache(CreateMstPvpRankModels).ToList();

            // pointとMstPvpRankModel.RequiredLowerPointを比較して、pointの次に小さいRequiredLowerPointを持つModelを取得
            var currentModel = mstModels
                .OrderBy(m => PvpConst.OrderedPvpRankClassTypes.IndexOf(m.RankClassType))
                .ThenBy(m => m.RequiredLowerPoint)
                .LastOrDefault(m => m.RequiredLowerPoint.Value <= pvpPoint.Value);

            //pvpPoint:0など、mstModelsに対象が存在しない場合は、Emptyを返す
            return currentModel?? MstPvpRankModel.Empty;
        }

        MstPvpRankModel IMstPvpDataRepository.GetNextPvpRankModel(PvpPoint pvpPoint)
        {
            var mstModels = GetOrCreateModelCache(CreateMstPvpRankModels).ToList();

            var nextModel = mstModels
                .OrderBy(m => PvpConst.OrderedPvpRankClassTypes.IndexOf(m.RankClassType))
                .ThenBy(m => m.RequiredLowerPoint.Value)
                .FirstOrDefault(m => m.RequiredLowerPoint.Value > pvpPoint.Value);

            return nextModel ?? MstPvpRankModel.Empty;
        }

        #endregion

        MstTradeProductModel IMstExchangeShopDataRepository.GetTradeProduct(MasterDataId mstExchangeId)
        {
            return GetOrCreateModelCache(CreateMstTradeProductModels)
                .FirstOrDefault(product => product.Id == mstExchangeId, MstTradeProductModel.Empty);
        }

        MstExchangeLineupModel IMstExchangeShopDataRepository.GetTradeLineup(MasterDataId mstLineupId)
        {
            return GetOrCreateModelCache(CreateMstTradeProductModels)
                .SelectMany(product => product.Lineups)
                .FirstOrDefault(lineup => lineup.MstLineupId == mstLineupId, MstExchangeLineupModel.Empty);
        }

        IReadOnlyList<MstTradeProductModel> IMstExchangeShopDataRepository.GetTradeProducts(MasterDataId mstGroupId)
        {
            return GetOrCreateModelCache(CreateMstTradeProductModels)
                .Where(product => product.MstGroupId == mstGroupId)
                .ToList();
        }

        IReadOnlyList<MstTradeProductModel> CreateMstTradeProductModels()
        {
            var mstExchangeData = DataStore.Get<MstExchangeData>();
            var mstExchangeLineupData = DataStore.Get<MstExchangeLineupData>();
            var mstExchangeRewardData = DataStore.Get<MstExchangeRewardData>();
            var mstExchangeCostData = DataStore.Get<MstExchangeCostData>();

            return mstExchangeData
                .GroupJoin(
                    mstExchangeLineupData,
                    exchange => exchange.LineupGroupId,
                    lineup => lineup.GroupId,
                    (exchange, lineups) => (exchange, lineups))
                .Select(data =>
                {
                    var lineupList = data.lineups
                        .Join(
                            mstExchangeRewardData,
                            lineup => lineup.Id,
                            reward => reward.MstExchangeLineupId,
                            (lineup, reward) => (lineup, reward))
                        .Join(
                            mstExchangeCostData,
                            data => data.lineup.Id,
                            cost => cost.MstExchangeLineupId,
                            (data, cost) => (data.lineup, data.reward, cost))
                        .ToList();

                    return MstTradeProductDataTranslator.Translate(
                        data.exchange,
                        lineupList.Select(x => x.lineup).ToList(),
                        lineupList.Select(x => x.reward).ToList(),
                        lineupList.Select(x => x.cost).ToList());
                })
                .ToList();
        }

        MstExchangeModel IMstExchangeShopDataRepository.GetTradeContentFirstOrDefault(MasterDataId mstTradeShopId)
        {
            return GetOrCreateModelCache(CreateMstTradeContentModels)
                .FirstOrDefault(m => m.Id == mstTradeShopId, MstExchangeModel.Empty);
        }

        IReadOnlyList<MstExchangeModel> IMstExchangeShopDataRepository.GetTradeContents()
        {
            return GetOrCreateModelCache(CreateMstTradeContentModels)
                .ToList();
        }

        IReadOnlyList<MstExchangeModel> CreateMstTradeContentModels()
        {
            var mstExchange = DataStore.Get<MstExchangeData>();
            var mstExchangeI18n = DataStore.Get<MstExchangeI18nData>();

            var mstTradeContentModels = mstExchange
                .Join(
                    mstExchangeI18n,
                    exchange => exchange.Id,
                    i18n => i18n.MstExchangeId,
                    (exchange, i18n) => (exchange, i18n))
                .Select(dataAndI18n => MstExchangeDataTranslator.Translate(dataAndI18n.exchange, dataAndI18n.i18n))
                .ToList();

            return mstTradeContentModels;
        }

        void ClearCache()
        {
            ClearModelCache();

            _mstEnemyStageParameterModelDictionary.Clear();
        }
    }
}

