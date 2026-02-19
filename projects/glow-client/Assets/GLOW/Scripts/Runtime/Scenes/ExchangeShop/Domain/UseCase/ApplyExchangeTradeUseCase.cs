using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.ExchangeShop;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.ArtworkFragmentAcquisition.Domain.Models;
using GLOW.Scenes.BattleResult.Domain.Factory;
using GLOW.Scenes.ExchangeShop.Domain.UseCaseModel;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Extensions;
using GLOW.Scenes.UnitReceive.Domain.Model;

namespace GLOW.Scenes.ExchangeShop.Domain.UseCase
{
    public class ApplyExchangeTradeUseCase
    {
        [Inject] IExchangeService ExchangeService { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IAcquisitionDisplayedUnitIdsRepository AcquisitionDisplayedUnitIdsRepository { get; }
        [Inject] IArtworkFragmentAcquisitionModelFactory ArtworkFragmentAcquisitionModelFactory { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }

        public async UniTask<ExchangeResultUseCaseModel> ApplyExchangeTrade(
            CancellationToken cancellationToken,
            MasterDataId mstExchangeId,
            MasterDataId mstExchangeLineupId,
            ItemAmount amount)
        {
            var exchangeTradeResultModel =
                await ExchangeService.Trade(cancellationToken, mstExchangeId, mstExchangeLineupId, amount);

            var gameFetchOtherModel = GameRepository.GetGameFetchOther();
            var gameFetchModel = GameRepository.GetGameFetch();

            // ユーザー情報の更新
            UpdateAndSaveRepository(gameFetchModel, gameFetchOtherModel, exchangeTradeResultModel);

            // アイテムの報酬情報を作成
            var itemReward = exchangeTradeResultModel.ExchangeRewards
                .Select(CreateCommonReceiveResourceModel)
                .ToList();

            // 交換前のユーザーフラグメント情報を取得
            var beforeUserArtworkFragments = gameFetchOtherModel.UserArtworkFragmentModels.ToList();

            // 原画演出のための情報を作成
            var artworkFragmentAcquisitionModel = CreateArtworkFragument(
                exchangeTradeResultModel,
                beforeUserArtworkFragments);

            var unitReceiveModel = CreateUnitReceiveModel(exchangeTradeResultModel);

            return new ExchangeResultUseCaseModel(
                itemReward,
                artworkFragmentAcquisitionModel,
                unitReceiveModel);
        }

        UnitReceiveModel CreateUnitReceiveModel(ExchangeTradeResultModel exchangeTradeResultModel)
        {
            var unitReward = exchangeTradeResultModel.ExchangeRewards
                .FirstOrDefault(r =>
                    r.ExchangeReward.ResourceType == ResourceType.Unit,ExchangeRewardModel.Empty);

            if(unitReward.IsEmpty()) return UnitReceiveModel.Empty;

            var mstUnitId = unitReward.ExchangeReward.ResourceId;
            var mstUnit = MstCharacterDataRepository.GetCharacter(mstUnitId);
            var displayedUnitIds = AcquisitionDisplayedUnitIdsRepository.GetAcquisitionDisplayedUnitIds();

            // 初回獲得キャラのみ表示するため、既に獲得しているキャラの場合はEmptyを返す
            if (displayedUnitIds.Contains(mstUnitId)) return UnitReceiveModel.Empty;

            // 副作用
            var updatedDisplayedUnitIds = displayedUnitIds.Append(mstUnitId).ToList();
            AcquisitionDisplayedUnitIdsRepository.SetAcquisitionDisplayedUnitIds(updatedDisplayedUnitIds);

            var speechBalloonText = mstUnit.SpeechBalloons
                .FirstOrDefault(
                    model => model.ConditionType == SpeechBalloonConditionType.Summon,
                    SpeechBalloonModel.Empty)
                .SpeechBalloonText;

            return new UnitReceiveModel(
                mstUnit.Name,
                mstUnit.RoleType,
                mstUnit.Color,
                mstUnit.Rarity,
                UnitCutInKomaAssetPath.FromAssetKey(mstUnit.AssetKey),
                UnitImageAssetPath.FromAssetKey(mstUnit.AssetKey),
                new SeriesLogoImagePath(SeriesAssetPath.GetSeriesLogoPath(mstUnit.SeriesAssetKey.Value)),
                speechBalloonText);
        }

        CommonReceiveResourceModel CreateCommonReceiveResourceModel(ExchangeRewardModel exchangeRewardModel)
        {
            var reward = exchangeRewardModel.ExchangeReward;

            var playerResourceModel = PlayerResourceModelFactory.Create(
                reward.ResourceType,
                reward.ResourceId,
                reward.Amount);

            var preConversionPlayerResourceModel = reward.PreConversionResource.IsEmpty()
                ? PlayerResourceModel.Empty
                : PlayerResourceModelFactory.Create(
                    reward.PreConversionResource.ResourceType,
                    reward.PreConversionResource.ResourceId,
                    new PlayerResourceAmount(reward.PreConversionResource.ResourceAmount.Value));

            return new CommonReceiveResourceModel(
                reward.UnreceivedRewardReasonType,
                playerResourceModel,
                preConversionPlayerResourceModel);
        }

        ArtworkFragmentAcquisitionModel CreateArtworkFragument(
            ExchangeTradeResultModel exchangeTradeResultModel,
            IReadOnlyList<UserArtworkFragmentModel> beforeUserArtworkFragments)
        {
            var artwork = ArtworkFragmentAcquisitionModel.Empty;
            var artworkReward = exchangeTradeResultModel.ExchangeRewards
                .FirstOrDefault(r => r.ExchangeReward.ResourceType == ResourceType.Artwork,ExchangeRewardModel.Empty);

            if (!artworkReward.IsEmpty())
            {
                artwork = ArtworkFragmentAcquisitionModelFactory.CreateArtworkFragmentAcquisitionModel(
                    exchangeTradeResultModel.UserArtworks,
                    artworkReward.ExchangeReward.ResourceId,
                    beforeUserArtworkFragments);
            }

            return artwork;
        }

        void UpdateAndSaveRepository(
            GameFetchModel gameFetchModel,
            GameFetchOtherModel gameFetchOtherModel,
            ExchangeTradeResultModel exchangeTradeResultModel)
        {
            var updatedGameFetchModel = gameFetchModel with
            {
                UserParameterModel = exchangeTradeResultModel.UserParameter
            };

            var updatedGameFetchOtherModel = gameFetchOtherModel with
            {
                UserItemModels = gameFetchOtherModel.UserItemModels.Update(exchangeTradeResultModel.UserItems),
                UserUnitModels = gameFetchOtherModel.UserUnitModels.Update(exchangeTradeResultModel.UserUnits),
                UserEmblemModel = gameFetchOtherModel.UserEmblemModel.Update(exchangeTradeResultModel.UserEmblems),
                UserArtworkModels = gameFetchOtherModel.UserArtworkModels.Update(exchangeTradeResultModel.UserArtworks),
                UserArtworkFragmentModels = gameFetchOtherModel.UserArtworkFragmentModels.Update(exchangeTradeResultModel.UserArtworkFragments),
                UsrExchangeLineupModels = gameFetchOtherModel.UsrExchangeLineupModels.Update(exchangeTradeResultModel.UserExchangeLineups)
            };

            GameManagement.SaveGameFetch(updatedGameFetchModel);
            GameManagement.SaveGameFetchOther(updatedGameFetchOtherModel);
        }
    }
}
