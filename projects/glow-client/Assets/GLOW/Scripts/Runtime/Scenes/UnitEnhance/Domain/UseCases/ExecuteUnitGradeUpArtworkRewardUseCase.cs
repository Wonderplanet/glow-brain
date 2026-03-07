using System.Collections.Generic;
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
using Zenject;

namespace GLOW.Scenes.UnitEnhance.Domain.UseCases
{
    public class ExecuteUnitGradeUpArtworkRewardUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IUnitService UnitService { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IArtworkFragmentAcquisitionModelFactory ArtworkFragmentAcquisitionModelFactory { get; }

        public async UniTask<ArtworkFragmentAcquisitionModel> ExecuteUnitGradeUpArtworkReward(
            CancellationToken cancellationToken,
            UserDataId userUnitId)
        {
            var result = await UnitService.ReceiveGradeUpReward(cancellationToken, userUnitId);

            var gameFetchOtherModel = GameRepository.GetGameFetchOther();

            // 副作用 : GameFetchOtherの更新
            UpdateGameModel(result.UserUnit, result.UserArtworks, result.UserArtworkFragments);

            // 交換前のユーザーの原画情報を取得
            var beforeUserArtworkModels = gameFetchOtherModel.UserArtworkModels;
            var beforeUserArtworkFragments = gameFetchOtherModel.UserArtworkFragmentModels;

            // 原画演出のための情報を作成
            var artworkFragmentAcquisitionModel = CreateArtworkFragment(
                result.UserArtworks,
                result.RewardModels,
                beforeUserArtworkModels,
                beforeUserArtworkFragments);

            return artworkFragmentAcquisitionModel;
        }

        void UpdateGameModel(
            UserUnitModel unit,
            IReadOnlyList<UserArtworkModel> userArtworks,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragments)
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();

            var newGameFetchOther = gameFetchOther with
            {
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
