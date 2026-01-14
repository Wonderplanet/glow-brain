using GLOW.Core.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.EventQuestSelect.Domain.ValueObject
{
    public record EventChallengeCount(ObscuredInt Value) : IQuestChallengeCountable
    {
        public static EventChallengeCount Empty { get; } = new(0);
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        public bool IsZero()
        {
            return Value == 0;
        }
    }
}
