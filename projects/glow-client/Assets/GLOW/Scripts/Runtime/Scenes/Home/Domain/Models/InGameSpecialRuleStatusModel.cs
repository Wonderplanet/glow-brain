using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record InGameSpecialRuleStatusModel(PartyName PartyName, IReadOnlyList<StageLimitStatus> LimitStatus)
    {
        public static InGameSpecialRuleStatusModel Empty { get; } = new(PartyName.Empty, new List<StageLimitStatus>());
    };

}
