using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.PvpTop.Domain.ValueObject
{
    public record PvpOpponentRefreshCoolTime(ObscuredFloat Value)
    {
        public static PvpOpponentRefreshCoolTime Empty { get; } = new(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool HasCoolTime()
        {
            return Value > 0;
        }

        public string ToViewString()
        {
            return ((int)Value).ToString();
        }
    };
}
