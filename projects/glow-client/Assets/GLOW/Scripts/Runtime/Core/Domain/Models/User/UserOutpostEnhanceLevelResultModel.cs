using GLOW.Core.Domain.ValueObjects.OutpostEnhance;

namespace GLOW.Core.Domain.Models
{
    public record UserOutpostEnhanceLevelResultModel(
        OutpostEnhanceLevel BeforeLevel,
        OutpostEnhanceLevel AfterLevel
        );
}
