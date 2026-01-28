using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pass;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Extensions;
using GLOW.Scenes.IdleIncentiveQuickReward.Domain.Moels;
using GLOW.Scenes.PassShop.Domain.Factory;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.IdleIncentiveQuickReward.Domain.UseCases
{
    public class GetIdleIncentiveQuickReceiveModelUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstIdleIncentiveRepository MstIdleIncentiveRepository { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }
        [Inject] IHeldPassEffectRepository HeldPassEffectRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IHeldAdSkipPassInfoModelFactory HeldAdSkipPassInfoModelFactory { get; }

        public IdleIncentiveQuickReceiveWindowModel GetModel()
        {
            var mstIdleIncentive = MstIdleIncentiveRepository.GetMstIdleIncentive();
            var usrIdleIncentive = GameRepository.GetGameFetchOther().UserIdleIncentiveModel;
            var userParameter = GameRepository.GetGameFetch().UserParameterModel;

            var paidDiamond = userParameter.GetPaidDiamondFromPlatform(SystemInfoProvider.GetApplicationSystemInfo().PlatformId).Value;
            var requireDiamondAmount = userParameter.FreeDiamond.Value + paidDiamond;
            
            var nowTime = TimeProvider.Now;
            var passEffectForAd = HeldPassEffectRepository.GetHeldPassEffectListModel()
                .GetPassEffectValue(
                    ShopPassEffectType.IdleIncentiveMaxQuickReceiveByAd,
                    nowTime);
            
            var passEffectForDiamond = HeldPassEffectRepository.GetHeldPassEffectListModel()
                .GetPassEffectValue(
                    ShopPassEffectType.IdleIncentiveMaxQuickReceiveByDiamond,
                    nowTime);

            var heldAdSkipPassInfoModel = HeldAdSkipPassInfoModelFactory.CreateHeldAdSkipPassInfo();
            
            var adQuickReceiveCount = mstIdleIncentive.MaxDailyAdQuickReceiveAmount +
                                      passEffectForAd.ToIdleIncentiveReceiveCount() - 
                                      usrIdleIncentive.AdQuickReceiveCount;
            
            var diamondQuickReceiveCount = mstIdleIncentive.MaxDailyDiamondQuickReceiveAmount + 
                                           passEffectForDiamond.ToIdleIncentiveReceiveCount() - 
                                           usrIdleIncentive.DiamondQuickReceiveCount;

            return new IdleIncentiveQuickReceiveWindowModel(
                adQuickReceiveCount.ToIdleIncentiveRemainCount(),
                diamondQuickReceiveCount.ToIdleIncentiveRemainCount(),
                new ItemAmount(mstIdleIncentive.RequiredQuickReceiveDiamondAmount.Value),
                new EnoughItem(requireDiamondAmount >= mstIdleIncentive.RequiredQuickReceiveDiamondAmount.Value),
                mstIdleIncentive.QuickIdleMinutes,
                heldAdSkipPassInfoModel);
        }
    }
}
