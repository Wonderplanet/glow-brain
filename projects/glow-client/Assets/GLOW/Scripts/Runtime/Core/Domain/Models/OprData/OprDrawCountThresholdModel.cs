using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Domain.Models.OprData
{
    public record OprDrawCountThresholdModel(
        DrawCountThresholdGroupId DrawCountThresholdGroupId,
        UpperType UpperType,
        GachaThresholdCount GachaThresholdCount
    );
}
