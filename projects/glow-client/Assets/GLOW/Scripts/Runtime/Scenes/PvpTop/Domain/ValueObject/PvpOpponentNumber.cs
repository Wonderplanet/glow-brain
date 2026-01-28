using GLOW.Core.Domain.Constants;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.PvpTop.Domain.ValueObject
{
    public record PvpOpponentNumber(ObscuredInt Value)
    {
        public static PvpOpponentNumber Empty { get; } = new(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsInRange(int listCount)
        {
            return Value >= 1 && Value <= listCount;
        }

        public int ToIndex()
        {
            return Value - 1;
        }

        public bool IsValid()
        {
            return 1 <= Value && Value <= PvpConst.MatchUserMaxCount;
        }
    };
}
