using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.DebugStageDetail.Domain;
using Zenject;

namespace GLOW.Debugs.Command.Domains.UseCase
{
    public class PvpDebugStageDetailModelFactory
    {
        [Inject] IMstPageDataRepository MstPageDataRepository { get; }
        [Inject] IMstPvpDataRepository MstPvpDataRepository { get; }

        public DebugStageDetailUseCaseModel CreateDebugStageDetailUseCaseModel(
            DebugStageSummaryUseCaseModel model)
        {
            var contentSeasonSystemId = new ContentSeasonSystemId(model.MstId.Value);
            var mstPvpModel = MstPvpDataRepository.GetMstPvpModelFirstOrDefault(contentSeasonSystemId);
            var mstBattleModel = MstPvpDataRepository.GetMstPvpBattleModelFirstOrDefault(contentSeasonSystemId);
            var summary = CreateDebugStageQuestSummaryModel(mstPvpModel, mstBattleModel);
            var info = CreateDebugStageDetailElementStageInfoUseCaseModel(mstBattleModel);

            return new DebugStageDetailUseCaseModel(summary, new[] { info });
        }

        DebugStageQuestSummaryModel CreateDebugStageQuestSummaryModel(MstPvpModel mstPvpModel, MstPvpBattleModel mstBattleModel)
        {
            return new DebugStageQuestSummaryModel(
                new QuestName(mstBattleModel.MstInGameId.Value),
                EventName.Empty,
                DebugStageDetailQuestType.Pvp,
                mstBattleModel.Id,
                MasterDataId.Empty,
                MasterDataId.Empty,
                new QuestFlavorText(mstPvpModel.Description.Value),
                new UnlimitedCalculableDateTimeOffset(DateTimeOffset.MinValue),
                new UnlimitedCalculableDateTimeOffset(DateTimeOffset.MinValue),
                new List<DebugStageDetailEventQuestTopUnitModel>(),
                CreateDebugStageDetailAtPvpStatusModel(mstPvpModel)
            );
        }

        DebugStageDetailElementStageInfoUseCaseModel CreateDebugStageDetailElementStageInfoUseCaseModel(
            MstPvpBattleModel mstBattleModel)
        {
            return new DebugStageDetailElementStageInfoUseCaseModel(
                CreateDebugStageDetailAtBaseInfoModel(mstBattleModel),
                DebugStageDetailAtRewardModel.Empty,
                CreateDebugStageDetailAtKomaModel(mstBattleModel.MstPageId)
            );
        }

        DebugStageDetailAtBaseInfoModel CreateDebugStageDetailAtBaseInfoModel(MstPvpBattleModel mstBattleModel)
        {
            var mstEnemyOutPostModel = MstEnemyOutpostModel.Empty;

            return new DebugStageDetailAtBaseInfoModel(
                StageNumber.Empty,
                AdventBattleName.Empty,
                StageConsumeStamina.Empty,
                Difficulty.Normal,
                StageRecommendedLevel.Empty,
                mstBattleModel.BGMAssetKey, //いる
                mstBattleModel.BossBGMAssetKey, // いる
                new OutpostAssetKey("default"),
                mstBattleModel.MstEnemyOutpostId,
                mstEnemyOutPostModel.OutpostAssetKey,
                mstEnemyOutPostModel.ArtworkAssetKey,
                mstEnemyOutPostModel.Hp,
                mstEnemyOutPostModel.IsDamageInvalidation
            );
        }


        DebugStageDetailAtKomaModel CreateDebugStageDetailAtKomaModel(MasterDataId mstPageId)
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

        DebugStageDetailAtPvpStatusModel CreateDebugStageDetailAtPvpStatusModel(MstPvpModel mstPvpModel)
        {
            var contentSeasonSystemId = new ContentSeasonSystemId(mstPvpModel.Id.Value);
            var rewardGroups = MstPvpDataRepository.GetMstPvpRewardGroups(contentSeasonSystemId);
            var rewardGroup = rewardGroups.Any() ? mstPvpModel.Id : MasterDataId.Empty;


            return new DebugStageDetailAtPvpStatusModel(
                mstPvpModel.MinPvpRankClass != null,
                mstPvpModel.MinPvpRankClass,
                rewardGroup,
                mstPvpModel.MaxDailyChallengeCount,
                mstPvpModel.MaxDailyItemChallengeCount
            );
        }
    }
}
