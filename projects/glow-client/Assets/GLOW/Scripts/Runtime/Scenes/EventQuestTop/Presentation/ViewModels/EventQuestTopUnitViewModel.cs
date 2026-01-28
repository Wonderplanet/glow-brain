using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EventQuestTop.Presentation.ViewModels
{
    public record EventQuestTopUnitViewModel(
        UnitImageAssetPath UnitImageAssetPath,
        IReadOnlyList<EventDisplayUnitSpeechBalloonText> SpeechBalloonTexts);
}
