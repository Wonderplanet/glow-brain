using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Domain.Models
{
    /// <summary> フィルタの特性項目の一覧表示用のモデル </summary>
    public record UnitAbilityFilterTitleModel(
        UnitAbilityType UnitAbilityType,
        AbilityFilterTitle AbilityFilterTitle);
}
