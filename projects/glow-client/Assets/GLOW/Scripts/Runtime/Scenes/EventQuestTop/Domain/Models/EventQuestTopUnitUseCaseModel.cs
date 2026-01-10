using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EventQuestTop.Domain.Models
{
    public record EventQuestTopUnitUseCaseModel(
        UnitImageAssetPath UnitImageAssetPath,
        IReadOnlyList<EventDisplayUnitSpeechBalloonText> SpeechBalloonTexts
    )
    {
        public static EventQuestTopUnitUseCaseModel Empty { get; } = new EventQuestTopUnitUseCaseModel(
            UnitImageAssetPath.Empty,
            new List<EventDisplayUnitSpeechBalloonText>()
        );
    };
}
