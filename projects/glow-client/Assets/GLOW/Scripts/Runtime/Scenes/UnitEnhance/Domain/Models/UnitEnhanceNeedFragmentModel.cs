using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhance.Domain.Models
{
    public record UnitEnhanceNeedFragmentModel(
        MasterDataId FragmentMstItemId,
        ItemModel FragmentBoxItemModel,
        ItemAmount LimitUseAmount,
        bool IsEnoughFragment,
        bool IsLackFragmentBox,
        bool IsMaxGrade,
        ItemName FragmentItemName);
}
