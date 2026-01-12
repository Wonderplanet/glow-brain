using GLOW.Core.Domain.ValueObjects.InGame;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record MoveStopConditionValue(ObscuredLong Value)
    {
        public static MoveStopConditionValue Empty { get; } = new(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public CommonConditionValue ToCommonConditionValue()
        {
            return new CommonConditionValue(Value.ToString());
        }

        public TickCount ToTickCount()
        {
            return new(Value);
        }

        public int ToInt()
        {
            return (int)Value;
        }
    }
}
