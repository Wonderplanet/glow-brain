using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    /// <summary>
    /// 間合い
    /// </summary>
    /// <param name="Value"></param>
    public record WellDistance(ObscuredFloat Value)
    {
        public static WellDistance Empty { get; } = new(0f);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
