using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pass;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Scenes.Notice.Domain.Evaluator
{
    public class NoticePassPurchasedEvaluator : INoticePassPurchasedEvaluator
    {
        [Inject] IHeldPassEffectRepository HeldPassEffectRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        bool INoticePassPurchasedEvaluator.IsTargetPassPurchased(MasterDataId mstShopPassId)
        {
            var passEffectModels = HeldPassEffectRepository.GetHeldPassEffectListModel();
            var targetPass = passEffectModels.PassEffectModels
                .FirstOrDefault(x => x.MstShopPassId == mstShopPassId, HeldPassEffectModel.Empty);

            if (targetPass.IsEmpty()) return false;

            return CalculateTimeCalculator.IsValidTime(
                TimeProvider.Now,
                targetPass.StartAt.Value,
                targetPass.EndAt.Value);
        }
    }
}
