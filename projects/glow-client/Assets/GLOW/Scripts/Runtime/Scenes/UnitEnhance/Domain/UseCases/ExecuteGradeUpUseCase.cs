using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.ArtworkFragmentAcquisition.Domain.Models;
using GLOW.Scenes.BattleResult.Domain.Factory;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.UnitEnhance.Domain.Models;
using Zenject;

namespace GLOW.Scenes.UnitEnhance.Domain.UseCases
{
    public class ExecuteGradeUpUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IUnitService UnitService { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IArtworkFragmentAcquisitionModelFactory ArtworkFragmentAcquisitionModelFactory { get; }

        public async UniTask<UnitEnhanceGradeUpResultModel> ExecuteGradeUp(CancellationToken cancellationToken, UserDataId userUnitId)
        {
            var gameFetchOtherModel = GameRepository.GetGameFetchOther();
            var beforeUnit = gameFetchOtherModel.UserUnitModels.Find(unit => unit.UsrUnitId == userUnitId);
            var beforeGrade = beforeUnit.Grade;
            // 交換前のユーザーの原画情報を取得
            var beforeUserArtworkModels = gameFetchOtherModel.UserArtworkModels;
            var beforeUserArtworkFragments = gameFetchOtherModel.UserArtworkFragmentModels;

            var result = await UnitService.GradeUp(cancellationToken, userUnitId);
            // 副作用 : GameFetchOtherの更新
            UpdateGameModel(result.UserUnit, result.UserItems, result.UserArtworks, result.UserArtworkFragments);
            var artworkFragmentAcquisitionModel = CreateArtworkFragment(
                result.UserArtworks,
                result.RewardModels,
                beforeUserArtworkModels,
                beforeUserArtworkFragments);

            return new UnitEnhanceGradeUpResultModel(
                userUnitId,
                beforeGrade,
                result.UserUnit.Grade,
                artworkFragmentAcquisitionModel);
        }

        void UpdateGameModel(
            UserUnitModel unit,
            IReadOnlyList<UserItemModel> userItems,
            IReadOnlyList<UserArtworkModel> userArtworks,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragments)
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();

            var newGameFetchOther = gameFetchOther with
            {
                UserItemModels = gameFetchOther.UserItemModels.Update(userItems),
                UserUnitModels = gameFetchOther.UserUnitModels.Update(unit),
                UserArtworkModels = gameFetchOther.UserArtworkModels.Update(userArtworks),
                UserArtworkFragmentModels = gameFetchOther.UserArtworkFragmentModels.Update(userArtworkFragments),
            };

            GameManagement.SaveGameFetchOther(newGameFetchOther);
        }

        ArtworkFragmentAcquisitionModel CreateArtworkFragment(
            IReadOnlyList<UserArtworkModel> userArtworks,
            IReadOnlyList<RewardModel> rewardModels,
            IReadOnlyList<UserArtworkModel> beforeUserArtworks,
            IReadOnlyList<UserArtworkFragmentModel> beforeUserArtworkFragments)
        {
            var artwork = ArtworkFragmentAcquisitionModel.Empty;
            var artworkReward = rewardModels
                .FirstOrDefault(reward => reward.ResourceType == ResourceType.Artwork, RewardModel.Empty);

            if (!artworkReward.IsEmpty())
            {
                artwork = ArtworkFragmentAcquisitionModelFactory.CreateArtworkFragmentAcquisitionModel(
                    userArtworks,
                    artworkReward.ResourceId,
                    beforeUserArtworks,
                    beforeUserArtworkFragments);
            }

            return artwork;
        }
    }
}
