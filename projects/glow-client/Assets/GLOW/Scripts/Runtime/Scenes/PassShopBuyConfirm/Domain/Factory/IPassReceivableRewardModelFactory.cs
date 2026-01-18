using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PassShopProductDetail.Domain.Model;

namespace GLOW.Scenes.PassShopBuyConfirm.Domain.Factory
{
    public interface IPassReceivableRewardModelFactory
    {
        IReadOnlyList<PassReceivableRewardModel> CreatePassReceivableRewardModels(MasterDataId mstShopPassId);
    }
}