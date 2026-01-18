using GLOW.Scenes.InGame.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record AttackRangeParameter(ObscuredFloat Value)
    {
        public static AttackRangeParameter Empty { get; } = new(0f);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public OutpostCoordV2 ToOutpostCoord()
        {
            return new OutpostCoordV2(Value, 0f);
        }

        public KomaNo ToKomaNo()
        {
            return new KomaNo((int)Value);
        }
        
        public KomaCount ToKomaCount()
        {
            return new KomaCount((int)Value);
        }

        public KomaLineNo ToKomaLineNo()
        {
            return new KomaLineNo((int)Value);
        }
    }
}
