using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;
using GLOW.Scenes.DebugStageDetail.Domain;
using Zenject;

namespace GLOW.Debugs.Command.Domains.UseCase
{
    public class AdventBattleDebugStageDetailModelFactory
    {
        [Inject] IMstPageDataRepository MstPageDataRepository { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IMstEnemyOutpostDataRepository MstEnemyOutpostDataRepository { get; }
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }

        public DebugStageDetailUseCaseModel CreateDebugStageDetailUseCaseModel(
            DebugStageSummaryUseCaseModel model)
        {
            var mstAdventBattleModel = MstAdventBattleDataRepository.GetMstAdventBattleModel(model.MstId);
            var summary = CreateDebugStageQuestSummaryModel(mstAdventBattleModel);
            var info = CreateDebugStageDetailElementStageInfoUseCaseModel(mstAdventBattleModel);
            return new DebugStageDetailUseCaseModel(
                summary,
                new List<DebugStageDetailElementStageInfoUseCaseModel>() { info }
            );
        }

        DebugStageQuestSummaryModel CreateDebugStageQuestSummaryModel(
            MstAdventBattleModel mstAdventBattleModel)
        {
            return new DebugStageQuestSummaryModel(
                new QuestName(mstAdventBattleModel.AdventBattleName.Value),
                EventName.Empty,
                DebugStageDetailQuestType.AdventBattle,
                mstAdventBattleModel.Id,
                MasterDataId.Empty,
                MasterDataId.Empty,
                new QuestFlavorText(mstAdventBattleModel.AdventBattleBossDescription.Value),
                new UnlimitedCalculableDateTimeOffset(mstAdventBattleModel.StartDateTime.Value),
                new UnlimitedCalculableDateTimeOffset(mstAdventBattleModel.EndDateTime.Value),
                CreateDebugStageDetailEventQuestTopUnitModel(mstAdventBattleModel),
                DebugStageDetailAtPvpStatusModel.Empty
            );
        }

        IReadOnlyList<DebugStageDetailEventQuestTopUnitModel> CreateDebugStageDetailEventQuestTopUnitModel(
            MstAdventBattleModel model)
        {
            var result = new List<DebugStageDetailEventQuestTopUnitModel>();
            if (!model.DisplayEnemyUnitIdFirst.IsEmpty())
            {
                var chara = MstEnemyCharacterDataRepository
                    .GetEnemyCharacter(model.DisplayEnemyUnitIdFirst);

                result.Add(new DebugStageDetailEventQuestTopUnitModel(
                    chara.AssetKey,
                    chara.Name,
                    new List<EventDisplayUnitSpeechBalloonText>())
                );
            }

            if (!model.DisplayEnemyUnitIdSecond.IsEmpty())
            {
                var chara = MstEnemyCharacterDataRepository
                    .GetEnemyCharacter(model.DisplayEnemyUnitIdSecond);

                result.Add(new DebugStageDetailEventQuestTopUnitModel(
                    chara.AssetKey,
                    chara.Name,
                    new List<EventDisplayUnitSpeechBalloonText>())
                );
            }

            if (!model.DisplayEnemyUnitIdThird.IsEmpty())
            {
                var chara = MstEnemyCharacterDataRepository
                    .GetEnemyCharacter(model.DisplayEnemyUnitIdThird);

                result.Add(new DebugStageDetailEventQuestTopUnitModel(
                    chara.AssetKey,
                    chara.Name,
                    new List<EventDisplayUnitSpeechBalloonText>())
                );
            }

            return result;
        }

        DebugStageDetailElementStageInfoUseCaseModel CreateDebugStageDetailElementStageInfoUseCaseModel(
            MstAdventBattleModel mstAdventBattleModel)
        {
            return new DebugStageDetailElementStageInfoUseCaseModel(
                CreateDebugStageDetailAtBaseInfoModel(mstAdventBattleModel),
                CreateDebugStageDetailAtRewardModel(mstAdventBattleModel),
                CreateDebugStageDetailAtKomaModel(mstAdventBattleModel.MstPageId));
        }

        DebugStageDetailAtBaseInfoModel CreateDebugStageDetailAtBaseInfoModel(
            MstAdventBattleModel mstAdventBattleModel)
        {
            var playerOutpostAssetKey = mstAdventBattleModel.PlayerOutpostAssetKey.IsEmpty()
                ? new OutpostAssetKey("default") // インゲームではOutpostAssetKey.PlayerDefault利用
                : mstAdventBattleModel.PlayerOutpostAssetKey;
            var mstEnemyOutPostModel = MstEnemyOutpostDataRepository.GetEnemyOutpost(mstAdventBattleModel.MstEnemyOutpostId);

            return new DebugStageDetailAtBaseInfoModel(
                StageNumber.Empty,
                mstAdventBattleModel.AdventBattleName,
                StageConsumeStamina.Empty,
                Difficulty.Normal,
                StageRecommendedLevel.Empty,
                mstAdventBattleModel.BGMAssetKey,
                mstAdventBattleModel.BossBGMAssetKey,
                playerOutpostAssetKey,
                mstAdventBattleModel.MstEnemyOutpostId,
                mstEnemyOutPostModel.OutpostAssetKey,
                mstEnemyOutPostModel.ArtworkAssetKey,
                mstEnemyOutPostModel.Hp,
                mstEnemyOutPostModel.IsDamageInvalidation
            );
        }

        DebugStageDetailAtRewardModel CreateDebugStageDetailAtRewardModel(
            MstAdventBattleModel mstAdventBattleModel)
        {
            var clearRewards = MstAdventBattleDataRepository.GetMstAdventBattleClearRewardModels(mstAdventBattleModel.Id)
                .Select(CreateDebugStageDetailRewardItemModel)
                .ToList();
            return new DebugStageDetailAtRewardModel(
                mstAdventBattleModel.Coin,
                new Exp((int)mstAdventBattleModel.UserExp.Value),
                new List<ArtworkFragmentAssetNum>(),
                Percentage.Empty,
                clearRewards,
                new List<DebugStageDetailAtSpeedAttackRewardModel>()
            );
        }

        DebugStageDetailRewardItemModel CreateDebugStageDetailRewardItemModel(
            MstAdventBattleClearRewardModel model)
        {
            var playerResource = PlayerResourceModelFactory.Create(
                model.ResourceType,
                model.ResourceId,
                model.ResourceAmount.ToPlayerResourceAmount());

            var amount = model.ResourceType == ResourceType.Unit
                ? new PlayerResourceAmount(1) // ユニットは必ず1体
                : playerResource.Amount;

            var name = model.ResourceType == ResourceType.Unit
                ? CreateUnitName(model.ResourceId)
                : playerResource.Name;


            return new DebugStageDetailRewardItemModel(
                ToRewardCategory(model.RewardCategory),
                model.ResourceType,
                model.ResourceId,
                name,
                amount,
                model.Percentage
            );
        }

        PlayerResourceName CreateUnitName(MasterDataId resourceId)
        {
            var mstCharacterModel = MstCharacterDataRepository.GetCharacter(resourceId);
            return new PlayerResourceName(mstCharacterModel.Name.Value);
        }

        RewardCategory ToRewardCategory(AdventBattleClearRewardCategory category)
        {
            return category switch
            {
                AdventBattleClearRewardCategory.FirstClear => RewardCategory.FirstClear,
                AdventBattleClearRewardCategory.Always => RewardCategory.Always,
                AdventBattleClearRewardCategory.Random => RewardCategory.Random,
            };
        }

        DebugStageDetailAtKomaModel CreateDebugStageDetailAtKomaModel(
            MasterDataId mstPageId)
        {
            var komaElements = MstPageDataRepository.GetPage(mstPageId).KomaLineList
                .Select((k, index) => CreateDebugStageDetailKomaElementModel(index, k))
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
