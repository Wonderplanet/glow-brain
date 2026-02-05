using Cysharp.Text;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Stage
{
    public record StageNumber(ObscuredInt Value)
    {
        public static StageNumber Empty { get; } = new (0);

        public static StageNumber Create(int value)
        {
            return value > 0 ? new StageNumber(value) : Empty;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public InGameNumber ToInGameStageNumber()
        {
            return IsEmpty() ? InGameNumber.Empty : new InGameNumber(Value);
        }

        public string ToSentenceString()
        {
            return ZString.Format("{0}話", Value);
        }
    }
}
