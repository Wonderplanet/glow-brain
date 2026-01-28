using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record OutpostEnhancementElement(
        OutpostEnhancementType Type,
        OutpostEnhanceLevel Level,
        OutpostEnhanceValue Value)
    {
        public static OutpostEnhancementElement Empty { get; } = new OutpostEnhancementElement(
            OutpostEnhancementType.LeaderPointSpeed, 
            OutpostEnhanceLevel.Empty,
            OutpostEnhanceValue.Empty);

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
