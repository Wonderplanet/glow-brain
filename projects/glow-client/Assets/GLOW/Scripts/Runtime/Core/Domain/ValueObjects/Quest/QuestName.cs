using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Quest
{
    public record QuestName(ObscuredString Value)
    {
        public static QuestName Empty { get; } = new QuestName(string.Empty);
        public bool IsEmpty() => ReferenceEquals(this, Empty);

        public override string ToString()
        {
            return Value;
        }
    };
}
