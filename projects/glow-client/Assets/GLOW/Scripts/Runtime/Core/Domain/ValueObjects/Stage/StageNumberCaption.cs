using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects.Stage
{
    public record StageNumberCaption(string Value)
    {
        public static StageNumberCaption Empty { get; } = new(string.Empty);

        public static StageNumberCaption Create(InGameNumber inGameNumber)
        {
            return inGameNumber.IsEmpty() ? Empty : new StageNumberCaption(ZString.Format("第{0}話", inGameNumber.Value.ToString()));
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
