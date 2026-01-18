using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.PassShop.Domain.Enum;
using Zenject;

namespace GLOW.Scenes.PassShop.Domain.UseCase
{
    public class CheckPassPurchasableUseCase
    {
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }
        
        public PassUnpurchasableReason CheckPassPurchaseStatus(MasterDataId mstShopPassId)
        {
            var lastLoginTime = GameRepository.GetGameFetchOther().UserLoginInfoModel.LastLoginAt;
            if (lastLoginTime != null)
            {
                if (DailyResetTimeCalculator.IsPastDailyRefreshTime(lastLoginTime.Value))
                {
                    return PassUnpurchasableReason.IsDateChanged;
                }
            }
            
            var pass = MstShopProductDataRepository.GetShopPasses()
                .Where(model => model.MstShopPassId == mstShopPassId)
                .FirstOrDefault(MstShopPassModel.Empty);
            
            if (pass.IsEmpty())
            {
                return PassUnpurchasableReason.IsInvalidPass;
            }

            var isValidTime = CalculateTimeCalculator.IsValidTime(
                TimeProvider.Now,
                pass.PassStartAt.Value,
                pass.PassEndAt.Value);

            if (!isValidTime)
            {
                return PassUnpurchasableReason.IsInvalidPass;
            }

            var userPass = GameRepository.GetGameFetchOther().UserShopPassModels
                .FirstOrDefault(model => model.MstShopPassId == mstShopPassId, UserShopPassModel.Empty);
            if (!userPass.IsEmpty() && userPass.EndAt > TimeProvider.Now)
            {
                return PassUnpurchasableReason.Purchased;
            }

            return PassUnpurchasableReason.None;
        }
    }
}
