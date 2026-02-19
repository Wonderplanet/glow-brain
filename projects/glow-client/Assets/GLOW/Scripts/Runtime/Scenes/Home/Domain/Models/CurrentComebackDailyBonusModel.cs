using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record CurrentComebackDailyBonusModel(
        MasterDataId MstComebackDailyBonusScheduleId,
        DisplayAtLoginFlag IsDisplayAtLogin,
        bool IsVisibleHomeComebackDailyBonusIcon)
    {
        public static CurrentComebackDailyBonusModel Empty { get; } = new(
            MasterDataId.Empty,
            DisplayAtLoginFlag.False,
            false);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}