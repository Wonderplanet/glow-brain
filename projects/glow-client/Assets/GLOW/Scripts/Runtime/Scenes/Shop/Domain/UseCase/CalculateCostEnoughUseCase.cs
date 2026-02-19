using System;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Shop.Domain.Model;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.Shop.Domain.UseCase
{
    public class CalculateCostEnoughUseCase
    {
        [Inject] IGameRepository GameRepository { get; }

        [Inject] ISystemInfoProvider SystemInfoProvider { get; }

        public CalculateCostEnoughUseCaseModel CalculateCostEnough(DisplayCostType displayCostType, CostAmount costAmount)
        {
            var userParameterModel = GameRepository.GetGameFetch().UserParameterModel;
            long currentResourceAmount = 0;

            switch (displayCostType)
            {
                case DisplayCostType.Coin:
                    currentResourceAmount = userParameterModel.Coin.Value;
                    break;
                case DisplayCostType.Diamond:
                    currentResourceAmount = userParameterModel.FreeDiamond.Value + userParameterModel.GetPaidDiamondFromPlatform(SystemInfoProvider.GetApplicationSystemInfo().PlatformId).Value;
                    break;
                case DisplayCostType.PaidDiamond:
                    currentResourceAmount = userParameterModel.GetPaidDiamondFromPlatform(SystemInfoProvider.GetApplicationSystemInfo().PlatformId).Value;
                    break;
            }
            var afterResourceAmount = currentResourceAmount - costAmount.ToInt;
            var isEnough = afterResourceAmount >= 0;
            afterResourceAmount = Math.Max(afterResourceAmount, 0);

            return new CalculateCostEnoughUseCaseModel(displayCostType, currentResourceAmount, afterResourceAmount,
                isEnough);
        }
    }
}
