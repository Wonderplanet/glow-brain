using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaTop.Domain.Models;
using GLOW.Scenes.EncyclopediaTop.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.EncyclopediaTop.Domain.UseCases
{
    public class GetEncyclopediaTopUseCase
    {
        record SeriesCount(int UnlockCount, int MaxCount, bool NewBadgeFlag);

        [Inject] IMstSeriesDataRepository MstSeriesDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstUnitEncyclopediaRewardDataRepository MstUnitEncyclopediaRewardDataRepository { get; }
        [Inject] IMstCharacterDataRepository MstUnitDataRepository { get; }
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }
        [Inject] IMstArtworkDataRepository MstArtworkDataRepository { get; }
        [Inject] IMstEmblemRepository MstEmblemDataRepository { get; }

        public EncyclopediaTopModel GetSeriesList()
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();

            var list = MstSeriesDataRepository.GetMstSeriesModels()
                .Select(model => TranslateCellModel(gameFetchOther, model))
                .ToList();

            var unitGrade = UnitEncyclopediaEffectCalculator.CalculateUnitEncyclopediaGrade(gameFetchOther.UserUnitModels);
            var userReceivedRewards = gameFetchOther.UserReceivedUnitEncyclopediaRewardModels;
            var mstRewards = MstUnitEncyclopediaRewardDataRepository.GetUnitEncyclopediaRewards();
            var isUnReceived = mstRewards
                .Where(mst => mst.UnitEncyclopediaRank.Value <= unitGrade.Value)
                .Any(mst => userReceivedRewards.All(receivedReward => receivedReward.MstUnitEncyclopediaRewardId != mst.Id));

            var badge = new NotificationBadge(isUnReceived);
            return new EncyclopediaTopModel(list, unitGrade, badge);
        }

        EncyclopediaTopSeriesCellModel TranslateCellModel(GameFetchOtherModel gameFetchOther, MstSeriesModel model)
        {
            var seriesBannerPath = new SeriesIconImagePath(SeriesAssetPath.GetSeriesBannerPath(model.SeriesAssetKey.Value));
            var playerUnitCount = GetPlayerUnitUnlockCount(model.Id);
            var enemyUnitCount = GetEnemyUnitUnlockCount(gameFetchOther, model.Id);
            var artworkCount = GetArtworkUnlockCount(model.Id);
            var emblemCount = GetEmblemUnlockCount(model.Id);

            var unlockCounts = playerUnitCount.UnlockCount
                                + enemyUnitCount.UnlockCount
                                + artworkCount.UnlockCount
                                + emblemCount.UnlockCount;
            var maxCounts = playerUnitCount.MaxCount
                            + enemyUnitCount.MaxCount
                            + artworkCount.MaxCount
                            + emblemCount.MaxCount;

            var badge = playerUnitCount.NewBadgeFlag
                        || enemyUnitCount.NewBadgeFlag
                        || artworkCount.NewBadgeFlag
                        || emblemCount.NewBadgeFlag;

            return new EncyclopediaTopSeriesCellModel(
                model.Id,
                seriesBannerPath,
                model.Name,
                new EncyclopediaSeriesCount(maxCounts),
                new EncyclopediaSeriesCount(unlockCounts),
                new NotificationBadge(badge));
        }

        SeriesCount GetPlayerUnitUnlockCount(MasterDataId mstSeriesId)
        {
            var mstUnitIds = MstUnitDataRepository.GetSeriesCharacters(mstSeriesId)
                .Select(mstUnit => mstUnit.Id)
                .ToHashSet();
            var userUnits = GameRepository.GetGameFetchOther().UserUnitModels
                .Where(userUnit => mstUnitIds.Contains(userUnit.MstUnitId))
                .ToHashSet();

            var unlockCount = userUnits.Count;
            var maxCount = mstUnitIds.Count;
            var newBadgeFlag = userUnits
                .Any(userUnit => userUnit.IsNewEncyclopedia);
            return new SeriesCount(unlockCount, maxCount, newBadgeFlag);
        }

        SeriesCount GetEnemyUnitUnlockCount(GameFetchOtherModel gameFetchOther, MasterDataId mstSeriesId)
        {
            var mstEnemies = MstEnemyCharacterDataRepository.GetSeriesEnemyCharacters(mstSeriesId)
                .Where(mstEnemy => mstEnemy.VisibleOnEncyclopediaFlag)
                .ToHashSet();

            var enemyDiscovers = gameFetchOther.UserEnemyDiscoverModels
                .Where(discover => mstEnemies.Any(mst => mst.Id == discover.MstEnemyCharacterId))
                .ToHashSet();

            var unlockCount = enemyDiscovers.Count;
            var maxCount = mstEnemies.Count;
            var newBadgeFlag = enemyDiscovers
                .Any(enemyDiscover => enemyDiscover.IsNewEncyclopedia);
            return new SeriesCount(unlockCount, maxCount, newBadgeFlag);
        }

        SeriesCount GetArtworkUnlockCount(MasterDataId mstSeriesId)
        {
            var mstArtworkIds = MstArtworkDataRepository.GetSeriesArtwork(mstSeriesId)
                .Select(mstArtwork => mstArtwork.Id)
                .ToHashSet();
            var userArtworks = GameRepository.GetGameFetchOther().UserArtworkModels
                .Where(userArtwork => mstArtworkIds.Contains(userArtwork.MstArtworkId))
                .ToHashSet();

            var unlockCount = userArtworks.Count;
            var maxCount = mstArtworkIds.Count;
            var newBadgeFlag = userArtworks
                .Any(userArtwork => userArtwork.IsNewEncyclopedia);
            return new SeriesCount(unlockCount, maxCount, newBadgeFlag);
        }

        SeriesCount GetEmblemUnlockCount(MasterDataId mstSeriesId)
        {
            var mstEmblems = MstEmblemDataRepository.GetSeriesEmblems(mstSeriesId)
                .Select(mstEmblem => mstEmblem.Id)
                .ToList();
            var userEmblems = GameRepository.GetGameFetchOther().UserEmblemModel
                .Where(userEmblem => mstEmblems.Contains(userEmblem.MstEmblemId))
                .ToList();

            var unlockCount = userEmblems.Count;
            var maxCount = mstEmblems.Count;
            var newBadgeFlag = userEmblems
                .Any(userEmblem => userEmblem.IsNewEncyclopedia);
            return new SeriesCount(unlockCount, maxCount, newBadgeFlag);
        }
    }
}
