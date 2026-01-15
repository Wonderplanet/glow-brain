using GLOW.Scenes.InGame.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record MangaAnimationConditionValue(ObscuredString Value)
    {
        public static MangaAnimationConditionValue Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public AutoPlayerSequenceElementId ToAutoPlayerSequenceElementId()
        {
            return new AutoPlayerSequenceElementId(Value);
        }
    }
}
