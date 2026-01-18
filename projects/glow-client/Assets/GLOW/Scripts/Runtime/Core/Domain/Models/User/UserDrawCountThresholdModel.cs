using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Domain.Models
{
    public record UserDrawCountThresholdModel(
        DrawCountThresholdGroupId DrawCountThresholdGroupId,
        UpperType UpperType,
        GachaPlayedCount GachaPlayedCount)
    {
        public static UserDrawCountThresholdModel Empty { get; } = new (
            DrawCountThresholdGroupId.Empty, 
            UpperType.MaxRarity, 
            GachaPlayedCount.Zero);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
