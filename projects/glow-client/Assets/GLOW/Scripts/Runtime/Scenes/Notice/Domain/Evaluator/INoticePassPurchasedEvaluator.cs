using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Notice.Domain.Evaluator
{
    public interface INoticePassPurchasedEvaluator
    {
        bool IsTargetPassPurchased(MasterDataId mstShopPassId);
    }
}
