using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.ShopBuyConform.Domain.Model;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.ShopBuyConform.Domain.UseCase
{
    public class CurrentPlayerResourceInfoUseCase
    {
        [Inject] IGameRepository GameRepository { get; }

        [Inject] ISystemInfoProvider SystemInfoProvider { get; }

        public CurrentPlayerResourceInfoUseCaseModel GetCurrentPlayerResourceAmount()
        {
            var userParameterModel = GameRepository.GetGameFetch().UserParameterModel;
            var freeDiamond = userParameterModel.FreeDiamond;
            var paidDiamond = userParameterModel.GetPaidDiamondFromPlatform(SystemInfoProvider.GetApplicationSystemInfo().PlatformId);
            var currentCoin = userParameterModel.Coin;
            return new CurrentPlayerResourceInfoUseCaseModel(paidDiamond, freeDiamond, currentCoin);
        }
    }
}