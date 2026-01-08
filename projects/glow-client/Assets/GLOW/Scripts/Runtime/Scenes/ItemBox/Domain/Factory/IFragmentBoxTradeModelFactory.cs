using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ItemBox.Domain.Models;

namespace GLOW.Scenes.ItemBox.Domain.Factory
{
    public interface IFragmentBoxTradeModelFactory
    {
        FragmentBoxTradeModel CreateFragmentBoxTradeModel(MasterDataId offerItemId);
    }
}