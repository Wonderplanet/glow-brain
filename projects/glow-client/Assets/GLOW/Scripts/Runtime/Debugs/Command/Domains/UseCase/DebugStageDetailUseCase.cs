using System.Collections.Generic;
using System.Globalization;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Event;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Extensions;
using GLOW.Debugs.Command.Domains.UseCase;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models.PersistentStateKomaEffectModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;
using Zenject;
using Exp = GLOW.Core.Domain.ValueObjects.Exp;

namespace GLOW.Scenes.DebugStageDetail.Domain
{
    // 企画設計書と実装が合っているか確認するためのQA向けModel
    // モデル構造
    // 全体..(DebugStageDetailUseCaseModel)
    // ├─クエスト別設計..(DebugStageDetailElementUseCaseModel)
    // ├───クエスト内ステージ概要..(DebugStageDetailSummaryModel)
    // └─────各種ステージ..(その他Model)

    // クエスト別設計
    public record DebugStageDetailUseCaseModel(
        DebugStageQuestSummaryModel Summary,
        IReadOnlyList<DebugStageDetailElementStageInfoUseCaseModel> StageInfos);

    // クエスト内ステージ概要
    public record DebugStageDetailElementStageInfoUseCaseModel(
        DebugStageDetailAtBaseInfoModel BaseInfo,
        DebugStageDetailAtRewardModel Rewards,
        DebugStageDetailAtKomaModel Komas
    );

    # region Model ステージ概要

    public record DebugStageQuestSummaryModel(
        QuestName QuestName,
        EventName EventName,
        DebugStageDetailQuestType QuestType,
        MasterDataId NormalMasterDataId,
        MasterDataId HardQuestMstId,
        MasterDataId ExtraQuestMstId,
        QuestFlavorText FlavorText,
        UnlimitedCalculableDateTimeOffset StartAt,
        UnlimitedCalculableDateTimeOffset EndAt,
        IReadOnlyList<DebugStageDetailEventQuestTopUnitModel> EventUnitModels,
        DebugStageDetailAtPvpStatusModel PvpStatus)
    {
        public string ToStartString()
        {
            return StartAt.Value.ToString("yyyy/MM/dd HH:mm:ss", CultureInfo.InvariantCulture);
        }
        public string ToEndString()
        {
            return EndAt.Value.ToString("yyyy/MM/dd HH:mm:ss", CultureInfo.InvariantCulture);
        }
    };

    public enum DebugStageDetailQuestType
    {
        Event,
        Normal,
        Enhance,
        Tutorial,
        AdventBattle,
        Pvp
    }

    public record DebugStageDetailEventQuestTopUnitModel(
        UnitAssetKey UnitAssetKey,
        CharacterName UnitName,
        IReadOnlyList<EventDisplayUnitSpeechBalloonText> SpeechBalloonTexts
    );

    #endregion

    # region Model 基礎情報

    public record DebugStageDetailAtBaseInfoModel(
        StageNumber StageNumber,
        AdventBattleName AdventBattleName,
        StageConsumeStamina ConsumeStamina,
        Difficulty Difficulty,
        StageRecommendedLevel StageRecommendedLevel,
        BGMAssetKey NormalBGMAssetKey,
        BGMAssetKey BossBGMAssetKey,
        OutpostAssetKey PlayerOutpostAssetKey,
        MasterDataId MstEnemyOutpostId,
        OutpostAssetKey EnemyOutpostAssetKey,
        ArtworkAssetKey EnemyOutpostArtworkAssetKey,
        HP EnemyOutpostHp,
        OutpostDamageInvalidationFlag IsDamageInvalidation
    )
    {
        public string NameString()
        {
            if (!AdventBattleName.IsEmpty())
            {
                return AdventBattleName.Value;
            }

            return $"{StageNumber.Value}話";
        }

        public string HpString()
        {
            if (IsDamageInvalidation)
            {
                return "∞";
            }
            return EnemyOutpostHp.Value.ToString("N0", CultureInfo.InvariantCulture);
        }
    };

    #endregion

    # region Model 報酬設計

    public record DebugStageDetailAtRewardModel(
        Coin ClearCoin,
        Exp Exp,
        IReadOnlyList<ArtworkFragmentAssetNum> DropArtworkFragmentAssetNums,
        Percentage DropPercentage,
        IReadOnlyList<DebugStageDetailRewardItemModel> RewardItems,
        IReadOnlyList<DebugStageDetailAtSpeedAttackRewardModel> SpeedAttackRewardModels)
    {
        public static DebugStageDetailAtRewardModel Empty { get; } = new (
            Coin.Empty,
            Exp.Empty,
            new List<ArtworkFragmentAssetNum>(),
            Percentage.Empty,
            new List<DebugStageDetailRewardItemModel>(),
            new List<DebugStageDetailAtSpeedAttackRewardModel>()
            );

        public string FragmentNumsString()
        {
            if (DropArtworkFragmentAssetNums.Count == 0)
            {
                return "なし";
            }

            return string.Join(", ", DropArtworkFragmentAssetNums.Select(m => m.Value));
        }
    };
    public record DebugStageDetailAtSpeedAttackRewardModel(
        ResourceType ResourceType,
        MasterDataId ResourceId,
        PlayerResourceName PlayerResourceName,
        PlayerResourceAmount ResourceAmount,
        StageClearTime Time);

    public record DebugStageDetailRewardItemModel(
        RewardCategory RewardCategory,
        ResourceType ResourceType,
        MasterDataId ResourceId,
        PlayerResourceName PlayerResourceName,
        PlayerResourceAmount ResourceAmount,
        Percentage DropPercentage
    );

    #endregion

    # region Model コマ設計

    public record DebugStageDetailAtKomaModel(
        IReadOnlyList<DebugStageDetailKomaElementModel> Elements);

    public record DebugStageDetailKomaElementModel(
        int Row,
        string KomaLineLayoutAssetKey,
        int KomaCount, //PageComponent.prefabから見るしか無い
        float Height,
        DebugStageDetailKomaElementKomaModel Koma1,
        DebugStageDetailKomaElementKomaModel Koma2,
        DebugStageDetailKomaElementKomaModel Koma3,
        DebugStageDetailKomaElementKomaModel Koma4,
        KomaBackgroundAssetKey KomaBackgroundAssetKey
    );

    public record DebugStageDetailKomaElementKomaModel(
        float Width,
        KomaEffectType EffectType,
        KomaEffectParameter EffectParameter1, //Gust・Poisonのときは効果時間。ほかは効果値。
        KomaEffectParameter EffectParameter2 //Gust・Poisonのときは効果値。ほかは未使用。
    )
    {
        public static DebugStageDetailKomaElementKomaModel Empty { get; } = new(
            0f,
            KomaEffectType.None,
            KomaEffectParameter.Empty,
            KomaEffectParameter.Empty);
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public string KomaEffectString()
        {
            if (EffectType == KomaEffectType.Gust || PersistentStateKomaEffectModel.IsPersistentKomaEffect(EffectType))
            {
                return $"効果時間: {EffectParameter1.Value} / 効果数値: {EffectParameter2.Value}";
            }

            return $"効果数値: {EffectParameter1.Value}";
        }
    }

    #endregion

    #region Pvp情報
    // 開催
    // 参加最低ランク
    // 専用報酬
    // 1日の上限回数(フリー)
    // 1日の上限回数(ランクマッチチケット)
    public record DebugStageDetailAtPvpStatusModel(
        bool IsOpenRanking,
        PvpRankClassType? MinPvpRankClass,
        MasterDataId SpecificMstPvpRewardGroupId,
        PvpDailyChallengeCount MaxDailyChallengeCount,
        PvpDailyChallengeCount MaxDailyItemChallengeCount
    )
    {
        public static DebugStageDetailAtPvpStatusModel Empty { get; } = new(
            false,
            null,
            MasterDataId.Empty,
            PvpDailyChallengeCount.Empty,
            PvpDailyChallengeCount.Empty);

        public string RankingOpeningString()
        {
            return IsOpenRanking ? "する" : "しない";
        }

        public string MinPvpRankClassString()
        {
            return MinPvpRankClass.HasValue ? MinPvpRankClass.Value.ToString() : "なし";
        }

        public string RewardString()
        {
            return SpecificMstPvpRewardGroupId.IsEmpty()
                ? "なし(default_pvp)"
                : SpecificMstPvpRewardGroupId.Value.ToString();
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };

    #endregion

    public class DebugStageDetailUseCase
    {
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstEnemyOutpostDataRepository MstEnemyOutpostDataRepository { get; }
        [Inject] IMstArtworkFragmentDataRepository MstArtworkFragmentDataRepository { get; }
        [Inject] IMstStageEventRewardDataRepository MstStageEventRewardDataRepository { get; }
        [Inject] IMstStageRewardDataRepository MstStageRewardDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IMstPageDataRepository MstPageDataRepository { get; }
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] IMstInGameSpecialRuleDataRepository MstInGameSpecialRuleDataRepository { get; }
        [Inject] IMstStageClearTimeRewardRepository MstStageClearTimeRewardRepository { get; }
        [Inject] AdventBattleDebugStageDetailModelFactory AdventBattleDebugStageDetailModelFactory { get; }
        [Inject] PvpDebugStageDetailModelFactory PvpDebugStageDetailModelFactory { get; }

        public DebugStageDetailUseCaseModel GetModel(
            DebugStageSummaryUseCaseModel model)
        {
            if (model.DebugQuestType == DebugStageDetailQuestType.AdventBattle)
            {
                return AdventBattleDebugStageDetailModelFactory.CreateDebugStageDetailUseCaseModel(model);
            }

            if (model.DebugQuestType == DebugStageDetailQuestType.Pvp)
            {
                return PvpDebugStageDetailModelFactory.CreateDebugStageDetailUseCaseModel(model);
            }

            var mstQuestModel = MstQuestDataRepository.GetMstQuestModel(model.MstId);
            var stages = MstStageDataRepository.GetMstStagesFromMstQuestId(mstQuestModel.Id)
                .Select(m =>
                    CreateDebugStageDetailElementStageInfoUseCaseModel(
                        model.Difficulty,
                        model.ToQuestType(),
                        m))
                .ToList();

            var groupMstQuests = MstQuestDataRepository.GetMstQuestModelsByQuestGroup(mstQuestModel.GroupId)
                .GroupBy(m => m.GroupId)
                .First();

            var summary = CreateDebugStageDetailSummaryModel(groupMstQuests);
            return new DebugStageDetailUseCaseModel(summary, stages);
        }

        DebugStageDetailElementStageInfoUseCaseModel CreateDebugStageDetailElementStageInfoUseCaseModel(
            Difficulty difficulty,
            QuestType questType,
            MstStageModel mstStageModel)
        {
            var baseInfo = CreateDebugStageDetailAtBaseInfoModel(difficulty, mstStageModel);
            var rewards = CreateDebugStageDetailAtRewardModel(questType, mstStageModel);
            var koma = CreateDebugStageDetailAtKomaModel(mstStageModel);

            return new DebugStageDetailElementStageInfoUseCaseModel(
                baseInfo,
                rewards,
                koma
            );
        }

        // ステージ概要
        DebugStageQuestSummaryModel CreateDebugStageDetailSummaryModel(
            IGrouping<MasterDataId, MstQuestModel> mstQuestModelsGroupByQuestGroup)
        {
            // 下準備
            var sampleMstQuestModel = mstQuestModelsGroupByQuestGroup.First();
            var normalMstQuest = mstQuestModelsGroupByQuestGroup
                .FirstOrDefault(m => m.Difficulty == Difficulty.Normal, MstQuestModel.Empty);
            var hardMstQuest = mstQuestModelsGroupByQuestGroup
                .FirstOrDefault(m => m.Difficulty == Difficulty.Hard, MstQuestModel.Empty);
            var extraMstQuest = mstQuestModelsGroupByQuestGroup
                .FirstOrDefault(m => m.Difficulty == Difficulty.Extra, MstQuestModel.Empty);

            if (sampleMstQuestModel.QuestType != QuestType.Event)
            {
                //イベント以外の要素作成
                return new DebugStageQuestSummaryModel(
                    sampleMstQuestModel.Name,
                    EventName.Empty,
                    ToDebugStageDetailQuestType(sampleMstQuestModel.QuestType),
                    normalMstQuest.Id,
                    hardMstQuest.Id,
                    extraMstQuest.Id,
                    sampleMstQuestModel.QuestFlavorText,
                    sampleMstQuestModel.StartDate,
                    sampleMstQuestModel.EndDate,
                    new List<DebugStageDetailEventQuestTopUnitModel>(),
                    DebugStageDetailAtPvpStatusModel.Empty
                );
            }

            // EventQuestTopUnitUseCaseModelFactoryコード引用しつつDebug向けに改修
            var eventTopDisplayUnitModels = MstQuestDataRepository.GetEventDisplayUnits()
                .Where(m => m.MstQuestId == sampleMstQuestModel.Id)
                // .Take(MaxDisplayUnitCount)
                .Join(
                    MstCharacterDataRepository.GetCharacters(),
                    m => m.MstUnitId,
                    c => c.Id,
                    CreateDebugStageDetailEventQuestTopUnitModel)
                .ToList();

            var msteventModel = MstEventDataRepository.GetEvent(sampleMstQuestModel.MstEventId);

            // イベントの要素作成
            return new DebugStageQuestSummaryModel(
                sampleMstQuestModel.Name,
                msteventModel.Name,
                ToDebugStageDetailQuestType(sampleMstQuestModel.QuestType),
                normalMstQuest.Id,
                hardMstQuest.Id,
                extraMstQuest.Id,
                sampleMstQuestModel.QuestFlavorText,
                sampleMstQuestModel.StartDate,
                sampleMstQuestModel.EndDate,
                eventTopDisplayUnitModels,
                DebugStageDetailAtPvpStatusModel.Empty
            );
        }

        DebugStageDetailQuestType ToDebugStageDetailQuestType(QuestType questType)
        {
            return questType switch
            {
                QuestType.Event => DebugStageDetailQuestType.Event,
                QuestType.Normal => DebugStageDetailQuestType.Normal,
                QuestType.Enhance => DebugStageDetailQuestType.Enhance,
                QuestType.Tutorial => DebugStageDetailQuestType.Tutorial,
                _ => DebugStageDetailQuestType.Normal,
            };
        }

        DebugStageDetailEventQuestTopUnitModel CreateDebugStageDetailEventQuestTopUnitModel(
            MstEventDisplayUnitModel displayUnitModel,
            MstCharacterModel mstCharacterModel
        )
        {
            var speechBalloons = new List<EventDisplayUnitSpeechBalloonText>();
            if (!displayUnitModel.SpeechBalloonText1.IsEmpty())
            {
                speechBalloons.Add(displayUnitModel.SpeechBalloonText1);
            }

            if (!displayUnitModel.SpeechBalloonText2.IsEmpty())
            {
                speechBalloons.Add(displayUnitModel.SpeechBalloonText2);
            }

            if (!displayUnitModel.SpeechBalloonText3.IsEmpty())
            {
                speechBalloons.Add(displayUnitModel.SpeechBalloonText3);
            }

            return new DebugStageDetailEventQuestTopUnitModel(
                mstCharacterModel.AssetKey,
                mstCharacterModel.Name,
                speechBalloons
            );
        }

        // 基礎情報
        DebugStageDetailAtBaseInfoModel CreateDebugStageDetailAtBaseInfoModel(
            Difficulty difficulty,
            MstStageModel mstStageModel)
        {
            var playerOutpostAssetKey = mstStageModel.PlayerOutpostAssetKey.IsEmpty()
                ? new OutpostAssetKey("default")// インゲームではOutpostAssetKey.PlayerDefault利用
                : mstStageModel.PlayerOutpostAssetKey;

            var mstEnemyOutPostModel = MstEnemyOutpostDataRepository.GetEnemyOutpost(mstStageModel.MstEnemyOutpostId);


            return new DebugStageDetailAtBaseInfoModel(
                mstStageModel.StageNumber,
                AdventBattleName.Empty,
                mstStageModel.StageConsumeStamina,
                difficulty,
                mstStageModel.RecommendedLevel,
                mstStageModel.BGMAssetKey,
                mstStageModel.BossBGMAssetKey,
                playerOutpostAssetKey,
                mstStageModel.MstEnemyOutpostId,
                mstEnemyOutPostModel.OutpostAssetKey,
                mstEnemyOutPostModel.ArtworkAssetKey,
                mstEnemyOutPostModel.Hp,
                mstEnemyOutPostModel.IsDamageInvalidation
            );
        }


        // 報酬設計
        DebugStageDetailAtRewardModel CreateDebugStageDetailAtRewardModel(
            QuestType questType,
            MstStageModel mstStageModel)
        {
            //原画情報はmstStageData.MstArtworkFragmentDropGroupIdから
            //MstArtworkFragmentDataRepository.GetArtworkFragmentModels().GroupBy(m => m.MstDropGroupId).ToList();
            //して、FirstOrDefaultで取得する
            var gettableArtWorkFragments = MstArtworkFragmentDataRepository
                .GetArtworkFragmentModels()
                .Where(m => m.MstDropGroupId == mstStageModel.MstArtworkFragmentDropGroupId)
                .ToList();
            var dropPercentage = gettableArtWorkFragments.Any()
                ? gettableArtWorkFragments.First().DropPercentage
                : Percentage.Empty;

            return new DebugStageDetailAtRewardModel(
                mstStageModel.Coin,
                mstStageModel.Exp,
                gettableArtWorkFragments.Select(m => m.AssetNum).ToList(),
                dropPercentage,
                CreateDebugStageDetailRewardItemModel(questType, mstStageModel),
                CreateSpeedAttackRewardModels(mstStageModel.Id)
                );
        }

        IReadOnlyList<DebugStageDetailAtSpeedAttackRewardModel> CreateSpeedAttackRewardModels(MasterDataId mstStageId)
        {
            var mstInGameSpecialRules =
                MstInGameSpecialRuleDataRepository.GetInGameSpecialRuleModels(
                    mstStageId,
                    InGameContentType.Stage);
            var isSpeedAttack = mstInGameSpecialRules.Any(mst => mst.RuleType == RuleType.SpeedAttack);
            if (!isSpeedAttack) return new List<DebugStageDetailAtSpeedAttackRewardModel>();

            return MstStageClearTimeRewardRepository.GetClearTimeRewards(mstStageId)
                .Select(m =>
                {
                    var playerResource = PlayerResourceModelFactory.Create(
                        m.ResourceType,
                        m.ResourceId,
                        m.ResourceAmount.ToPlayerResourceAmount());

                    return new DebugStageDetailAtSpeedAttackRewardModel(
                        playerResource.Type,
                        playerResource.Id,
                        playerResource.Name,
                        playerResource.Amount,
                        m.UpperClearTimeMs);
                })
                .ToList();
        }



        IReadOnlyList<DebugStageDetailRewardItemModel> CreateDebugStageDetailRewardItemModel(
            QuestType questType,
            MstStageModel mstStageModel)
        {
            if (questType == QuestType.Event)
            {
                return MstStageEventRewardDataRepository.GetMstStageEventRewardList(mstStageModel.Id)
                    .Select(m =>
                    {
                        var playerResource = PlayerResourceModelFactory.Create(
                            m.ResourceType,
                            m.ResourceId,
                            m.ResourceAmount.ToPlayerResourceAmount());

                        var amount = m.ResourceType == ResourceType.Unit
                            ? new PlayerResourceAmount(1) // ユニットは必ず1体
                            : playerResource.Amount;

                        var name = m.ResourceType == ResourceType.Unit
                            ? CreateUnitName(m.ResourceId)
                            : playerResource.Name;

                        return new DebugStageDetailRewardItemModel(
                            m.RewardCategory,
                            m.ResourceType,
                            m.ResourceId,
                            name,
                            amount,
                            m.Percentage);
                    })
                    .ToList();
            }
            else
            {
                return MstStageRewardDataRepository.GetMstStageRewardList(mstStageModel.Id)
                    .Select(m =>
                    {
                        var playerResource = PlayerResourceModelFactory.Create(
                            m.ResourceType,
                            m.ResourceId,
                            m.ResourceAmount.ToPlayerResourceAmount());

                        var amount = m.ResourceType == ResourceType.Unit
                            ? new PlayerResourceAmount(1) // ユニットは必ず1体
                            : playerResource.Amount;

                        var name = m.ResourceType == ResourceType.Unit
                            ? CreateUnitName(m.ResourceId)
                            : playerResource.Name;


                        return new DebugStageDetailRewardItemModel(
                            m.RewardCategory,
                            m.ResourceType,
                            m.ResourceId,
                            name,
                            amount,
                            new Percentage(m.Percentage.Value));
                    })
                    .ToList();
            }
        }

        PlayerResourceName CreateUnitName(MasterDataId resourceId)
        {
            var mstCharacterModel = MstCharacterDataRepository.GetCharacter(resourceId);
            return new PlayerResourceName(mstCharacterModel.Name.Value);
        }

        // コマ設計
        DebugStageDetailAtKomaModel CreateDebugStageDetailAtKomaModel(MstStageModel mstStageModel)
        {
            var komaElements = MstPageDataRepository.GetPage(mstStageModel.MstPageId).KomaLineList
                .Select((k, index) => CreateDebugStageDetailKomaElementModel(index,k))
                .ToList();
            return new DebugStageDetailAtKomaModel(komaElements);
        }

        DebugStageDetailKomaElementModel CreateDebugStageDetailKomaElementModel(int index, MstKomaLineModel mstKomaLineModel)
        {
            // AssetKeyを直接取得する術が無いのでAsstPathを取得して、そこから整形してAssetKeyを取得する
            var komaAssetPathPrefix = KomaAssetPath.GetKomaLineAssetPath("");
            var assetPath = mstKomaLineModel.KomaSetTypeAssetPath.Value.ToString();
            var komaLineLayoutAssetKey = assetPath.Replace(komaAssetPathPrefix, "");

            return new DebugStageDetailKomaElementModel(
            index + 1,
            komaLineLayoutAssetKey,
            mstKomaLineModel.KomaList.Count,
            mstKomaLineModel.Height,
            CreateDebugStageDetailKomaElementKomaModel(mstKomaLineModel.KomaList[0]),
            2 <= mstKomaLineModel.KomaList.Count
                ? CreateDebugStageDetailKomaElementKomaModel(mstKomaLineModel.KomaList[1])
                : DebugStageDetailKomaElementKomaModel.Empty,
            3 <= mstKomaLineModel.KomaList.Count
                ? CreateDebugStageDetailKomaElementKomaModel(mstKomaLineModel.KomaList[2])
                : DebugStageDetailKomaElementKomaModel.Empty,
            4 <= mstKomaLineModel.KomaList.Count
                ? CreateDebugStageDetailKomaElementKomaModel(mstKomaLineModel.KomaList[3])
                : DebugStageDetailKomaElementKomaModel.Empty,
            mstKomaLineModel.KomaList.First().BackgroundAssetKey
            );
        }

        DebugStageDetailKomaElementKomaModel CreateDebugStageDetailKomaElementKomaModel(MstKomaModel mstKomaModel)
        {
            return new DebugStageDetailKomaElementKomaModel(
                mstKomaModel.Width,
                mstKomaModel.KomaEffectType,
                mstKomaModel.KomaEffectParameter1,
                mstKomaModel.KomaEffectParameter2
            );
        }
    }
}
