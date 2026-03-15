using GLOW.Core.Domain.Models.Unit;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.SpecialAttackInfo.Domain.Model
{
    public record SpecialAttackInfoUseCaseModel(
        SpecialAttackInfoModel SpecialAttackInfoModel,
        CharacterName CharacterName)
    {
        public static SpecialAttackInfoUseCaseModel Empty { get; } = new(
            SpecialAttackInfoModel.Empty,
            CharacterName.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
