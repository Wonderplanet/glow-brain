using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.BoxGacha;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.BattleResult.Domain.Factory;
using GLOW.Scenes.BoxGacha.Domain.Factory;
using GLOW.Scenes.BoxGacha.Domain.Model;
using GLOW.Scenes.BoxGacha.Domain.Provider;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.BoxGacha.Domain.UseCase
{
    public class DrawBoxGachaUseCase
    {
        [Inject] IBoxGachaService BoxGachaService { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IUserBoxGachaCacheRepository UserBoxGachaCacheRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IMstBoxGachaProvider MstBoxGachaProvider { get; }
        [Inject] IBoxGachaInfoModelFactory BoxGachaInfoModelFactory { get; }
        [Inject] IArtworkFragmentAcquisitionModelFactory ArtworkFragmentAcquisitionModelFactory { get; }

        public async UniTask<BoxGachaDrawModel> Draw(
            MasterDataId mstEventId, 
            GachaDrawCount drawCount, 
            CancellationToken cancellationToken)
        {
            var mstBoxGachaModel = MstBoxGachaProvider.GetMstBoxGachaModelByEventId(mstEventId);
            if (mstBoxGachaModel.IsEmpty()) return BoxGachaDrawModel.Empty;
            
            var cachedUserBoxGachaModel = UserBoxGachaCacheRepository.GetFirstOrDefault(mstBoxGachaModel.Id);
            if (cachedUserBoxGachaModel.IsEmpty()) return BoxGachaDrawModel.Empty;
            
            // 引く前の原画関連の情報を保持
            var beforeGameFetchOther = GameRepository.GetGameFetchOther();
            var beforeUserArtworkFragments = beforeGameFetchOther.UserArtworkFragmentModels;
            
            var result = await BoxGachaService.Draw(
                cancellationToken, 
                mstBoxGachaModel.Id,
                drawCount,
                cachedUserBoxGachaModel.CurrentBoxLevel);
            
            // 副作用: キャッシュを更新
            UserBoxGachaCacheRepository.Save(result.UserBoxGachaModel);
            
            // 副作用: Boxガチャの結果を元に所持アイテムやユーザー情報などを更新
            UpdateFetchAndFetchOther(result);
            
            var infoModel = BoxGachaInfoModelFactory.Create(
                mstEventId,
                mstBoxGachaModel.Id,
                mstBoxGachaModel.CostId,
                mstBoxGachaModel.CostAmount,
                result.UserBoxGachaModel);
            
            var drawnBoxGachaResultRewards = CreateBoxGachaDrawResultCellModels(result.BoxGachaRewardModels);
            
            var artworkFragmentAcquisitionModels = result.BoxGachaRewardModels
                .Where(model => model.Reward.ResourceType == ResourceType.Artwork)
                .Select(model => model.Reward.ResourceId)
                .Distinct()
                .Select(id => ArtworkFragmentAcquisitionModelFactory.CreateArtworkFragmentAcquisitionModel(
                    result.UserArtworkModels, 
                    id, 
                    beforeUserArtworkFragments))
                .ToList();
            
            return new BoxGachaDrawModel(
                infoModel, 
                drawnBoxGachaResultRewards,
                artworkFragmentAcquisitionModels);
        }

        void UpdateFetchAndFetchOther(BoxGachaDrawResultModel result)
        {
            var previousGameFetchModel = GameRepository.GetGameFetch();
            var previousGameFetchOtherModel = GameRepository.GetGameFetchOther();
            var updatedGameFetchModel = CreateUpdatedGameFetch(previousGameFetchModel, result.UserParameterModel);
            var updatedGameFetchOtherModel = CreateUpdatedGameFetchOther(
                previousGameFetchOtherModel, 
                result.UserItemModels,
                result.UserUnitModels,
                result.UserArtworkModels,
                result.UserArtworkFragmentModels);
            GameManagement.SaveGameUpdateAndFetch(updatedGameFetchModel, updatedGameFetchOtherModel);
        }
        
        GameFetchModel CreateUpdatedGameFetch(
            GameFetchModel gameFetchModel,
            UserParameterModel userParameterModel)
        {
            var updatedGameFetchModel = gameFetchModel with
            {
                UserParameterModel = userParameterModel
            };

            return updatedGameFetchModel;
        }
        
        GameFetchOtherModel CreateUpdatedGameFetchOther(
            GameFetchOtherModel gameFetchOtherModel,
            IReadOnlyList<UserItemModel> userItemModels,
            IReadOnlyList<UserUnitModel> userUnitModels,
            IReadOnlyList<UserArtworkModel> userArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentModels)
        {
            var updatedGameFetchOtherModel = gameFetchOtherModel with
            {
                UserItemModels = gameFetchOtherModel.UserItemModels.Update(userItemModels),
                UserUnitModels = gameFetchOtherModel.UserUnitModels.Update(userUnitModels),
                UserArtworkModels = gameFetchOtherModel.UserArtworkModels.Update(userArtworkModels),
                UserArtworkFragmentModels = gameFetchOtherModel.UserArtworkFragmentModels.Update(userArtworkFragmentModels),
            };

            return updatedGameFetchOtherModel;
        }
        
        IReadOnlyList<BoxGachaDrawResultCellModel> CreateBoxGachaDrawResultCellModels(
            IReadOnlyList<BoxGachaRewardModel> models)
        {
            return models
                .Select(r => new BoxGachaDrawResultCellModel(
                    CreateCommonReceiveResourceModel(r.Reward), 
                    EvaluateNewUnit(r.Reward)))
                .ToList();
        }

        CommonReceiveResourceModel CreateCommonReceiveResourceModel(RewardModel rewardModel)
        {
            return new CommonReceiveResourceModel(
                rewardModel.UnreceivedRewardReasonType,
                PlayerResourceModelFactory.Create(
                    rewardModel.ResourceType,
                    rewardModel.ResourceId,
                    rewardModel.Amount),
                PlayerResourceModelFactory.Create(rewardModel.PreConversionResource));
        }
        
        IsNewUnitBadge EvaluateNewUnit(RewardModel rewardModel)
        {
            var isNewUnit = rewardModel.ResourceType == ResourceType.Unit &&
                             rewardModel.PreConversionResource.IsEmpty();
            return new IsNewUnitBadge(isNewUnit);
        }
    }
}